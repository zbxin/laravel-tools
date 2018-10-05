<?php

namespace ZhiEq\Utils;

use Carbon\Carbon;
use function GuzzleHttp\Psr7\parse_query;
use Psr\Http\Message\RequestInterface;

class ApiSignatureGuzzleMiddleware
{
    protected $signSecret;

    public function __construct($signSecret)
    {
        $this->signSecret = $signSecret;
    }

    /**
     * Called when the middleware is handled.
     *
     * @param callable $handler
     *
     * @return \Closure
     */
    public function __invoke(callable $handler)
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            foreach ($this->baseHeaders() as $headerKey => $headerValue) {
                $request->withAddedHeader($headerKey, $headerValue);
            }
            $signHeaders = $request->getHeaders();
            $request->withAddedHeader('X-Ca-Signature-Headers', implode(',', $signHeaders));
            ksort($signHeaders);
            $signHeaderString = collect($signHeaders)->map(function ($headerValue, $headerKey) {
                return $headerKey . '=' . $headerValue;
            })->implode("\n");
            $signQuery = parse_query($request->getUri()->getQuery());
            ksort($signQuery);
            $signQueryString = collect($signQuery)->map(function ($queryValue, $queryKey) {
                return $queryKey . '=' . $queryValue;
            })->implode('&');
            $signString = strtoupper($request->getMethod()) . "\n"
                . $request->getHeader('Content-Type')[0] . "\n"
                . '' . "\n"
                . $request->getHeader('Accept')[0] . "\n"
                . $request->getHeader('X-Ca-Timestamp')[0] . "\n"
                . $signHeaderString . " \n"
                . $request->getUri()->getPath() . (empty($request->getUri()->getQuery()) ? '' : '?' . $signQueryString);
            logs()->info('request api signature', [
                'signString' => $signString,
            ]);
            $request->withAddedHeader('X-Ca-Signature', base64_encode(hash_hmac('sha256', $signString, $this->signSecret, true)));
            return $handler($request, $options);
        };
    }

    protected function baseHeaders()
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'X-Ca-Timestamp' => Carbon::now('UTC')->format('Y-m-dTH:i:s') . 'Z',
            'X-Ca-Nonce' => str_random(40),
        ];
    }

}
