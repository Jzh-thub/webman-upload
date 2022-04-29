<?php
/**
 * @desc StorageService
 */
declare(strict_types=1);

namespace Jzh\Upload;

use Jzh\Upload\Adapter\AdapterInterface;
use Jzh\Upload\Exception\UploadException;

/**
 * @see Upload
 * @mixin Upload
 *
 * @method static array uploadFile(array $config = [])                          上传文件
 * @method static array uploadBase64(string $base64, string $extension = 'png') 上传Base64文件
 * @method static array uploadServerFile(string $file_path)                     上传服务端文件
 * @method static array getTempKeys(string $dir)                                获取上传密钥
 */
class Upload
{
    /**
     * 本地对象存储.
     */
    public const MODE_LOCAL = 'local';

    /**
     * 阿里云对象存储.
     */
    public const MODE_OSS = 'oss';

    /**
     * 腾讯云对象存储.
     */
    public const MODE_COS = 'cos';

    /**
     * 七牛云对象存储.
     */
    public const MODE_QINIU = 'qiniu';


    /**
     * @param string|null $storage
     * @param bool        $_is_file_upload
     * @return mixed
     */
    public static function config(string $storage = null, bool $_is_file_upload = true):AdapterInterface
    {
        $config  = config('plugin.jzh.upload.app.storage');
        $storage = $storage ?: $config['default'];
        if (!isset($config[$storage]) || empty($config[$storage]['adapter'])) {
            throw new UploadException('对应的adapter不存在');
        }
        return new $config[$storage]['adapter'](array_merge(
            $config[$storage],
            [
                '_is_file_upload' => $_is_file_upload,
            ]
        ));
    }
}
