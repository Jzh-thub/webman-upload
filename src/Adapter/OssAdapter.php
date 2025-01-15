<?php
/**
 * 阿里云OSS适配器
 */
declare(strict_types=1);

namespace Jzh\Upload\Adapter;

use Jzh\Upload\Exception\UploadException;
use OSS\Core\OssException;
use OSS\OssClient;
use Throwable;

class OssAdapter extends AdapterAbstract
{
    protected $instance = null;

    /**
     * 阿里云实例
     * @return OssClient|null
     * @throws \OSS\Core\OssException
     */
    public function getInstance(): ?OssClient
    {
        if (is_null($this->instance)) {
            $this->instance = new OssClient(
                $this->config['accessKeyId'],
                $this->config['accessKeySecret'],
                $this->config['endpoint']
            );
        }
        return $this->instance;
    }

    /**
     * @param array $options
     * @return array
     */
    public function uploadFile(array $options = []): array
    {
        try {
            $result = [];
            foreach ($this->files as $key => $file) {
                $uniqueId = hash_file($this->algo, $file->getPathname());
                $saveName = $uniqueId . '.' . $file->getUploadExtension();
                $object = $this->config['dirname'] . $this->dirSeparator . $saveName;
                $temp = [
                    'key' => $key,
                    'origin_name' => $file->getUploadName(),
                    'save_name' => $saveName,
                    'save_path' => $object,
                    'url' => $this->config['domain'] . $this->dirSeparator . $object,
                    'unique_id' => $uniqueId,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getUploadMineType(),
                    'extension' => $file->getUploadExtension(),
                    'storage_mode' => 'OSS'
                ];
                $upload = $this->getInstance()->uploadFile($this->config['bucket'], $object, $file->getPathname());
                if (!isset($upload['info']) && 200 != $upload['info']['http_code']) {
                    throw new UploadException((string)$upload);
                }
                array_push($result, $temp);
            }
        } catch (Throwable|OssException $exception) {
            throw new UploadException($exception->getMessage());
        }

        return $result;
    }

    /**
     * base64上传
     * @param string $base64
     * @param string $extension
     * @return array|bool
     */
    public function uploadBase64(string $base64, string $extension = 'png')
    {
        $base64 = explode(',', $base64);
        $uniqueId = date('YmdHis') . uniqid();
        $object = $this->config['dirname'] . $this->dirSeparator . $uniqueId . '.' . $extension;

        try {
            $result = $this->getInstance()->putObject($this->config['bucket'], $object, base64_decode($base64[1]));
            if (!isset($result['info']) && 200 != $result['info']['http_code']) {
                return $this->setError(false, (string)$result);
            }
        } catch (OssException $e) {
            return $this->setError(false, $e->getMessage());
        }
        $imgLen = strlen($base64['1']);
        $fileSize = $imgLen - ($imgLen / 8) * 2;

        return [
            'save_path' => $object,
            'url' => $this->config['domain'] . $this->dirSeparator . $object,
            'unique_id' => $uniqueId,
            'size' => $fileSize,
            'extension' => $extension,
        ];
    }

    /**
     * 上传服务端文件
     * @param string $file_path
     * @return array
     * @throws OssException
     */
    public function uploadServerFile(string $file_path, string $dir = ''): array
    {
        $file = new \SplFileInfo($file_path);
        if (!$file->isFile()) {
            throw new UploadException('不是一个有效的文件');
        }

        $uniqueId = hash_file($this->algo, $file->getPathname());
        $dir = $dir ? $dir : $this->config['dirname'];
        $object = $dir . $this->dirSeparator . $uniqueId . '.' . $file->getExtension();

        $result = [
            'origin_name' => $file->getFilename(),//getRealPath
            'save_path' => $object,
            'url' => $this->config['domain'] . $this->dirSeparator . $object,
            'unique_id' => $uniqueId,
            'size' => $file->getSize(),
            'extension' => $file->getExtension(),
        ];
        $upload = $this->getInstance()->uploadFile($this->config['bucket'], $object, $file->getRealPath());
        if (!isset($upload['info']) && 200 != $upload['info']['http_code']) {
            throw new UploadException((string)$upload);
        }

        return $result;
    }

    /**
     * 获取上传密钥
     * @param string $dir
     * @param string $callbackUrl
     * @return array
     */
    public function getTempKeys(string $dir = '', string $callbackUrl = ''): array
    {
        $base64CallbackBody = base64_encode(json_encode([
            'callbackUrl' => $callbackUrl,
            'callbackBody' => 'filename=${object}&size=${size}&mimeType=${mimeType}&height=${imageInfo.height}&width=${imageInfo.width}',
            'callbackBodyType' => "application/x-www-form-urlencoded"
        ]));
        $dir = $dir ?: $this->config['dirname'];
        $policy = json_encode([
            'expiration' => $this->gmtIso8601(time() + 30),
            'conditions' =>
                [
                    [0 => 'content-length-range', 1 => 0, 2 => 1048576000],
                    [0 => 'starts-with', 1 => '$key', 2 => $dir]
                ]
        ]);
        $base64Policy = base64_encode($policy);

        $signature = base64_encode(hash_hmac('sha1', $base64Policy, $this->config['accessKeySecret'], true));
        return [
            'accessid' => $this->config['accessKeyId'],
            'host' => $this->config['domain'],
            'policy' => $base64Policy,
            'signature' => $signature,
            'expire' => time() + 30,
            'callback' => $base64CallbackBody,
            'dir' => $dir,
            'type' => 'OSS'
        ];
    }

    /**
     * 获取ISO时间格式
     * @param $time
     * @return string
     * @throws \Exception
     */
    protected function gmtIso8601($time)
    {
        $dtStr = date("c", $time);
        $mydatetime = new \DateTime($dtStr);
        $expiration = $mydatetime->format(\DateTime::ISO8601);
        $pos = strpos($expiration, '+');
        $expiration = substr($expiration, 0, $pos);
        return $expiration . "Z";
    }
}
