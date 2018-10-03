<?php

namespace ZhiEq\Middleware;


use Closure;
use Illuminate\Http\Request;
use ZhiEq\Contracts\MiddlewareExceptRoute;

class CamelCaseInputJson extends MiddlewareExceptRoute
{

    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function subHandle($request, Closure $next)
    {
        if (!empty($request->json())) {
            $request->replace(camel_case_array_keys(collect($request->json())->toArray()));
        }
        return $next($request);
    }
}
