<?php

namespace ZhiEq\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use ZhiEq\Constant;
use ZhiEq\Contracts\MiddlewareExceptRoute;
use ZhiEq\Utils\ConvertJsonKeyFormat;

class StudlyCaseOutputJson extends MiddlewareExceptRoute
{
    use ConvertJsonKeyFormat;

    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function subHandle($request, Closure $next)
    {
        /**
         * @var Response $response
         */
        $response = $next($request);
        if (!empty($response->getContent()) && !empty(json_decode($response->getContent(), true))) {
            $response->setContent($this->convertJsonKeyFormat($response->getContent(), Constant::JSON_KEY_FORMAT_STUDLY_CASE));
        }
        return $response;
    }
}
