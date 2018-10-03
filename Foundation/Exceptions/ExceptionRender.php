<?php

namespace ZhiEq\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Debug\Exception\FlattenException;
use ZhiEq\Contracts\Exception as CustomBaseException;

trait ExceptionRender
{
    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param Exception $exception
     * @return \Illuminate\Http\Response
     */

    public function renderInline($request, Exception $exception)
    {
        if ($exception instanceof AuthenticationException) {
            return errors('Unauthorized', 401);
        }
        if ($exception instanceof ValidationException) {
            logs()->error('validate exception');
            return errors($exception->validator->errors()->first());
        }
        if ($exception instanceof CustomBaseException) {
            logs()->error('custom exception');
            return errors($exception);
        }
        $fe = FlattenException::create($exception);
        $message = $fe->getStatusCode() == 404 ?
            'Sorry, the page you are looking for could not be found.'
            : debug_output($exception->getMessage(), 'Whoops, looks like something went wrong.');
        logs()->error('exception array', $fe->toArray());
        return $request->expectsJson() ? errors($message, 1, debug_output($fe->toArray()), $fe->getStatusCode(), $fe->getHeaders()) : parent::render($request, $exception);
    }
}
