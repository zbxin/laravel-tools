<?php

namespace ZhiEq\Middleware;


use Closure;
use Illuminate\Http\Request;
use ZhiEq\Constant;
use ZhiEq\Contracts\MiddlewareExceptRoute;
use ZhiEq\Utils\ConvertJsonKeyFormat;

class CamelCaseInputJson extends MiddlewareExceptRoute
{
    use ConvertJsonKeyFormat;

    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function subHandle($request, Closure $next)
    {
        if (!empty($request->json())) {
            $request->replace($this->convertJsonKeyFormat($request->getContent(), Constant::JSON_KEY_FORMAT_CAMEL_CASE, true));
        }
        return $next($request);
    }
}
