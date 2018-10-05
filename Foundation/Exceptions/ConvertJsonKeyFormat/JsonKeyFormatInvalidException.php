<?php

namespace ZhiEq\Exceptions\ConvertJsonKeyFormat;

use ZhiEq\Contracts\Exception;

class JsonKeyFormatInvalidException extends Exception
{

    /**
     * 唯一错误代码5位数字，不能以零开头
     *
     * @return integer
     */
    protected function errorCode()
    {
        return 50001;
    }

    /**
     * 错误信息提示
     *
     * @return string
     */
    protected function message()
    {
        return 'Convert Json Key Format Params "format" Is Valid.';
    }

    /**
     * 固定调试信息
     *
     * @return array|null
     */
    protected function debug()
    {
        return [];
    }

    /**
     * Http状态码
     *
     * @return int
     */
    protected function statusCode()
    {
        return 500;
    }

    /**
     * 头部信息
     *
     * @return array
     */
    protected function headers()
    {
        return [];
    }

    /**
     * 内容信息
     *
     * @return array|null
     */
    protected function data()
    {
        return [];
    }
}
