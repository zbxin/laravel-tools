<?php

namespace ZhiEq\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use ZhiEq\Constant;
use ZhiEq\Contracts\MiddlewareExceptRoute;
use ZhiEq\Utils\ConvertJsonKeyFormat;

class StudlyCaseInputJson extends MiddlewareExceptRoute
{
    use ConvertJsonKeyFormat;

    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function subHandle($request, Closure $next)
    {
        if (!empty($request->getContent())) {
            $request->replace($this->convertJsonKeyFormat($request->getContent(), Constant::JSON_KEY_FORMAT_STUDLY_CASE, true));
        }
        return $next($request);
    }
}
