<?php

namespace Zbxin\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Debug\Exception\FlattenException;
use Zbxin\CaseJson\ConvertJsonKeyFormat;
use Zbxin\CaseJson\Exceptions\JsonKeyFormatInvalidException;
use Zbxin\Contracts\Exception as CustomBaseException;

trait ExceptionRender
{

    /**
     * @param Request $request
     * @param Exception $exception
     * @return \Illuminate\Http\JsonResponse
     */

    public function renderInline($request, Exception $exception)
    {
        if ($exception instanceof AuthenticationException) {
            return $this->renderError('Unauthorized', 401);
        }
        if ($exception instanceof ValidationException) {
            logs()->error('validate exception');
            return $this->renderError($exception->validator->errors()->first());
        }
        if ($exception instanceof CustomBaseException) {
            logs()->error('custom exception');
            return $this->renderError($exception);
        }
        if ($exception instanceof JsonKeyFormatInvalidException) {
            return parent::render($request, $exception);
        }
        $fe = FlattenException::create($exception);
        $message = $fe->getStatusCode() == 404 ?
            'Sorry, the page you are looking for could not be found.'
            : debug_output($exception->getMessage(), 'Whoops, looks like something went wrong.');
        logs()->error('exception array', $fe->toArray());
        return $request->expectsJson() ? $this->renderError($message, 1, debug_output($fe->toArray()), $fe->getStatusCode(), $fe->getHeaders()) : parent::render($request, $exception);
    }

    /**
     * @param $message
     * @param int $code
     * @param array $data
     * @param int $status
     * @param array $headers
     * @param int $encodingOptions
     * @return \Illuminate\Http\JsonResponse
     */

    protected function renderError($message, $code = 1, $data = [], $status = 200, $headers = [], $encodingOptions = JSON_UNESCAPED_UNICODE)
    {
        $response = errors($message, $code, $data, $status, $headers, $encodingOptions);
        $response->setContent(ConvertJsonKeyFormat::convertJsonKeyFormat($response->getContent(), config('tools.case_output_format')));
        return $response;
    }
}
