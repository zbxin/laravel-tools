<?php

namespace ZhiEq\GeeTest;

use Carbon\Carbon;
use Illuminate\Support\Facades\Request;
use Psr\SimpleCache\InvalidArgumentException;

class GeeTest
{
    /**
     * @var \Illuminate\Config\Repository
     */

    protected $config;

    /**
     * @var GeeTestLib
     */

    protected $gtLib;

    /**
     * GeeTest constructor.
     * @param $config
     */

    public function __construct($config)
    {
        $this->config = $config;
        $this->gtLib = new GeeTestLib($this->config->get('gee_test.id'), $this->config->get('gee_test.key'));
    }

    /**
     * 生成验证码
     *
     * @param string $type
     * @param null $userId
     * @return array|bool
     */

    public function generateCaptcha($type = 'web', $userId = null)
    {
        $params = ['client_type' => $type, 'ip_address' => Request::ip()];
        if ($userId) $params['user_id'] = sha1($userId);
        $result = $this->gtLib->pre_process($params);
        $response = $this->gtLib->get_response();
        $resultKey = str_random(40);
        try {
            if (!cache()->set($resultKey, json_encode(array_merge($params, ['status' => $result])), Carbon::now()->addMinutes(5))) {
                return false;
            }
            return array_merge($response, ['status' => $resultKey]);
        } catch (\Exception $e) {
            return false;
        } catch (InvalidArgumentException $e) {
            return false;
        }
    }

    /**
     * 校验验证码
     *
     * @param $statusKey
     * @param $challenge
     * @param $validate
     * @param $secCode
     * @return int
     */

    public function verifyCaptcha($statusKey, $challenge, $validate, $secCode)
    {
        try {
            if (!$captchaInfo = cache()->get($statusKey)) {
                throw new CaptchaTimeoutException();
            }
            if ($captchaInfo['status'] === 1) {
                unset($captchaInfo['status']);
                return $this->gtLib->success_validate($challenge, $validate, $secCode, $captchaInfo);
            }
            return $this->gtLib->fail_validate($challenge, $validate, $secCode);
        } catch (\Exception $e) {
            throw new CaptchaTimeoutException();
        }
    }
}
