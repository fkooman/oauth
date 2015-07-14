<?php

namespace fkooman\OAuth\Impl;

use fkooman\Crypto\Symmetric;
use fkooman\Crypto\Key;
use fkooman\OAuth\AuthorizationCodeInterface;
use fkooman\Json\Json;

class CryptoAuthorizationCode implements AuthorizationCodeInterface
{
    /** @var \fkooman\Crypto\Symmetric */
    private $symmetric;

    public function __construct(Key $key)
    {
        $this->symmetric = new Symmetric($key);
    }

    public function create($userId, $issuedAt, $redirectUri, $scope)
    {
        // generate code
        $payload = array(
            'iat' => $issuedAt,
            // FIXME: add nonce
            'user_id' => $userId,
# https://tools.ietf.org/html/rfc7519#section-4.1.7

            'jti' => 'some_nonce_that_must_be_recorded_against_replay',
            'redirect_uri' => $redirectUri,
            'scope' => $scope,
        );

        return $this->symmetric->encrypt(Json::encode($payload));
    }

    public function validate($code)
    {
        // FIXME: protection against replaying must be implemented somewhere,
        // maybe here??
        return Json::decode($this->symmetric->decrypt($code), true);
    }
}
