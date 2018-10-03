<?php

namespace ZhiEq\Middleware;

use Closure;
use Illuminate\Http\Request;
use ZhiEq\Contracts\MiddlewareExceptRoute;
use ZhiEq\Utils\AESEncrypt;

class DecryptionBody extends MiddlewareExceptRoute
{

    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function subHandle($request, Closure $next)
    {
        if (!empty($request->json()) && !empty($request->json('encryptionData'))) {
            $request->replace(json_decode(AESEncrypt::quickDecrypt($request->json('encryptionData')), true));
        }
        return $next($request);
    }
}
