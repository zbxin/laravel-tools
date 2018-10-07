<?php

return [
    //接口签名密钥
    'api_signature_secret' => env('API_SIGNATURE_SECRET'),

    //JSON输入转换中间件格式
    'case_input_format' => env('CASE_INPUT_FORMAT', \ZhiEq\Constant::JSON_KEY_FORMAT_CAMEL_CASE),

    //JSON输出转换中间件格式
    'case_output_format' => env('CASE_OUTPUT_FORMAT', \ZhiEq\Constant::JSON_KEY_FORMAT_STUDLY_CASE),

    //加密算法密钥
    'aes_secret_key' => env('AES_SECRET_KEY'),

    //定时任务
    /**
     * [
     *      ['command'=>'xxx','interval'=>10,'align'=>0]
     * ]
     */
    'cron_tasks' => [

    ],
];
