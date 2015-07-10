<?php

require_once dirname(__DIR__).'/vendor/autoload.php';

use fkooman\Rest\Service;
use fkooman\Http\Request;
use fkooman\OAuth\OAuthServer;
use fkooman\OAuth\Impl\TwigTemplateManager;
use fkooman\OAuth\Impl\CryptoAuthorizationCode;
use fkooman\OAuth\Impl\CryptoAccessToken;

$service = new Service();

$o = new OAuthServer(
    new TwigTemplateManager(),
    new CryptoAuthorizationCode('7c34796dadace5c974b049774b3df89a', 'df3baee06309a11493d4c16bb70d09df'),
    new CryptoAccessToken('d6240461387e6aab9b0dafeda51d4504', '8aa5800fa8805ba6c777734e9d646544')
);

$service->get(
    '/authorize',
    function (Request $request) use ($o) {
        return $o->getAuthorize($request);
    }
);

$service->post(
    '/authorize',
    function (Request $request) use ($o) {
        return $o->postAuthorize($request);
    }
);

$service->post(
    '/token',
    function (Request $request) use ($o) {
        return $o->postToken($request);
    }
);

$service->run()->send();
