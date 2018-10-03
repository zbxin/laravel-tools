<?php

namespace ZhiEq\Middleware;

use Closure;
use Illuminate\Http\Request;
use ZhiEq\Contracts\MiddlewareExceptRoute;

class EncryptionBody extends MiddlewareExceptRoute
{

    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function subHandle($request, Closure $next)
    {
        // TODO: Implement subHandle() method.
    }
}
