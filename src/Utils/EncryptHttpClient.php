<?php

namespace ZhiEq\Utils;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use ZhiEq\ApiSignature\GuzzleMiddleware\ApiSignatureGuzzleMiddleware;
use ZhiEq\CaseJson\GuzzleMiddleware\CaseResponseGuzzleMiddleware;
use ZhiEq\Encrypt\GuzzleMiddleware\DecryptionBodyGuzzleMiddleware;
use ZhiEq\Encrypt\GuzzleMiddleware\EncryptionBodyGuzzleMiddleware;

/**
 * Class EncryptHttpClient
 * @package ZhiEq\Utils
 */

class EncryptHttpClient extends Client
{

    public function __construct($configs = [])
    {
        $stack = HandlerStack::create();
        $signatureMiddleware = new ApiSignatureGuzzleMiddleware(config('tools.api_signature_secret'));
        $encryptMiddleware = new EncryptionBodyGuzzleMiddleware(config('tools.aes_secret_key'));
        $decryptMiddleware = new DecryptionBodyGuzzleMiddleware(config('tools.aes_secret_key'));
        $caseMiddleware = new CaseResponseGuzzleMiddleware(config('tools.case_input_format'));
        $stack->push($encryptMiddleware);
        $stack->push($signatureMiddleware);
        $stack->push($caseMiddleware);
        $stack->push($decryptMiddleware);
        $configs = array_merge($configs, ['handler' => $stack]);
        parent::__construct($configs);
    }


}
