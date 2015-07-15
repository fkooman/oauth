<?php

require_once dirname(__DIR__).'/vendor/autoload.php';

use fkooman\Rest\Service;
use fkooman\Rest\PluginRegistry;
use fkooman\Rest\ExceptionHandler;
use fkooman\OAuth\AuthenticationPlugin;
use fkooman\Rest\Plugin\Authentication\UserInfoInterface;
use fkooman\Rest\Plugin\Basic\BasicAuthentication;
use fkooman\Http\Request;
use fkooman\OAuth\OAuthServer;
use fkooman\OAuth\Impl\TwigTemplateManager;
use fkooman\OAuth\Impl\CryptoAuthorizationCode;
use fkooman\OAuth\Impl\CryptoAccessToken;
use fkooman\Crypto\Key;
use fkooman\Json\Json;

ExceptionHandler::register();

$service = new Service();

// for user authentication
$userAuthentication = new BasicAuthentication(
    function ($userName) {
        if ('admin' === $userName) {
            return password_hash('adm1n', PASSWORD_DEFAULT);
        } elseif ('fkooman' === $userName) {
            return password_hash('foobar', PASSWORD_DEFAULT);
        }
    },
    array(
        'realm' => 'OAuth',
    )
);

// for resource server authentication
$resourceServerAuthentication = new BasicAuthentication(
    function ($resourceServerId) {
        $resourceServerData = Json::decodeFile(dirname(__DIR__).'/config/resource_servers.json');
        if (array_key_exists($resourceServerId, $resourceServerData)) {
            return password_hash($resourceServerData[$resourceServerId]['secret'], PASSWORD_DEFAULT);
        }

        return false;
    },
    array(
        'realm' => 'OAuth',
    )
);

$authenticationPlugin = new AuthenticationPlugin();
$authenticationPlugin->registerAuthenticationPlugin($userAuthentication, 'user');
$authenticationPlugin->registerAuthenticationPlugin($resourceServerAuthentication, 'resource_server');

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
        'fkooman\OAuth\AuthenticationPlugin' => array(
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
        'fkooman\OAuth\AuthenticationPlugin' => array(
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
        'fkooman\OAuth\AuthenticationPlugin' => array(
            //'only' => 'client',
            'enabled' => false,
        ),
    )
);

$service->post(
    '/introspect',
    function (Request $request, UserInfoInterface $userInfo) use ($o) {
        return $o->postIntrospect($request, $userInfo);
    },
    array(
        'fkooman\OAuth\AuthenticationPlugin' => array(
            'only' => 'resource_server',
        ),
    )
);

$service->run()->send();
