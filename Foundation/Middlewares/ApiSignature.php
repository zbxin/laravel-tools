<?php

namespace App\Http\Middleware;

use App\Exceptions\Headers\SignatureInvalidException;
use Carbon\Carbon;
use Closure;
use Exception;
use Log;

class ApiSignature
{

    protected $requiredHeaders = [
        'X-Ca-Signature-Headers',
        'X-Ca-Timestamp',
        'X-Ca-Nonce',
        'X-Ca-Signature',
    ];

    protected $signElseHeaders = [
        'accept',
        'content-md5',
        'date',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /*
         * 强制检查传入的body必须为json格式
         */
        if (!$request->isJson()) {
            return errors('content-type only support application/json');
        }
        if (!$request->wantsJson()) {
            return errors('accept only support application/json');
        }
        if (!empty($request->getContent()) && empty($request->json())) {
            return errors('body must be json format');
        }
        /*
         * 检查签名必须的字段
         */
        foreach ($this->requiredHeaders as $headerKey) {
            if (!$request->hasHeader($headerKey)) {
                return errors($headerKey . ' required');
            }
        }
        /*
         * 检验请求的时间与实际时间的偏差值，超过偏差值的请求会被拒绝，防止回放攻击
         */
        info('request time' . $request->header('X-Ca-Timestamp'));
        try {
            $timeDiff = (new Carbon($request->header('X-Ca-Timestamp'), 'UTC'))->diffInSeconds(Carbon::now('UTC'), false);
            if ($timeDiff > 900 || $timeDiff < -900) {
                return errors('request time must between server time +-15 minutes');
            }
        } catch (Exception $exception) {
            return errors('X-Ca-Timestamp format invalid.must be format like 2017-01-01T00:00:00Z by UTC timezone');
        }
        /*
         * 根据请求的路径和请求的随机数进行校验，保证在15分钟内只能请求一次，结合上述时间校验防止回放攻击
         */
        $uniqueRequestStr = sha1('/' . $request->path() . "\n" . $request->header('X-Ca-Nonce'));
        if (cache()->get($uniqueRequestStr)) {
            return errors('request has handle');
        }
        cache()->put($uniqueRequestStr, app_id(), Carbon::now()->addMinutes(15));
        /*
         * 组装签名字符串的请求头部分
         */
        $signHeaders = explode(',', $request->header('X-Ca-Signature-Headers'));
        sort($signHeaders);
        $signHeaderString = implode("\n", collect($signHeaders)->map(function ($headerKey) use ($request) {
            return $headerKey . ':' . $request->header($headerKey);
        })->toArray());
        /*
         * 组装签名字符串的query部分
         */
        $signQuery = $request->query();
        ksort($signQuery);
        $signQueryString = implode('&', collect($signQuery)->map(function ($value, $key) {
            return $key . '=' . $value;
        })->toArray());
        /*
         * 租装签名字符串
         */
        $signString = strtoupper($request->method()) . "\n"
            . $request->header('Content-Type') . "\n"
            . $this->getContentEncode($request) . "\n"
            . $request->header('Accept') . "\n"
            . $request->header('X-Ca-Timestamp') . "\n"
            . $signHeaderString . "\n"
            . '/' . $request->path() . (empty($request->query()) ? '' : '?' . $signQueryString);
        Log::info('sign str:' . $signString, ['secret' => config('gateway.application_signature_secret')]);
        /*
         * 计算签名并对比请求的签名是否一致
         */
        $signature = base64_encode(hash_hmac('sha256', $signString, config('gateway.application_signature_secret'), true));
        Log::info('sign:' . $signature);
        if ($signature !== $request->header('X-Ca-Signature')) {
            throw new SignatureInvalidException($signString);
        }
        return $next($request);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return string
     */

    protected function getContentEncode($request)
    {
        if (env('APP_ENV') === 'testing' && ($request->getMethod() === 'GET' || $request->getMethod() === 'DELETE')) {
            return '';
        }
        return (empty($request->getContent()) ? '' : base64_encode(md5($request->getContent(), true)));
    }
}
