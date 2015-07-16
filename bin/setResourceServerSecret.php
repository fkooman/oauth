<?php

require_once dirname(__DIR__).'/vendor/autoload.php';

use fkooman\Json\Json;

$fileName = dirname(__DIR__).'/config/resource_servers.json';

try {
    if (3 > $argc) {
        throw new Exception(
            sprintf('SYNTAX: %s [resourceServerId] [resourceServerSecret]', $argv[0])
        );
    }

    $data = array();
    try {
        $data = Json::decodeFile($fileName);
    } catch (Exception $e) {
        // do nothing
    }

    $data[$argv[1]]['secret'] = password_hash($argv[2], PASSWORD_DEFAULT);
    if (false === @file_put_contents($fileName, Json::encode($data, JSON_PRETTY_PRINT))) {
        throw new RuntimeException('unable to write to credential file');
    }
} catch (Exception $e) {
    echo $e->getMessage().PHP_EOL;
    exit(1);
}
