<?php
/**
 * 本地适配器
 */
declare(strict_types=1);

namespace Jzh\Upload\Adapter;

use Jzh\Upload\Exception\UploadException;

class LocalAdapter extends AdapterAbstract
{
    /**
     *  上传文件
     */
    public function uploadFile(array $options = []): array
    {
        $result = [];
        $basePath = $this->config['root'] . $this->config['dirname'] . DIRECTORY_SEPARATOR;
        if (!$this->createDir($basePath)) {
            throw new UploadException('文件夹创建失败，请核查是否有对应权限。');
        }

        $baseUrl = $this->config['domain'] . $this->config['uri'] . str_replace(DIRECTORY_SEPARATOR, '/', $this->config['dirname']) . DIRECTORY_SEPARATOR;
        foreach ($this->files as $key => $file) {
            $uniqueId = hash_file($this->algo, $file->getPathname());
//            $saveFilename = $uniqueId . '.' . $file->getUploadExtension();
            $saveFilename = $file->getUploadName();
            $savePath = $basePath . $saveFilename;
            $temp = [
                'key' => $key,
                'origin_name' => $file->getUploadName(),
                'save_name' => $saveFilename,
                'save_path' => $savePath,
                'url' => $baseUrl . $saveFilename,
                'unique_id' => $uniqueId,
                'size' => $file->getSize(),
                'mime_type' => $file->getUploadMineType(),
                'extension' => $file->getUploadExtension(),
                'storage_mode' => 'LOCAL'
            ];
            $file->move($savePath);
            array_push($result, $temp);
        }

        return $result;
    }

    /**
     * 创建目录
     */
    protected function createDir(string $path): bool
    {
        if (is_dir($path)) {
            return true;
        }

        $parent = dirname($path);
        if (!is_dir($parent)) {
            if (!$this->createDir($parent)) {
                return false;
            }
        }

        return mkdir($path);
    }
}
