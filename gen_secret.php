<?php

require_once 'vendor/autoload.php';

use fkooman\Base64\Base64Url;
use fkooman\Json\Json;

echo Base64Url::encode(Json::encode(
    array(
        'i' => $argv[1],
        's' => $argv[2],
    )
)).PHP_EOL;
