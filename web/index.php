<?php

require_once dirname(__DIR__).'/vendor/autoload.php';

use fkooman\OAuth\Storage\NullClientStorage;
use fkooman\OAuth\Storage\JsonResourceServerStorage;
use fkooman\OAuth\OAuthServer;
use fkooman\Rest\Plugin\Authentication\IndieAuth\IndieAuthAuthentication;
use fkooman\Ini\IniReader;
use fkooman\OAuth\Impl\TwigTemplateManager;
use fkooman\OAuth\Impl\CryptoStorage;
use fkooman\Crypto\Key;
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
    new NullClientStorage(),
    new JsonResourceServerStorage(dirname(__DIR__).'/config/resource_servers.json'),
    $cryptoStorage,
    $cryptoStorage
);

$service = new MyOAuthService($o, $userAuthentication);
$service->run()->send();
