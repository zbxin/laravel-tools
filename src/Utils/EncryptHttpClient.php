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
        $signatureMiddleware = new ApiSignatureGuzzleMiddleware(isset($configs['signatureSecret']) ? $configs['signatureSecret'] : config('tools.api_signature_secret'));
        $encryptMiddleware = new EncryptionBodyGuzzleMiddleware(isset($configs['encryptSecret']) ? $configs['encryptSecret'] : config('tools.aes_secret_key'));
        $decryptMiddleware = new DecryptionBodyGuzzleMiddleware(isset($configs['encryptSecret']) ? $configs['encryptSecret'] : config('tools.aes_secret_key'));
        $caseMiddleware = new CaseResponseGuzzleMiddleware(isset($configs['caseFormat']) ? $configs['caseFormat'] : config('tools.case_input_format'));
        $stack->push($encryptMiddleware);
        $stack->push($signatureMiddleware);
        $stack->push($caseMiddleware);
        $stack->push($decryptMiddleware);
        $configs = array_merge($configs, ['handler' => $stack]);
        parent::__construct($configs);
    }


}
