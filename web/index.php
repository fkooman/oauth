<?php

require_once dirname(__DIR__).'/vendor/autoload.php';

use fkooman\Rest\Service;
use fkooman\Rest\PluginRegistry;
use fkooman\Rest\ExceptionHandler;
use fkooman\Rest\Plugin\Authentication\AuthenticationPlugin;
use fkooman\Rest\Plugin\Authentication\UserInfoInterface;
use fkooman\Rest\Plugin\Basic\BasicAuthentication;
use fkooman\Http\Request;
use fkooman\OAuth\OAuthServer;
use fkooman\OAuth\Impl\TwigTemplateManager;
use fkooman\OAuth\Impl\CryptoAuthorizationCode;
use fkooman\OAuth\Impl\CryptoAccessToken;

ExceptionHandler::register();

$service = new Service();

$basicAuthentication = new BasicAuthentication(
    function ($userName) {
        return password_hash('bar', PASSWORD_DEFAULT);
    },
    array(
        'realm' => 'OAuth',
    )
);

$authenticationPlugin = new AuthenticationPlugin();
$authenticationPlugin->registerAuthenticationPlugin($basicAuthentication);

$pluginRegistry = new PluginRegistry();
$pluginRegistry->registerDefaultPlugin($authenticationPlugin);

$service->setPluginRegistry($pluginRegistry);

$o = new OAuthServer(
    new TwigTemplateManager(),
    new CryptoAuthorizationCode('7c34796dadace5c974b049774b3df89a', 'df3baee06309a11493d4c16bb70d09df'),
    new CryptoAccessToken('d6240461387e6aab9b0dafeda51d4504', '8aa5800fa8805ba6c777734e9d646544')
);

$service->get(
    '/authorize',
    function (Request $request, UserInfoInterface $userInfo) use ($o) {
        return $o->getAuthorize($request, $userInfo);
    }
);

$service->post(
    '/authorize',
    function (Request $request, UserInfoInterface $userInfo) use ($o) {
        return $o->postAuthorize($request, $userInfo);
    }
);

$service->post(
    '/token',
    function (Request $request) use ($o) {
        return $o->postToken($request);
    },
    array(
        'fkooman\Rest\Plugin\Authentication\AuthenticationPlugin' => array(
            'enabled' => false,
        ),
    )
);

$service->run()->send();
