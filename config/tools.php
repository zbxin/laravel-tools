<?php

return [
  //接口签名密钥
  'api_signature_secret' => env('API_SIGNATURE_SECRET'),

  //JSON输入转换中间件格式
  'case_input_format' => env('CASE_INPUT_FORMAT', \Zbxin\CaseJson\ConvertJsonKeyFormat::FORMAT_CAMEL_CASE),

  //JSON输出转换中间件格式
  'case_output_format' => env('CASE_OUTPUT_FORMAT', \Zbxin\CaseJson\ConvertJsonKeyFormat::FORMAT_STUDLY_CASE),

  //加密算法密钥
  'aes_secret_key' => env('AES_SECRET_KEY'),

  //编码生成器默认编码长度
  'model_code_length' => env('MODEL_CODE_LENGTH', 10),

  //定时任务开关
  'cron_task_switch' => env('CRON_TASK_SWITCH', false),

  //定时任务
  /**
   * 定时执行的命令行格式如下
   * @var string $command 对应的是artisan命令
   * @var integer $interval 对应的是执行间隔，单位为秒
   * @var integer|false $align 首次执行是否对齐时间，当输入 0 的时候首次执行会
   * 等待到下一次0秒的时候才开始第一次执行，之后按间隔时间来重复执行。如果不需要对齐执行请输入false
   * [
   *      ['command'=>'xxx','interval'=>10,'align'=>0]
   * ]
   */
  'cron_tasks' => [],
];
