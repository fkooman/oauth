<?php

require_once dirname(__DIR__).'/vendor/autoload.php';

use fkooman\Rest\Plugin\Authentication\AuthenticationPlugin;
use fkooman\Rest\Plugin\Authentication\IndieAuth\IndieAuthAuthentication;
use fkooman\Rest\Plugin\Authentication\Basic\BasicAuthentication;
use fkooman\OAuth\OAuthServer;
use fkooman\OAuth\Impl\TwigTemplateManager;
use fkooman\OAuth\Impl\CryptoAuthorizationCode;
use fkooman\OAuth\Impl\CryptoAccessToken;
use fkooman\Crypto\Key;
use fkooman\Json\Json;
use fkooman\Ini\IniReader;
use fkooman\OAuth\Impl\NoRegistrationClient;
use fkooman\OAuth\Impl\JsonResourceServer;
use fkooman\OAuth\OAuthService;

// CONFIG
$iniReader = IniReader::fromFile(
    dirname(__DIR__).'/config/server.ini'
);

// USER AUTH
#$userAuthentication = new IndieAuthAuthentication();
#$userAuthentication->setUnauthorizedRedirectUri('/_indieauth/identify');

$userAuthentication = new BasicAuthentication(
    function ($userId) {
        $c = Json::decodeFile(dirname(__DIR__).'/config/users.json');
        return $c[$userId]['secret'];
    },
    array(
        'realm' => 'OAuth',
    )
);

// RESOURCE SERVER AUTH
$resourceServerAuthentication = new BasicAuthentication(
    function ($resourceServerId) {
        $jsonResourceServer = new JsonResourceServer(dirname(__DIR__).'/config/resource_servers.json');
        $resourceServer = $jsonResourceServer->getResourceServer($resourceServerId);
        if (false === $resourceServer) {
            return false;
        }

        return $resourceServer->getSecret();
    },
    array(
        'realm' => 'OAuth',
    )
);

$key = Key::load($iniReader->v('Security', 'Key'));
$o = new OAuthServer(
    new TwigTemplateManager(),
    new NoRegistrationClient(),
    new JsonResourceServer(dirname(__DIR__).'/config/resource_servers.json'),
    new CryptoAuthorizationCode($key),
    new CryptoAccessToken($key)
);

$service = new OAuthService($o);

// AUTHENTICATION PLUGIN
$authenticationPlugin = new AuthenticationPlugin();
$authenticationPlugin->register($userAuthentication, 'user');
$authenticationPlugin->register($resourceServerAuthentication, 'resource_server');
$service->getPluginRegistry()->registerDefaultPlugin($authenticationPlugin);
$service->run()->send();
