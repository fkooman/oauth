<?php

namespace fkooman\OAuth\Impl;

use fkooman\Crypto\Symmetric;
use fkooman\Crypto\Key;
use fkooman\OAuth\AccessTokenInterface;
use fkooman\Json\Json;
use fkooman\OAuth\AccessToken;
use Exception;

class CryptoAccessToken implements AccessTokenInterface
{
    /** @var \fkooman\Crypto\Symmetric */
    private $symmetric;

    public function __construct(Key $key)
    {
        $this->symmetric = new Symmetric($key);
    }

    public function store(AccessToken $accessToken)
    {
        // generate code
        $payload = array(
            'iat' => $accessToken->getIssuedAt(),
            // FIXME: add nonce

# https://tools.ietf.org/html/rfc7519#section-4.1.7
            'user_id' => $accessToken->getUserId(),
            'jti' => 'some_nonce_that_must_be_recorded_against_replay',
            'redirect_uri' => $accessToken->getRedirectUri(),
            'scope' => $accessToken->getScope(),
        );

        return $this->symmetric->encrypt(Json::encode($payload));
    }

    public function retrieve($accessToken)
    {
        // FIXME: catch situation where signature not matches and return false instead
        try { 
            return AccessToken::fromArray(
                Json::decode($this->symmetric->decrypt($accessToken), true)
            );
        } catch (Exception $e) {
            // if anything goes wrong, just return false
            return false;
        }
    }
}
