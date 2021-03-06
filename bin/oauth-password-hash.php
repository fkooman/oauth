<?php

require_once dirname(__DIR__).'/vendor/autoload.php';

try {
    if (2 > $argc) {
        throw new Exception(
            sprintf('SYNTAX: %s [secret]', $argv[0])
        );
    }
    echo password_hash($argv[1], PASSWORD_DEFAULT).PHP_EOL;
} catch (Exception $e) {
    echo $e->getMessage().PHP_EOL;
    exit(1);
}
