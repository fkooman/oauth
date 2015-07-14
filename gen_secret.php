<?php

require_once 'vendor/autoload.php';

use fkooman\Base64\Base64Url;
use fkooman\Json\Json;

echo Base64Url::encode(Json::encode(
    array(
        'i' => 'foo',
        's' => 'secret',
    )
)).PHP_EOL;
