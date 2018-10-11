<?php

namespace ZhiEq\Utils;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use ZhiEq\ApiSignature\GuzzleMiddleware\ApiSignatureGuzzleMiddleware;
use ZhiEq\CaseJson\GuzzleMiddleware\CaseResponseGuzzleMiddleware;

/**
 * Class SignatureHttpClient
 * @package ZhiEq\Utils
 */

class SignatureHttpClient extends Client
{
    public function __construct($configs = [])
    {
        $stack = HandlerStack::create();
        $signatureMiddleware = new ApiSignatureGuzzleMiddleware(config('tools.api_signature_secret'));
        $caseMiddleware = new CaseResponseGuzzleMiddleware(config('tools.case_input_format'));
        $stack->push($signatureMiddleware);
        $stack->push($caseMiddleware);
        $configs = array_merge($configs, ['handler' => $stack]);
        parent::__construct($configs);
    }
}
