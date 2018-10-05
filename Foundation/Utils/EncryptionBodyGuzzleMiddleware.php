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
            $content = $request->getBody()->getContents();
            if (!empty($content)) {
                $request = $request->withBody(\GuzzleHttp\Psr7\stream_for(json_encode(['encryptionData' => AESEncrypt::encrypt($content, $this->secretKey)])));
            }
            return $handler($request, $options);
        };
    }
}
