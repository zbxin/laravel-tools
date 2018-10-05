<?php

namespace ZhiEq\Middleware;

use Closure;
use Illuminate\Http\Request;
use ZhiEq\Contracts\MiddlewareExceptRoute;
use ZhiEq\Utils\ConvertJsonKeyFormat;

class CaseInputJson extends MiddlewareExceptRoute
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
            $request->replace($this->convertJsonKeyFormat($request->getContent(), config('tools.case_input_format'), true));
        }
        return $next($request);
    }
}
