<?php

namespace fkooman\OAuth\Impl;

use fkooman\OAuth\ResourceServerStorageInterface;
use fkooman\OAuth\ResourceServer;
use fkooman\Json\Json;

class JsonResourceServer implements ResourceServerStorageInterface
{
    /** @var string */
    private $jsonFile;

    public function __construct($jsonFile)
    {
        $this->jsonFile = $jsonFile;
    }

    public function getResourceServer($resourceServerId)
    {
        $data = Json::decodeFile($this->jsonFile);
        if (!array_key_exists($resourceServerId, $data)) {
            return false;
        }

        // FIXME: this is not really nice... any other way?
        $data[$resourceServerId]['resource_server_id'] = $resourceServerId;

        return ResourceServer::fromArray($data[$resourceServerId]);
    }
}
