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

    public function setSecret($id, $secret)
    {
        $data = array();
        try {
            $data = Json::decodeFile($this->jsonFile);
        } catch (RuntimeException $e) {
            // unable to read file, continue with empty array
        }
        $data[$id]['secret'] = password_hash($secret, PASSWORD_DEFAULT);
        if (false === @file_put_contents($this->jsonFile, Json::encode($data, JSON_PRETTY_PRINT))) {
            throw new RuntimeException('unable to write to credential file');
        }
    }
}
