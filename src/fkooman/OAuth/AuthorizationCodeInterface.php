<?php

namespace fkooman\OAuth;

interface AuthorizationCodeInterface
{
    /**
     * Generate an authorization_code.
     *
     * @param int    $issuedAt    the issue time (epoch)
     * @param string $redirectUri the redirect_uri of the client
     * @param string $scope       the scope requested by the client
     */
    public function generate($issuedAt, $redirectUri, $scope);

    /**
     * Validate the authorization_code.
     *
     * @param string $code the authorization_code to validate
     *
     * @return array the fields that were bound to the authorization_code
     */
    public function validate($code);
}
