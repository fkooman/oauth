<?php

require_once dirname(__DIR__).'/vendor/autoload.php';

use fkooman\Rest\Plugin\Authentication\IndieAuth\IndieAuthAuthentication;
use fkooman\OAuth\OAuthServer;
use fkooman\OAuth\Impl\TwigTemplateManager;
use fkooman\OAuth\Impl\CryptoStorage;
use fkooman\Crypto\Key;
use fkooman\Json\Json;
use fkooman\Ini\IniReader;
use fkooman\OAuth\Impl\NoRegistrationClient;
use fkooman\OAuth\Impl\JsonResourceServer;
use fkooman\OAuth\Impl\MyOAuthService;

// CONFIG
$iniReader = IniReader::fromFile(
    dirname(__DIR__).'/config/server.ini'
);

// USER AUTH
$userAuthentication = new IndieAuthAuthentication();
$userAuthentication->setUnauthorizedRedirectUri('/identify');

$key = Key::load($iniReader->v('Security', 'Key'));
$cryptoStorage = new CryptoStorage($key);

$o = new OAuthServer(
    new TwigTemplateManager(),
    new NoRegistrationClient(),
    new JsonResourceServer(dirname(__DIR__).'/config/resource_servers.json'),
    $cryptoStorage,
    $cryptoStorage
);

$service = new MyOAuthService($o, $userAuthentication);
$service->run()->send();
