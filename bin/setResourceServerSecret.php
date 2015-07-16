<?php

require_once dirname(__DIR__).'/vendor/autoload.php';

use fkooman\OAuth\JsonCredentials;

try {
    if (3 > $argc) {
        throw new Exception(
            sprintf('SYNTAX: %s [resourceServerId] [resourceServerSecret]', $argv[0])
        );
    }

    $c = new JsonCredentials(dirname(__DIR__).'/config/resource_servers.json');
    $c->setSecret($argv[1], $argv[2]);
} catch (Exception $e) {
    echo $e->getMessage().PHP_EOL;
    exit(1);
}
