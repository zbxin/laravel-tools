<?php

return [
    //接口签名密钥
    'api_signature_secret' => env('API_SIGNATURE_SECRET'),

    //JSON输入转换中间件格式
    'case_input_format' => env('case_input_format', \ZhiEq\Constant::JSON_KEY_FORMAT_CAMEL_CASE),

    //JSON输出转换中间件格式
    'case_output_format' => env('case_output_format', \ZhiEq\Constant::JSON_KEY_FORMAT_STUDLY_CASE),
];
