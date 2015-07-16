<?php

namespace fkooman\OAuth;

use fkooman\Json\Json;
use RuntimeException;

class JsonCredentials
{
    /** @var string */
    private $jsonFile;

    public function __construct($jsonFile)
    {
        $this->jsonFile = $jsonFile;
    }

    public function getSecret($id)
    {
        $data = Json::decodeFile($this->jsonFile);
        if (array_key_exists($id, $data)) {
            if (array_key_exists('secret', $data[$id])) {
                return $data[$id]['secret'];
            }
        }

        return false;
    }
}
