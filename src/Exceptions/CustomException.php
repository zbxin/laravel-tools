<?php

namespace Zbxin\Exceptions;

use Zbxin\Contracts\Exception;

class CustomException extends Exception
{
    protected $customMessage;

    protected $errorCode;

    protected $statusCode;

    protected $data;

    protected $headers;

    protected $debug;

    public function __construct($message, $errorCode = null, $statusCode = null, $data = [], $headers = [], $debug = [])
    {
        $this->customMessage = $message;
        $this->errorCode = $errorCode;
        $this->statusCode = $statusCode;
        $this->data = $data;
        $this->headers = $headers;
        $this->debug = $debug;
        parent::__construct();
    }

    /**
     * 唯一错误代码5位数字，不能以零开头
     *
     * @return integer
     */
    protected function errorCode()
    {
        return $this->errorCode;
    }

    /**
     * 错误信息提示
     *
     * @return string
     */
    protected function message()
    {
        return $this->customMessage;
    }

    /**
     * 固定调试信息
     *
     * @return array|null
     */
    protected function debug()
    {
        return $this->debug;
    }

    /**
     * Http状态码
     *
     * @return int
     */
    protected function statusCode()
    {
        return $this->statusCode;
    }

    /**
     * 头部信息
     *
     * @return array
     */
    protected function headers()
    {
        return $this->headers;
    }

    /**
     * 内容信息
     *
     * @return array|null
     */
    protected function data()
    {
        return $this->data;
    }
}