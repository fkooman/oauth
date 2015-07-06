<?php

require_once dirname(__DIR__).'/vendor/autoload.php';

use fkooman\Rest\Service;
use fkooman\Http\Request;
use fkooman\OAuth\OAuthServer;
use fkooman\OAuth\TemplateManager;
use fkooman\OAuth\JwsAuthorizationCode;

$service = new Service();

$o = new OAuthServer(new TemplateManager(), new JwsAuthorizationCode('secret'));

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
