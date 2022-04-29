<?php
/**
 * @desc AdapterInterface 适配器接口
 *
 */
declare(strict_types=1);

namespace Jzh\Upload\Adapter;

interface AdapterInterface
{

    /**
     * 上传文件
     * @param array $options
     * @return mixed
     */
    public function uploadFile(array $options = []);

    /**
     * 上传服务端文件
     * @param string $file_path
     * @return mixed
     */
    public function uploadServerFile(string $file_path);


    /**
     * Base64上传文件
     * @param string $base64
     * @param string $extension
     * @return mixed
     */
    public function uploadBase64(string $base64, string $extension = 'png');

    /**
     * 获取上传密钥
     * @return mixed
     */
    public function getTempKeys(string $dir = "");
}
