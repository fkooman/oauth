<?php

namespace fkooman\OAuth;

interface AuthorizationCodeInterface
{
    /**
     * Create an authorization_code.
     *
     * @param string $userId      the user identifier
     * @param int    $issuedAt    the issue time (epoch)
     * @param string $redirectUri the redirect_uri of the client
     * @param string $scope       the scope requested by the client
     */
    public function create($userId, $issuedAt, $redirectUri, $scope);

    /**
     * Validate an authorization_code.
     *
     * @param string $code the authorization_code to validate
     *
     * @return mixed the fields that were bound to the authorization_code as
     *               array, or false if the code is invalid
     */
    public function validate($code);
}
