<?php

namespace ZhiEq\Utils;

use ZhiEq\Constant;
use ZhiEq\Exceptions\ConvertJsonKeyFormat\JsonFormatInvalidException;
use ZhiEq\Exceptions\ConvertJsonKeyFormat\JsonKeyFormatInvalidException;

trait ConvertJsonKeyFormat
{
    /**
     * @param $json
     * @param $format
     * @param bool $returnArray
     * @return false|array|string
     */

    public function convertJsonKeyFormat($json, $format, $returnArray = false)
    {
        $formatDefinition = [
            Constant::JSON_KEY_FORMAT_CAMEL_CASE,
            Constant::JSON_KEY_FORMAT_SNAKE_CASE,
            Constant::JSON_KEY_FORMAT_STUDLY_CASE,
        ];
        if (!in_array($format, $formatDefinition)) {
            throw new JsonKeyFormatInvalidException();
        }
        $json = json_decode($json, true);
        if ($json === null) {
            throw new JsonFormatInvalidException();
        }
        $convertResult = call_user_func($format . '_array_keys', $json);
        return $returnArray === true ? $convertResult : json_encode($convertResult);
    }
}
