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
     * 上传
     * @param string|null $storage 存储方式
     * @param bool $_is_file_upload 是否是文件（默认是）
     * @param array $option 其他参数(single_limit/total_limit/nums/include/exclude/dirname)
     * @return AdapterInterface
     */
    public static function config(string $storage = null, bool $_is_file_upload = true, array $option = []): AdapterInterface
    {
        $config = config('plugin.jzh.upload.app.storage');

        $storage = $storage ?: $config['default'];
        if (!isset($config[$storage]) || empty($config[$storage]['adapter'])) {
            throw new UploadException('对应的adapter不存在');
        }

        $initOption = array_merge($config[$storage], ['_is_file_upload' => $_is_file_upload]);
        if (isset($option['single_limit']) && $option['single_limit']) $initOption['single_limit'] = $option['single_limit'];
        if (isset($option['total_limit']) && $option['total_limit']) $initOption['total_limit'] = $option['total_limit'];
        if (isset($option['nums']) && $option['nums']) $initOption['nums'] = $option['nums'];
        if (isset($option['include']) && $option['include']) $initOption['include'] = $option['include'];
        if (isset($option['exclude']) && $option['exclude']) $initOption['exclude'] = $option['exclude'];
        if (isset($option['dirname']) && $option['dirname']) $initOption['dirname'] = $option['dirname'];
        return new $config[$storage]['adapter']($initOption);

    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return static::config()->{$name}(... $arguments);
    }
}
