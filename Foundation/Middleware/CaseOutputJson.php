<?php

namespace ZhiEq\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use ZhiEq\Contracts\MiddlewareExceptRoute;
use ZhiEq\Utils\ConvertJsonKeyFormat;

class CaseOutputJson extends MiddlewareExceptRoute
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
        if(!empty($response->getContent()) && !empty(json_decode($response->getContent(), true))){
            $response->setContent($this->convertJsonKeyFormat($response->getContent(),config('tools.case_output_format')));
        }
        return $response;
    }
}
