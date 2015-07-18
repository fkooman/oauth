<?php

namespace fkooman\OAuth\Impl;

use fkooman\OAuth\ResourceServerInterface;
use fkooman\OAuth\ResourceServerInfo;
use fkooman\Json\Json;

class JsonResourceServer implements ResourceServerInterface
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

        return ResourceServerInfo::fromArray($data[$resourceServerId]);
    }
}
