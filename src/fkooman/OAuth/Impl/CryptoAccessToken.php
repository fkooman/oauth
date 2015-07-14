<?php

namespace fkooman\OAuth\Impl;

use fkooman\Crypto\Symmetric;
use fkooman\Crypto\Key;
use fkooman\OAuth\AccessTokenInterface;
use fkooman\Json\Json;

class CryptoAccessToken implements AccessTokenInterface
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

# https://tools.ietf.org/html/rfc7519#section-4.1.7
            'user_id' => $userId,
            'jti' => 'some_nonce_that_must_be_recorded_against_replay',
            'redirect_uri' => $redirectUri,
            'scope' => $scope,
        );

        return $this->symmetric->encrypt(Json::encode($payload));
    }

    public function validate($token)
    {
        // FIXME: catch situation where signature not matches and return false instead

        return Json::decode($this->symmetric->decrypt($token), true);
    }
}
