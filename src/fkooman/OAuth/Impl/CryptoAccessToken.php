<?php

namespace fkooman\OAuth\Impl;

use fkooman\Crypto\Symmetric;
use fkooman\OAuth\AccessTokenInterface;
use fkooman\Json\Json;

class CryptoAccessToken implements AccessTokenInterface
{
    /** @var string */
    private $encryptKey;

    /** @var string */
    private $signKey;

    public function __construct($encryptKey, $signKey)
    {
        $this->encryptKey = $encryptKey;
        $this->signKey = $signKey;
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

        $crypto = new Symmetric($this->encryptKey, $this->signKey);

        return $crypto->encrypt(Json::encode($payload));
    }

    public function validate($token)
    {
        // FIXME: catch situation where signature not matches and return false instead
        $crypto = new Symmetric($this->encryptKey, $this->signKey);

        return Json::decode($crypto->decrypt($token), true);
    }
}
