<?php

namespace fkooman\OAuth;

use fkooman\Rest\Plugin\Bearer\ValidatorInterface;
use fkooman\Rest\Plugin\Bearer\TokenInfo;
use fkooman\Base64\Base64Url;
use fkooman\Json\Json;

class ResourceServerValidator implements ValidatorInterface
{
    /** @var string */
    private $resourceServerData;

    public function __construct($resourceServerFile)
    {
        $this->resourceServerData = Json::decodeFile($resourceServerFile);
    }

    public function validate($bearerToken)
    {
        $decodedBearerToken = Json::decode(Base64Url::decode($bearerToken));

        if (!array_key_exists('i', $decodedBearerToken)) {
            //
        }
        $id = $decodedBearerToken['i'];

        if (!array_key_exists('s', $decodedBearerToken)) {
            //
        }
        $secret = $decodedBearerToken['s'];

        if (!array_key_exists($id, $this->resourceServerData)) {
            //
        }

        // FIXME: safe string comparison!
        if ($this->resourceServerData[$id]['secret'] !== $secret) {
            // invalid secret
            return new TokenInfo(
                array('active' => false)
            );
        }

        // valid secret
        return new TokenInfo(
            array('active' => true)
        );
    }
}
