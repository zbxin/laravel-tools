<?php

namespace ZhiEq\Contracts;

use Closure;
use Illuminate\Http\Request;

abstract class MiddlewareExceptRoute
{
    protected $expectRoute = [

    ];

    /**
     * @param \Illuminate\Http\Request $request
     * @return mixed
     * @internal param Closure $next
     */

    public function checkExpectRoute($request)
    {
        $result = false;
        foreach ($this->expectRoute as $path) {
            if (starts_with($request->path(), substr($path, 1))) {
                $result = true;
                break;
            }
        }
        return $result;
    }

    /**
     * @param $request
     * @param Closure $next
     * @return mixed
     */

    public function handle($request, Closure $next)
    {
        if ($this->checkExpectRoute($request)) {
            return $next($request);
        }
        return $this->subHandle($request, $next);
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */

    abstract public function subHandle($request, Closure $next);
}
