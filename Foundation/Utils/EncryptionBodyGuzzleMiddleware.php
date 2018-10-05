<?php

namespace ZhiEq\Utils;


use Psr\Http\Message\RequestInterface;

class EncryptionBodyGuzzleMiddleware
{
    protected $secretKey;

    public function __construct($secretKey)
    {
        $this->secretKey = $secretKey;
    }

    public function __invoke(callable $handler)
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            $body = $request->getBody();
            var_dump($body->getContents());
            $body->write('dddd');
            var_dump($body->getContents());
            return $handler($request, $options);
        };
    }
}
