<?php

namespace fkooman\OAuth;

interface AccessTokenInterface
{
    /**
     * Create an access_token.
     *
     * @param string $userId      the user identifiero
     * @param int    $issuedAt    the issue time (epoch)
     * @param string $redirectUri the redirect_uri of the client
     * @param string $scope       the scope requested by the client
     */
    public function create($userId, $issuedAt, $redirectUri, $scope);

    /**
     * Validate an access_token.
     *
     * @param string $accessToken the access_token to validate
     *
     * @return mixed the fields that were bound to the access_token as
     *               array, or false if the access_token is invalid
     */
    public function validate($accessToken);
}
