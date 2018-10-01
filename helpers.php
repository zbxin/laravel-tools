<?php

if (!function_exists('errors')) {

    /**
     * 返回处理失败结果
     * @param $message
     * @param int $code
     * @param array $data
     * @param int $status
     * @param array $headers
     * @param int $encodingOptions
     * @return \Illuminate\Http\JsonResponse
     */

    function errors($message, $code = 1, $data = [], $status = 500, $headers = [], $encodingOptions = JSON_UNESCAPED_UNICODE)
    {
        $returnInfo = [
            'Request' => \Iit\RedisMonolog\RedisFormatter::$logId,
        ];
        if ($message instanceof \App\Contracts\Base\Exception) {
            $returnInfo['Code'] = $message->getCode();
            $returnInfo['Message'] = $message->getMessage();
            $returnInfo['Data'] = $message->getData();
            if (is_debug() && !is_test()) {
                $returnInfo['Debug'] = $message->getDebug();
                $returnInfo['Exception'] = $message->getTraceAsString();
            }
            $status = $message->getStatusCode();
            $headers = $message->getHeaders();
        } else {
            list($errorCode, $errorMessage) = (new \App\Extend\ErrorsManager())->getErrorInfo($message);
            $code = $code === 1 ? ($errorCode === null ? 99999 : $errorCode) : $code;
            $message = $errorMessage === null ? $message : $errorMessage;
            $returnInfo['Code'] = $code;
            $returnInfo['Message'] = $message;
            !empty($data) && $returnInfo['Data'] = filter_null($data);
        }
        Log::error('request has something errors', $returnInfo);
        return response()->json($returnInfo, $status, $headers, $encodingOptions);
    }
}

if (!function_exists('success')) {

    /**
     * 返回处理成功结果
     * @param array $data
     * @param null $message
     * @param array $headers
     * @param int $encodingOptions
     * @return \Illuminate\Http\JsonResponse
     */

    function success($data = [], $message = null, $headers = [], $encodingOptions = JSON_UNESCAPED_UNICODE)
    {
        Log::info('request return success at:' . microtime(true));
        Log::info('request return success data', ['data' => [
            'Request' => app('request')->header('X-Request-Id', app_id()),
            'Code' => 0,
            'Message' => $message === null ? trans('tips.request_handle_successful') : $message,
            'Data' => is_string($data) ? $data : (is_array($data) ? filter_null(studly_case_array_keys($data)) : filter_null(studly_case_array_keys(collect($data)->toArray()))),
        ]]);
        return response()->json([
            'Request' => app('request')->header('X-Request-Id', app_id()),
            'Code' => 0,
            'Message' => $message === null ? trans('tips.request_handle_successful') : $message,
            'Data' => is_string($data) ? $data : (is_array($data) ? filter_null(studly_case_array_keys($data)) : filter_null(studly_case_array_keys(collect($data)->toArray()))),
        ], 200, $headers, $encodingOptions);
    }
}

if (!function_exists('config_path')) {

    /**
     * 获取配置文件路径
     *
     * @param  string $path
     * @return string
     */

    function config_path($path = '')
    {
        return app()->basePath() . '/config' . ($path ? '/' . $path : $path);
    }
}

if (!function_exists('studly_case_array_keys')) {


    /**
     * 转换key下划线模式为首字母大写模式
     *
     * @param array $array
     * @return array
     */

    function studly_case_array_keys(array $array)
    {
        $return = [];
        foreach ($array as $key => $item) {
            if (is_array($item)) {
                $return[studly_case($key)] = studly_case_array_keys($item);
            } else if ($item instanceof \Illuminate\Support\Collection) {
                $return[studly_case($key)] = studly_case_array_keys($item->toArray());
            } else {
                $return[studly_case($key)] = $item;
            }
        }
        return $return;
    }
}

