# webman 上传插件

## 特性

- 本地多文件上传(本地、阿里云、腾讯云、七牛云)
- `Base64`图片文件上传
- 上传服务端文件
- 获取上传密钥

## 安装

```php
composer require jzh/upload
```

## 基本用法

```php
$upload=Jzh\Upload\Upload::config(); // 初始化。 默认为本地存储：local，阿里云：oss，腾讯云：cos，七牛：qiniu
$res = $upload->uploadFile();
if($res){
var_dump(json_encode($res));
}else{
    $res->getMessage();
    //or
    $res->setError();
}

```

### 上传成功信息
```json
[
    {
        "key": "webman",
        "origin_name": "常用编程软件和工具.xlsx",
        "save_name": "03414c9bdaf7a38148742c87b96b8167.xlsx",
        "save_path": "runtime/storage/03414c9bdaf7a38148742c87b96b8167.xlsx",
        "url": "/storage/fd2d472da56c71a6da0a5251f5e1b586.png",
        "uniqid ": "03414c9bdaf7a38148742c87b96b8167",
        "size": 15050,
        "mime_type": "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        "extension": "xlsx"
    }
    ...
]
```
> 失败，抛出`UploadException`异常
### 成功响应字段

| 字段|描述|示例值|
|:---|:---|:---|
|key | 上传文件key | webman |
|origin_name |原始文件名 | 常用编程软件和工具.xlsx |
|save_name |保存文件名 | 03414c9bdaf7a38148742c87b96b8167.xlsx |
|save_path|文件保存路径（相对） | /var/www/webman-admin/runtime/storage/03414c9bdaf7a38148742c87b96b8167.xlsx|
|url |url访问路径 | /storage/03414c9bdaf7a38148742c87b96b8167.xlsx|
|unique_id|uniqid | 03414c9bdaf7a38148742c87b96b8167|
|size |文件大小 | 15050（字节）|
|mime_type |文件类型 | application/vnd.openxmlformats-officedocument.spreadsheetml.sheet|
|extension |文件扩展名 | xlsx|


## 上传验证

支持使用验证类对上传文件的验证，包括文件大小、文件类型和后缀

| 字段|描述|示例值|
|:---|:---|:---|
|single_limit | 单个文件的大小限制，默认200M | 1024 * 1024 * 200 |
|total_limit | 所有文件的大小限制，默认200M | 1024 * 1024 * 200 |
|nums | 文件数量限制，默认10 | 10 |
|include | 被允许的文件类型列表 | ['xlsx','pdf'] |
|exclude | 不被允许的文件类型列表 | ['png','jpg'] |

## 支持上传SDK

#### 阿里云对象存储

```php
composer require aliyuncs/oss-sdk-php
```
#### 腾讯云对象存储

```php
composer require qcloud/cos-sdk-v5
composer require qcloud_sts/qcloud-sts-sdk
```

#### 七牛云云对象存储

```php
composer require qiniu/php-sdk
```

## 上传Base64图片

>**使用场景：** 前端直接截图（头像、Canvas等）一个Base64数据流的图片直接上传到云端

#### 请求参数

```json
{
    "extension": "png",
    "base64": "data:image/jpeg;base64,/9j/4AAQSkxxxxxxxxxxxxZJRgABvtyQBIr/MPTPTP/2Q=="
}
```
#### 请求案例（阿里云）

```php
public function upload(Request $request)
{
   $upload= Jzh\Upload\Upload::config(Jzh\Upload\Upload::MODE_OSS, false); // 第一个参数为存储方式。第二个参数为是否是文件（默认是）
    $base64 = $request->post('base64');
    $r = $upload->uploadBase64($base64,'png');
    var_dump($r);
}
```

#### 响应参数
```json
{
	"save_path": "storage/20220402213639624851671439e.png",
	"url": "http://webman.oss.com/storage/20220402213639624851671439e.png",
	"unique_id": "20220402213639624851671439e",
	"size": 11802,
	"extension": "png"
}
```
## 上传服务端文件

>**使用场景：** 服务端导出文件需要上传到云端存储，或者零时下载文件存储。

#### 请求案例（阿里云）

```php
$upload=Jzh\Upload\Upload::config(Jzh\Upload\Upload::MODE_OSS,false);
$localFile = public_path() . DIRECTORY_SEPARATOR . 'public/webman.png';
$res = $upload->uploadServerFile($localFile);
```

#### 响应参数

```json
{
	"origin_name": "webman.png",
	"save_path": "storage/6edf04d7c26f020cf5e46e6457620220402213414.png",
	"url": "http://webman.oss.com/storage/6ed9ffd54d0df57620220402213414.png",
	"unique_id": "6edf04d7c26f020cf5e46e6403213414",
	"size": 3505604,
	"extension": "png"
}
```

## 获取上传密钥

>**使用场景：** 获取密钥 用于前端上传

#### 请求案例（阿里云）

```php
$upload=Jzh\Upload\Upload::config(Jzh\Upload\Upload::MODE_OSS,false);
$res = $upload->getTempKeys();
```

