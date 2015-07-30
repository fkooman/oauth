<?php

require_once dirname(__DIR__).'/vendor/autoload.php';

use fkooman\OAuth\Storage\NullClientStorage;
use fkooman\OAuth\Storage\JsonResourceServerStorage;
use fkooman\OAuth\Storage\PdoCodeTokenStorage;
use fkooman\OAuth\OAuthServer;
use fkooman\Rest\Plugin\Authentication\IndieAuth\IndieAuthAuthentication;
use fkooman\Ini\IniReader;
use fkooman\Tpl\TwigTemplateManager;
use fkooman\IndieOAuth\MyOAuthService;

// CONFIG
$iniReader = IniReader::fromFile(
    dirname(__DIR__).'/config/server.ini'
);

// USER AUTH
$userAuthentication = new IndieAuthAuthentication();
$userAuthentication->setUnauthorizedRedirectUri('/identify');

$db = new PDO(
    $iniReader->v('Db', 'dsn'),
    $iniReader->v('Db', 'username', false),
    $iniReader->v('Db', 'password', false)
);
$pdoCodeTokenStorage = new PdoCodeTokenStorage($db);

$t = new TwigTemplateManager(
    array(
        dirname(__DIR__).'/views',
        dirname(__DIR__).'/config/views',
    )
);

$o = new OAuthServer(
    $t,
    new NullClientStorage(),
    new JsonResourceServerStorage(dirname(__DIR__).'/config/resource_servers.json'),
    $pdoCodeTokenStorage,
    $pdoCodeTokenStorage
);

$service = new MyOAuthService($o, $userAuthentication);
$service->run()->send();
