<?php

require_once 'vendor/autoload.php';

use fkooman\OAuth\JsonCredentials;

try {
    if (3 > $argc) {
        throw new Exception(
            sprintf('SYNTAX: %s [userId] [userSecret]', $argv[0])
        );
    }

    $c = new JsonCredentials(dirname(__DIR__).'/config/users.json');
    $c->setSecret($argv[1], $argv[2]);
} catch (Exception $e) {
    echo $e->getMessage().PHP_EOL;
    exit(1);
}
