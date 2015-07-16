<?php

require_once dirname(__DIR__).'/vendor/autoload.php';

use fkooman\Crypto\Key;

try {
    echo Key::generate().PHP_EOL;
} catch (Exception $e) {
    echo $e->getMessage().PHP_EOL;
    exit(1);
}
