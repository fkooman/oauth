<?php

require_once dirname(__DIR__).'/vendor/autoload.php';

use fkooman\Ini\IniReader;
use fkooman\OAuth\Storage\PdoCodeTokenStorage;

try {
    $config = IniReader::fromFile(dirname(__DIR__).'/config/server.ini');

    // initialize the DB
    $pdo = new PDO(
        $config->v('Db', 'dsn'),
        $config->v('Db', 'username', false),
        $config->v('Db', 'password', false)
    );

    $storage = new PdoCodeTokenStorage($pdo);
    $storage->initDatabase();
} catch (Exception $e) {
    echo $e->getMessage().PHP_EOL;
    exit(1);
}
