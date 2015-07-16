<?php

namespace fkooman\OAuth\Impl;

use fkooman\Crypto\Symmetric;
use fkooman\Crypto\Key;
use fkooman\OAuth\AuthorizationCodeInterface;
use fkooman\Json\Json;
use fkooman\OAuth\AuthorizationCode;
use Exception;

class CryptoAuthorizationCode implements AuthorizationCodeInterface
{
    /** @var \fkooman\Crypto\Symmetric */
    private $symmetric;

    public function __construct(Key $key)
    {
        $this->symmetric = new Symmetric($key);
    }

    public function store(AuthorizationCode $authorizationCode)
    {
        // generate code
        $payload = array(
            'iat' => $authorizationCode->getIssuedAt(),
            // FIXME: add nonce
            'user_id' => $authorizationCode->getUserId(),
# https://tools.ietf.org/html/rfc7519#section-4.1.7

            'jti' => 'some_nonce_that_must_be_recorded_against_replay',
            'redirect_uri' => $authorizationCode->getRedirectUri(),
            'scope' => $authorizationCode->getScope(),
        );

        return $this->symmetric->encrypt(Json::encode($payload));
    }

    public function retrieve($authorizationCode)
    {
        // FIXME: protection against replaying must be implemented somewhere,
        // maybe here??
        try { 
            return AuthorizationCode::fromArray(
                Json::decode($this->symmetric->decrypt($authorizationCode), true)
            );
        } catch (Exception $e) {
            // if anything goes wrong, just return false
            return false;
        }
    }
}
