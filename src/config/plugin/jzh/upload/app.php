<?php
return [
    'enable'  => true,
    'storage' => [
        'default'      => 'local',           // local：本地 oss：阿里云 cos：腾讯云 qos：七牛云
        'single_limit' => 1024 * 1024 * 200, // 单个文件的大小限制，默认200M 1024 * 1024 * 200
        'total_limit'  => 1024 * 1024 * 200, // 所有文件的大小限制，默认200M 1024 * 1024 * 200
        'nums'         => 10,                // 文件数量限制，默认10
        'include'      => [],                // 被允许的文件类型列表
        'exclude'      => [],                // 不被允许的文件类型列表
        // 本地对象存储
        'local'        => [
            'adapter' => \Jzh\Upload\Adapter\LocalAdapter::class,
            'root'    => public_path() . '/upload/',
            'dirname' => function () {
                return date('Ymd');
            },
            'domain'  => 'http://127.0.0.1:8787',
            'uri'     => '/upload/', // 如果 domain + uri 不在 public 目录下，请做好软链接，否则生成的url无法访问
            'algo'    => 'sha1',
        ],
        // 阿里云对象存储
        'oss'          => [
            'adapter'         => \Jzh\Upload\Adapter\OssAdapter::class,
            'accessKeyId'     => 'xxxxxxxxxxxxx',
            'accessKeySecret' => 'xxxxxxxxxxxxx',
            'bucket'          => 'webman',
            'dirname'         => function () {
                return 'upload';
            },
            'domain'          => 'http://webman.oss.com',
            'endpoint'        => 'oss-cn-shenzhen.aliyuncs.com',
            'algo'            => 'sha1',
        ],
        // 腾讯云对象存储
        'cos'          => [
            'adapter'   => \Jzh\Upload\Adapter\CosAdapter::class,
            'secretId'  => 'xxxxxxxxxxxxx',
            'secretKey' => 'xxxxxxxxxxxx',
            'bucket'    => 'webman',
            'dirname'   => 'upload',
            'domain'    => 'http://webman.oss.com',
            'region'    => 'ap-shanghai',
            'algo'      => 'sha1',
        ],
        // 七牛云对象存储
        'qiniu'        => [
            'adapter'   => \Jzh\Upload\Adapter\QiniuAdapter::class,
            'accessKey' => 'xxxxxxxxxxxxx',
            'secretKey' => 'xxxxxxxxxxxxx',
            'bucket'    => 'webman',
            'dirname'   => 'upload',
            'domain'    => 'http://webman.oss.com',
            'algo'      => 'sha1',
        ],
    ],
];
