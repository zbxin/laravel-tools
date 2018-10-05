<?php

namespace ZhiEq\Utils;

use GuzzleHttp\Promise\Promise;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use ZhiEq\Constant;

class CaseResponseGuzzleMiddleware
{
    use ConvertJsonKeyFormat;

    protected $caseType;

    public function __construct($caseType = Constant::JSON_KEY_FORMAT_CAMEL_CASE)
    {
        $this->caseType = $caseType;
    }

    /**
     * @param callable $handler
     * @return \Closure
     */

    public function __invoke(callable $handler)
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            /**
             * @var Promise $promise
             */
            $promise = $handler($request, $options);
            return $promise->then(
                function (ResponseInterface $response) {
                    $content = $response->getBody()->getContents();
                    if (!empty($content)) {
                        $response = $response->withBody(\GuzzleHttp\Psr7\stream_for($this->convertJsonKeyFormat($content, $this->caseType)));
                    }
                    return $response;
                }
            );
        };
    }
}
