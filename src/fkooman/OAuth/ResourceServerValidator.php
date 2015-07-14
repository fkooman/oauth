<?php

namespace fkooman\OAuth;

use fkooman\Rest\Plugin\Bearer\ValidatorInterface;
use fkooman\Rest\Plugin\Bearer\TokenInfo;
use fkooman\Base64\Base64Url;
use fkooman\Json\Json;

class ResourceServerValidator implements ValidatorInterface
{
    public function __construct()
    {
        // FIXME: implement file
    }

    public function validate($bearerToken)
    {
        // find the resource server ID
#        $decodedToken = Base64Url::decode($bearerToken);
#        $jsonToken = Json::decode($decodedToken);
#        $resourceServerId = $jsonToken;
#        $resourceServerToken = $jsonToken[1];

        // FIXME: unsafe comparison!
#        if('fooid' === $resourceServerId && 'ldfj3y23o4h23o4i' === $resourceServerToken) {
            return new TokenInfo(
                array('active' => true)
            );
#        }
#        return new TokenInfo(
#            array('active' => false)
#        );
    }
}
