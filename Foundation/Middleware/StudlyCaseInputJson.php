<?php

namespace ZhiEq\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use ZhiEq\Contracts\MiddlewareExceptRoute;

class StudlyCaseInputJson extends MiddlewareExceptRoute
{

    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function subHandle($request, Closure $next)
    {
        if (!empty($request->json())) {
            $request->replace(studly_case_array_keys(collect($request->json())->toArray()));
        }
        return $next($request);
    }
}
