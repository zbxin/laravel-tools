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
        logs()->info('request return error at:' . microtime(true));
        $returnInfo = [
            'request' => app_id(),
        ];
        if ($message instanceof \Zhieq\Contracts\Exception) {
            $returnInfo['code'] = $message->getCode();
            $returnInfo['message'] = $message->getMessage();
            $returnInfo['data'] = filter_null($message->getData());
            if (is_debug() && !is_test()) {
                $returnInfo['debug'] = $message->getDebug();
                $returnInfo['exception'] = $message->getTraceAsString();
            }
            $status = $message->getStatusCode();
            $headers = $message->getHeaders();
        } else {
            $code = $code === 1 ? 99999 : $code;
            $returnInfo['code'] = $code;
            $returnInfo['message'] = $message;
            !empty($data) && $returnInfo['data'] = filter_null($data);
        }
        logs()->error('request has something errors', $returnInfo);
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
        logs()->info('request return success at:' . microtime(true));
        $returnData = [
            'request' => app_id(),
            'code' => 0,
            'message' => $message === null ? trans('request_handle_successful') : $message,
            'data' => is_string($data) ? $data : (is_array($data) ? filter_null($data) : filter_null(collect($data)->toArray())),
        ];
        logs()->info('request return success data', $returnData);
        return response()->json($returnData, 200, $headers, $encodingOptions);
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

if (!function_exists('app_id')) {

    /**
     * @return string
     */

    function app_id()
    {
        if (config('app.id') === null) {
            config('app.id', uuid());
        }
        return config('app.id');
    }
}


if (!function_exists('uuid')) {

    /**
     * @return string
     */

    function uuid()
    {
        try {
            return \Ramsey\Uuid\Uuid::uuid4()->toString();
        } catch (Exception $exception) {
            return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                // 32 bits for "time_low"
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),

                // 16 bits for "time_mid"
                mt_rand(0, 0xffff),

                // 16 bits for "time_hi_and_version",
                // four most significant bits holds version number 4
                mt_rand(0, 0x0fff) | 0x4000,

                // 16 bits, 8 bits for "clk_seq_hi_res",
                // 8 bits for "clk_seq_low",
                // two most significant bits holds zero and one for variant DCE1.1
                mt_rand(0, 0x3fff) | 0x8000,

                // 48 bits for "node"
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );
        }
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

