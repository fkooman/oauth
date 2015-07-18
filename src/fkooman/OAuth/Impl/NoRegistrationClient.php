<?php

namespace fkooman\OAuth\Impl;

use fkooman\OAuth\ClientInterface;
use fkooman\OAuth\ClientInfo;

class NoRegistrationClient implements ClientInterface
{
    public function getClient($clientId, $responseType, $redirectUri, $scope)
    {
        // only when there is actual client registration the redirectUri and
        // scope are optional as they can be retrieve from the registration
        // data, because there is no registration we require them to be set
        // explicitly
        if (null === $redirectUri || null === $scope) {
            return false;
        }

        return new ClientInfo($clientId, $responseType, $redirectUri, $scope);
    }
}
