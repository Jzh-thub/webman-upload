<?php
/**
 * @desc ErrorMsg.php 描述信息
 */
declare(strict_types=1);

namespace Jzh\Upload\Traits;

trait ErrorMsg
{
    /**
     * 错误消息.
     */
    public $error = [
        'message' => '错误消息',
        'data'    => [],
    ];


    /**
     * 设置错误.
     * @param bool   $success 是否成功
     * @param string $message 错误消息
     * @param array  $data 消息体
     * @return bool
     */
    public function setError(bool $success, string $message, array $data = []): bool
    {
        $this->error = [
            'message' => $message,
            'data'    => $data,
        ];

        return $success;
    }

    /**
     * 获取错误信息完整体
     */
    public function getError(): array
    {
        return $this->error;
    }


    /**
     * 获取错误信息
     * @return string
     */
    public function getMessage(): string
    {
        return $this->error['message'];
    }


}
