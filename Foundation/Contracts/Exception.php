<?php

namespace ZhiEq\Contracts;

use Symfony\Component\HttpKernel\Exception\HttpException;

abstract class Exception extends HttpException
{
    /**
     * 构造函数
     */

    public function __construct()
    {
        $statusCode = $this->statusCode() ? $this->statusCode() : 500;
        $code = $this->errorCode() ? $this->errorCode() : 99999;
        $headers = $this->headers() ? $this->headers() : [];
        parent::__construct($statusCode, $this->message(), null, $headers, $code);
    }

    /**
     * 唯一错误代码5位数字，不能以零开头
     *
     * @return integer
     */

    abstract protected function errorCode();

    /**
     * 错误信息提示
     *
     * @return string
     */

    abstract protected function message();

    /**
     * 固定调试信息
     *
     * @return array|null
     */

    abstract protected function debug();

    /**
     * Http状态码
     *
     * @return int
     */

    abstract protected function statusCode();

    /**
     * 头部信息
     *
     * @return array
     */

    abstract protected function headers();

    /**
     * 内容信息
     *
     * @return array|null
     */

    abstract protected function data();

    /**
     * @return int
     */

    public function getErrorCode()
    {
        return $this->errorCode();
    }

    /**
     * 获取调试信息
     *
     * @return array|null
     */

    public function getDebug()
    {
        $debug = $this->debug() ? (is_array($this->debug()) ? $this->debug() : [$this->debug()]) : [];
        return debug_output(collect($debug)->toArray());
    }

    /**
     * 调试信息转成字符串
     *
     * @return string
     */

    public function getDebugAsString()
    {
        return collect($this->getDebug())->map(function ($value, $key) {
            return $key . ":" . $value;
        })->implode("\n");
    }

    /**
     * 获取返回的数据内容
     *
     * @return array|null
     */

    public function getData()
    {
        return is_array(collect($this->data())->toArray()) ? $this->data() : [];
    }
}