if (!function_exists('snake_case_array_keys')) {


    /**
     * 转换key首字母大写模式为下划线模式
     *
     * @param array $array
     * @return array
     */

    function snake_case_array_keys(array $array)
    {
        $return = [];
        foreach ($array as $key => $item) {
            if (is_array($item)) {
                $return[snake_case($key)] = snake_case_array_keys($item);
            } else if ($item instanceof \Illuminate\Support\Collection) {
                $return[snake_case($key)] = snake_case_array_keys($item->toArray());
            } else {
                $return[snake_case($key)] = $item;
            }
        }
        return $return;
    }
}

if (!function_exists('camel_case_array_keys')) {


    /**
     * 转换key首字母大写模式为驼峰式
     *
     * @param array $array
     * @return array
     */

    function camel_case_array_keys(array $array)
    {
        $return = [];
        foreach ($array as $key => $item) {
            if (is_array($item)) {
                $return[camel_case($key)] = camel_case_array_keys($item);
            } else if ($item instanceof \Illuminate\Support\Collection) {
                $return[camel_case($key)] = camel_case_array_keys($item->toArray());
            } else {
                $return[camel_case($key)] = $item;
            }
        }
        return $return;
    }
}

if (!function_exists('studly_case_array')) {


    /**
     * 转换下划线模式为首字母大写模式
     *
     * @param array $array
     * @return array
     */

    function studly_case_array(array $array)
    {
        $return = [];
        foreach ($array as $key => $item) {
            if (is_array($item)) {
                $return[$key] = studly_case_array($item);
            } else if ($item instanceof \Illuminate\Support\Collection) {
                $return[$key] = studly_case_array($item->toArray());
            } else {
                $return[$key] = studly_case($item);
            }
        }
        return $return;
    }
}

if (!function_exists('snake_case_array')) {


    /**
     * 转换key首字母大写模式为下划线模式
     *
     * @param array $array
     * @return array
     */

    function snake_case_array(array $array)
    {
        $return = [];
        foreach ($array as $key => $item) {
            if (is_array($item)) {
                $return[$key] = snake_case_array($item);
            } else if ($item instanceof \Illuminate\Support\Collection) {
                $return[$key] = snake_case_array($item->toArray());
            } else {
                $return[$key] = snake_case($item);
            }
        }
        return $return;
    }
}

if (!function_exists('camel_case_array')) {


    /**
     * 转换key首字母大写模式为驼峰式
     *
     * @param array $array
     * @return array
     */

    function camel_case_array(array $array)
    {
        $return = [];
        foreach ($array as $key => $item) {
            if (is_array($item)) {
                $return[$key] = camel_case_array($item);
            } else if ($item instanceof \Illuminate\Support\Collection) {
                $return[$key] = camel_case_array($item->toArray());
            } else {
                $return[$key] = camel_case($item);
            }
        }
        return $return;
    }
}

if (!function_exists('is_debug')) {

    /**
     * 根据环境判断是否调试环境
     *
     * @return bool
     */

    function is_debug()
    {
        return config('app.debug');
    }
}

if (!function_exists('is_test')) {

    /**
     * 根据环境判断是否调试环境
     *
     * @return bool
     */

    function is_test()
    {
        return app()->environment() === 'testing';
    }
}

if (!function_exists('debug_output')) {

    /**
     * 根据环境判断是否输出调试信息
     *
     * @param $debugInfo
     * @param null $tips
     * @return array|null
     */

    function debug_output($debugInfo, $tips = null)
    {
        return is_debug() ? $debugInfo : ($tips === null ? (is_array($debugInfo) ? [] : null) : $tips);
    }
}

if (!function_exists('filter_null')) {

    /**
     * 根据环境判断是否输出调试信息
     *
     * @param $filterData
     * @return array|null
     * @internal param $debugInfo
     */

    function filter_null($filterData)
    {
        foreach ($filterData as $key => &$item) {
            if (is_array($item)) {
                $item = filter_null($item);
            } else if ($item === null) {
                $item = '';
            }
        }
        return $filterData;
    }
}

if (!function_exists('identity')) {

    /**
     * 返回证件管理器
     *
     * @return Extend\Identity\IdentityManager
     */

    function identity()
    {
        return app('identity');
    }
}

if (!function_exists('verification_code')) {

    /**
     * 返回验证码管理器
     *
     * @return Extend\VerificationCode\VerificationCodeManager
     */

    function verification_code()
    {
        return app('verification_code');
    }
}

