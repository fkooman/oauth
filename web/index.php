<?php

require_once dirname(__DIR__).'/vendor/autoload.php';

use fkooman\Rest\Service;
use fkooman\Rest\PluginRegistry;
use fkooman\Rest\ExceptionHandler;
use fkooman\Rest\Plugin\Authentication\AuthenticationPlugin;
use fkooman\Rest\Plugin\Authentication\UserInfoInterface;
use fkooman\Rest\Plugin\Basic\BasicAuthentication;
use fkooman\Rest\Plugin\Bearer\BearerAuthentication;
use fkooman\Http\Request;
use fkooman\OAuth\OAuthServer;
use fkooman\OAuth\ResourceServerValidator;
use fkooman\OAuth\Impl\TwigTemplateManager;
use fkooman\OAuth\Impl\CryptoAuthorizationCode;
use fkooman\OAuth\Impl\CryptoAccessToken;
use fkooman\Crypto\Key;

ExceptionHandler::register();

$service = new Service();

$basicAuthentication = new BasicAuthentication(
    function ($userName) {
        return password_hash('adm1n', PASSWORD_DEFAULT);
    },
    array(
        'realm' => 'OAuth',
    )
);

$bearerAuthentication = new BearerAuthentication(new ResourceServerValidator(dirname(__DIR__).'/config/resource_servers.json'));

$authenticationPlugin = new AuthenticationPlugin();
$authenticationPlugin->registerAuthenticationPlugin($basicAuthentication, 'user');
$authenticationPlugin->registerAuthenticationPlugin($bearerAuthentication, 'resource_server');

$pluginRegistry = new PluginRegistry();
$pluginRegistry->registerDefaultPlugin($authenticationPlugin);

$service->setPluginRegistry($pluginRegistry);

$key = Key::load('eyJlIjoiNWFjMTBiNjgxYjQ1YmIwYTQxN2RjNjlhZWE0YjRmYzUiLCJzIjoiOGIzNTI3NDE0OTljMzAyODA2MzRhM2VmYTEwZWJjZGYzZGQ3ZWRhZWNjMGU1NWE5NDc1NDk1ZGE2NDVlNjJiNiJ9');

$o = new OAuthServer(
    new TwigTemplateManager(),
    new CryptoAuthorizationCode($key),
    new CryptoAccessToken($key)
);

$service->get(
    '/authorize',
    function (Request $request, UserInfoInterface $userInfo) use ($o) {
        return $o->getAuthorize($request, $userInfo);
    },
    array(
        'fkooman\Rest\Plugin\Authentication\AuthenticationPlugin' => array(
            'only' => 'user',
        ),
    )
);

$service->post(
    '/authorize',
    function (Request $request, UserInfoInterface $userInfo) use ($o) {
        return $o->postAuthorize($request, $userInfo);
    },
    array(
        'fkooman\Rest\Plugin\Authentication\AuthenticationPlugin' => array(
            'only' => 'user',
        ),
    )

);

$service->post(
    '/token',
    function (Request $request) use ($o) {
        return $o->postToken($request);
    },
    array(
        'fkooman\Rest\Plugin\Authentication\AuthenticationPlugin' => array(
            //'only' => array('fkooman\Rest\Plugin\Basic\BasicAuthentication'),
            'enabled' => false,
        ),
    )
);

$service->post(
    '/introspect',
    function (Request $request, UserInfoInterface $userInfo) use ($o) {
        return $o->postIntrospect($request, $userInfo);
    },
    // FIXME: this one must use Bearer!
    array(
        'fkooman\Rest\Plugin\Authentication\AuthenticationPlugin' => array(
            'only' => 'resource_server',
        ),
    )
);

$service->run()->send();
