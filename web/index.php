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
use fkooman\OAuth\JsonCredentials;
use fkooman\Ini\IniReader;

ExceptionHandler::register();

$iniReader = IniReader::fromFile(
    dirname(__DIR__).'/config/server.ini'
);

$service = new Service();

// for user authentication
$userAuthentication = new BasicAuthentication(
    function ($userId) {
        $c = new JsonCredentials(dirname(__DIR__).'/config/users.json');

        return $c->getSecret($userId);
    },
    array(
        'realm' => 'OAuth',
    )
);

// for resource server authentication
$resourceServerAuthentication = new BasicAuthentication(
    function ($resourceServerId) {
        $c = new JsonCredentials(dirname(__DIR__).'/config/resource_servers.json');

        return $c->getSecret($resourceServerId);
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

$key = Key::load($iniReader->v('Security', 'Key'));

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