if (!function_exists('cache')) {

    /**
     * 返回缓存类
     *
     * @return \Illuminate\Cache\Repository
     */

    function cache()
    {
        return app('cache');
    }
}

if (!function_exists('http')) {

    /**
     * GuzzleHttp客户端辅助方法
     *
     * @return \GuzzleHttp\Client
     */

    function http()
    {
        return app('http');
    }
}

if (!function_exists('app_id')) {

    /**
     * @return string
     */

    function app_id()
    {
        return \Iit\RedisMonolog\RedisFormatter::$logId;
    }
}

if (!function_exists('auth_admin')) {

    /**
     * @return \App\Models\PersonnelFile|\App\Models\User|\Illuminate\Contracts\Auth\Authenticatable
     */

    function auth_admin()
    {
        if (!Auth::guest()) {
            return Auth::user();
        }
        if (config('request.user_id') === null) {
            return null;
        }
        if (config('request.auth_guard') === null) {
            return Auth::loginUsingId(config('request.user_id'));
        }
        return Auth::guard(config('request.auth_guard'))->loginUsingId(config('request.user_id'));
    }
}

if (!function_exists('storage_private')) {

    /**
     * @return \OSS\OssClient
     */

    function storage_private()
    {
        return app('oss_private');
    }
}

if (!function_exists('storage_public')) {

    /**
     * @return \OSS\OssClient
     */

    function storage_public()
    {
        return app('oss_public');
    }
}

if (!function_exists('storage_private_url')) {

    /**
     * @param $object
     * @param int $timeout
     * @return string
     */

    function storage_private_url($object, $timeout = 60)
    {
        return empty($object) ? null : str_replace('http://', 'https://', str_replace(config('filesystems.disks.oss.bucket') . '.' . config('filesystems.disks.oss.endpoint'),
            config('filesystems.disks.oss.host'), storage_private()->signUrl(config('filesystems.disks.oss.bucket'), $object, $timeout)));
    }
}

if (!function_exists('storage_public_url')) {

    /**
     * @param $object
     * @return string
     */

    function storage_public_url($object)
    {
        return empty($object) ? null : 'https://' . config('filesystems.disks.oss_public.host') . '/' . $object;
    }
}

if (!function_exists('oss_upload_policy')) {

    /**
     * @param $key
     * @param $secret
     * @param $host
     * @param int $timeout
     * @param int $maxSize
     * @return string
     */

    function oss_upload_policy($key, $secret, $host, $timeout = 5, $maxSize = 2097152)
    {
        $baseTime = Carbon\Carbon::now()->addMinutes($timeout);
        $expiration = substr($baseTime->toIso8601String(), 0, strpos($baseTime->toIso8601String(), '+')) . "Z";
        $conditions[] = ['content-length-range', 0, $maxSize];
        $signArray = [
            'expiration' => $expiration,
            'conditions' => $conditions
        ];
        $policy = base64_encode(json_encode($signArray));
        $sign = base64_encode(hash_hmac('sha1', $policy, $secret, true));
        return success([
            'AccessId' => $key,
            'Host' => $host,
            'Policy' => $policy,
            'Signature' => $sign,
            'Expire' => $expiration
        ]);
    }
}

if (!function_exists('code_version')) {

    /**
     * @param null $default
     * @return string
     */

    function code_version($default = null)
    {
        $path = base_path('version');
        if (file_exists($path)) {
            return file_get_contents($path);
        }
        return $default;
    }
}

if (!function_exists('full_pinyin')) {

    /**
     * @param $string
     * @return string
     */

    function full_pinyin($string)
    {
        return \App\Caches\Forever::getCache(sha1($string), ['pinyin'], function () use ($string) {
            return implode('', app('pinyin')->convert($string));
        });
    }
}

if (!function_exists('short_pinyin')) {

    /**
     * @param $string
     * @return string
     */

    function short_pinyin($string)
    {
        return \App\Caches\Forever::getCache(sha1($string), ['short_pinyin'], function () use ($string) {
            return app('pinyin')->abbr($string);
        });
    }
}