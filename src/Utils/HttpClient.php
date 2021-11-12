<?php

namespace Zbxin\Utils;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\HandlerStack;
use Illuminate\Http\Request;
use Psr\Http\Message\ResponseInterface;
use Zbxin\ApiSignature\GuzzleMiddleware\ApiSignatureGuzzleMiddleware;
use Zbxin\CaseJson\GuzzleMiddleware\CaseResponseGuzzleMiddleware;
use Zbxin\Encrypt\AESEncrypt;
use Zbxin\Encrypt\GuzzleMiddleware\DecryptionBodyGuzzleMiddleware;
use Zbxin\Encrypt\GuzzleMiddleware\EncryptionBodyGuzzleMiddleware;

class HttpClient extends Client
{
    protected static $syncHeaderKey = 'X-Sync-Params';

    /**
     * @param $configs
     * @return HandlerStack
     */

    protected static function createSignatureStack($configs)
    {
        $stack = HandlerStack::create();
        $signatureMiddleware = new ApiSignatureGuzzleMiddleware(isset($configs['signatureSecret']) ? $configs['signatureSecret'] : config('tools.api_signature_secret'));
        $caseMiddleware = new CaseResponseGuzzleMiddleware(isset($configs['caseFormat']) ? $configs['caseFormat'] : config('tools.case_input_format'));
        $stack->push($signatureMiddleware);
        $stack->push($caseMiddleware);
        return $stack;
    }

    /**
     * @param $configs
     * @return HandlerStack
     */

    protected static function createEncryptStack($configs)
    {
        $stack = HandlerStack::create();
        $signatureMiddleware = new ApiSignatureGuzzleMiddleware(isset($configs['signatureSecret']) ? $configs['signatureSecret'] : config('tools.api_signature_secret'));
        $encryptMiddleware = new EncryptionBodyGuzzleMiddleware(isset($configs['encryptSecret']) ? $configs['encryptSecret'] : config('tools.aes_secret_key'));
        $decryptMiddleware = new DecryptionBodyGuzzleMiddleware(isset($configs['encryptSecret']) ? $configs['encryptSecret'] : config('tools.aes_secret_key'));
        $caseMiddleware = new CaseResponseGuzzleMiddleware(isset($configs['caseFormat']) ? $configs['caseFormat'] : config('tools.case_input_format'));
        $stack->push($encryptMiddleware);
        $stack->push($signatureMiddleware);
        $stack->push($caseMiddleware);
        $stack->push($decryptMiddleware);
        return $stack;
    }

    /**
     * @param array $configs
     * @return HttpClient
     */

    public static function createEncryptClient($configs = [])
    {
        $configs = array_merge(['handler' => self::createEncryptStack($configs)], $configs);
        return new self($configs);
    }

    /**
     * @param array $configs
     * @return HttpClient
     */

    public static function createSignatureClient($configs = [])
    {
        $configs = array_merge(['handler' => self::createSignatureStack($configs)], $configs);
        return new self($configs);
    }

    /**
     * @param callable $request
     * @param array $configs
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */

    public static function syncRequest(callable $request, array $configs = [])
    {
        try {
            /**
             * @var $response ResponseInterface
             */
            $response = $request(self::createEncryptClient($configs));
            return response($response->getBody()->getContents(), $response->getStatusCode(), $response->getHeaders());
        } catch (\Exception $exception) {
            if ($exception instanceof ClientException) {
                return response($exception->getResponse()->getBody()->getContents(), $exception->getResponse()->getStatusCode(), $exception->getResponse()->getHeaders());
            } elseif ($exception instanceof ServerException) {
                return response($exception->getResponse()->getBody()->getContents(), $exception->getResponse()->getStatusCode(), $exception->getResponse()->getHeaders());
            }
            return errors($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @param Request $request
     * @return array
     */

    public static function syncListHeaders(Request $request = null)
    {
        $request = $request === null ? \request()->instance() : $request;
        $headers = [];
        $headerKeys = [
            'X-Search-Keywords', 'X-Order-Field', 'X-Order-Type', 'X-Per-Page', 'X-Page'
        ];
        foreach ($headerKeys as $headerKey) {
            if (!empty($request->header($headerKey))) {
                $headers[$headerKey] = $request->header($headerKey);
            }
        }
        return $headers;
    }

    /**
     * @param array $params
     * @param null $encryptKey
     * @return array
     */

    public static function encodeSyncHeaderParams(array $params, $encryptKey = null)
    {
        $params = json_encode($params);
        return [self::$syncHeaderKey => $encryptKey === null ? AESEncrypt::quickEncrypt($params) : AESEncrypt::encrypt($params, $encryptKey)];
    }

    /**
     * @param Request|null $request
     * @param null $encryptKey
     * @return array|mixed
     */

    public static function decodeSyncHeaderParams(Request $request = null, $encryptKey = null)
    {
        $request = $request === null ? \request()->instance() : $request;
        if (!$encryptParams = $request->header(self::$syncHeaderKey)) {
            return [];
        }
        return json_decode($encryptKey === null ? AESEncrypt::quickDecrypt($encryptParams) : AESEncrypt::decrypt($encryptParams, $encryptKey), true);
    }

}