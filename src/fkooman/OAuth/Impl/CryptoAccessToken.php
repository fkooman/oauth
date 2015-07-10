<?php

namespace fkooman\OAuth\Impl;

use fkooman\Crypto\Crypto;
use fkooman\OAuth\AccessTokenInterface;

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

    public function create($issuedAt, $redirectUri, $scope)
    {
        // generate code
        $payload = array(
            'iat' => $issuedAt,
            // FIXME: add nonce

# https://tools.ietf.org/html/rfc7519#section-4.1.7

            'jti' => 'some_nonce_that_must_be_recorded_against_replay',
            'redirect_uri' => $redirectUri,
            'scope' => $scope,
        );

        $crypto = new Crypto($this->encryptKey, $this->signKey);

        return $crypto->encrypt(json_encode($payload));
    }

    public function validate($code)
    {
        // FIXME: catch situation where signature not matches and return false instead
        $crypto = new Crypto($this->encryptKey, $this->signKey);

        return json_decode($crypto->decrypt($code), true);
    }
}
