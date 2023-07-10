<?php
/**
 * 腾讯云对象存储适配器
 * https://cloud.tencent.com/document/product/436
 */
declare(strict_types=1);

namespace Jzh\Upload\Adapter;


use Jzh\Upload\Exception\UploadException;
use Qcloud\Cos\Client;
use Qcloud\Cos\Exception\CosException;
use QCloud\COSSTS\Sts;

class CosAdapter extends AdapterAbstract
{
    /**
     * @var null
     */
    protected $instance = null;

    /**
     * @desc: 对象存储实例
     */
    public function getInstance(): ?Client
    {
        if (is_null($this->instance)) {
            $this->instance = new Client([
                'region'      => $this->config['region'],
                'schema'      => 'https',
                'credentials' => [
                    'secretId'  => $this->config['secretId'],
                    'secretKey' => $this->config['secretKey'],
                ],
            ]);
        }

        return $this->instance;
    }

    /**
     * 上传文件
     */
    public function uploadFile(array $options = []): array
    {
        try {
            $result = [];
            foreach ($this->files as $key => $file) {
                $uniqueId = hash_file($this->algo, $file->getPathname());
                $saveName = $uniqueId . '.' . $file->getUploadExtension();
                $object   = $this->config['dirname'] . $this->dirSeparator . $saveName;
                $temp     = [
                    'key'         => $key,
                    'origin_name' => $file->getUploadName(),
                    'save_name'   => $saveName,
                    'save_path'   => $object,
                    'url'         => $this->config['domain'] . $this->dirSeparator . $object,
                    'unique_id'   => $uniqueId,
                    'size'        => $file->getSize(),
                    'mime_type'   => $file->getUploadMineType(),
                    'extension'   => $file->getUploadExtension(),
                    'storage_mode'=>'COS'
                ];
                $this->getInstance()->putObject([
                    'Bucket' => $this->config['bucket'],
                    'Key'    => $object,
                    'Body'   => fopen($file->getPathname(), 'rb'),
                ]);
                array_push($result, $temp);
            }
        } catch (\Throwable | CosException $exception) {
            throw new UploadException($exception->getMessage());
        }

        return $result;
    }

    /**
     *  上传服务端文件
     * @param string $file_path
     * @return array
     */
    public function uploadServerFile(string $file_path): array
    {
        $file = new \SplFileInfo($file_path);
        if (!$file->isFile()) {
            throw new UploadException('不是一个有效的文件');
        }

        $uniqueId = hash_file($this->algo, $file->getPathname());
        $object   = $this->config['dirname'] . $this->dirSeparator . $uniqueId . '.' . $file->getExtension();

        $result = [
            'origin_name' => $file->getFilename(),
            'save_path'   => $object,
            'url'         => $this->config['domain'] . $this->dirSeparator . $object,
            'unique_id'   => $uniqueId,
            'size'        => $file->getSize(),
            'extension'   => $file->getExtension(),
        ];

        $this->getInstance()->putObject([
            'Bucket' => $this->config['bucket'],
            'Key'    => $object,
            'Body'   => fopen($file->getPathname(), 'rb'),
        ]);

        return $result;
    }

    /**
     * 上传 base64
     * @param string $base64
     * @param string $extension
     * @return array
     */
    public function uploadBase64(string $base64, string $extension = 'png')
    {
        $base64   = explode(',', $base64);
        $uniqueId = date('YmdHis') . uniqid();
        $object   = $this->config['dirname'] . $this->dirSeparator . $uniqueId . '.' . $extension;

        $this->getInstance()->putObject([
            'Bucket' => $this->config['bucket'],
            'Key'    => $object,
            'Body'   => base64_decode($base64[1]),
        ]);

        $imgLen   = strlen($base64['1']);
        $fileSize = $imgLen - ($imgLen / 8) * 2;

        return [
            'save_path' => $object,
            'url'       => $this->config['domain'] . $this->dirSeparator . $object,
            'unique_id' => $uniqueId,
            'size'      => $fileSize,
            'extension' => $extension,
        ];
    }

    /**
     * 生成签名
     * @param string $dir
     * @return array
     * @throws \Exception
     */
    public function getTempKeys(string $dir = ""): array
    {

        $sts    = new Sts();
        $config = [
            'url'             => 'https://sts.tencentcloudapi.com/',
            'domain'          => 'sts.tencentcloudapi.com',
            'proxy'           => '',
            'secretId'        => $this->config['secretId'],      // 固定密钥
            'secretKey'       => $this->config['secretKey'],     // 固定密钥
            'bucket'          => $this->config['bucket'],        // 换成你的 bucket
            'region'          => $this->config['region'],        // 换成 bucket 所在园区
            'durationSeconds' => 1800,                           // 密钥有效期
            'allowPrefix'     => '*',                            // 这里改成允许的路径前缀，可以根据自己网站的用户登录态判断允许上传的具体路径，例子： a.jpg 或者 a/* 或者 * (使用通配符*存在重大安全风险, 请谨慎评估使用)
            // 密钥的权限列表。简单上传和分片需要以下的权限，其他权限列表请看 https://cloud.tencent.com/document/product/436/31923
            'allowActions'    => [
                // 简单上传
                'name/cos:PutObject',
                'name/cos:PostObject',
                // 分片上传
                'name/cos:InitiateMultipartUpload',
                'name/cos:ListMultipartUploads',
                'name/cos:ListParts',
                'name/cos:UploadPart',
                'name/cos:CompleteMultipartUpload'
            ]
        ];
        // 获取临时密钥，计算签名
        $result           = $sts->getTempKeys($config);
        $result['url']    = $this->config['domain'] . '/';
        $result['type']   = 'COS';
        $result['bucket'] = $this->config['bucket'];
        $result['region'] = $this->config['region'];
        return $result;
    }


}
