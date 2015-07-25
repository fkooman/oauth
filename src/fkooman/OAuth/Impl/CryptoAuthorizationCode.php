<?php

namespace fkooman\OAuth\Impl;

use fkooman\Crypto\Symmetric;
use fkooman\Crypto\Key;
use fkooman\OAuth\AuthorizationCodeInterface;
use fkooman\OAuth\AccessTokenInterface;
use fkooman\Json\Json;
use fkooman\OAuth\AuthorizationCode;
use fkooman\OAuth\AccessToken;
use fkooman\IO\IO;
use Exception;

class CryptoAuthorizationCode implements AuthorizationCodeInterface, AccessTokenInterface
{
    /** @var \fkooman\Crypto\Symmetric */
    private $symmetric;

    /** @var \fkooman\IO\IO */
    private $io;

    public function __construct(Key $key, IO $io = null)
    {
        $this->symmetric = new Symmetric($key);
        if (null === $io) {
            $io = new IO();
        }
        $this->io = $io;
    }

    public function storeAuthorizationCode(AuthorizationCode $authorizationCode)
    {
        // generate code
        $payload = array(
            'client_id' => $authorizationCode->getClientId(),
            'iat' => $authorizationCode->getIssuedAt(),
            'user_id' => $authorizationCode->getUserId(),
            'jti' => $this->io->getRandom(), // https://tools.ietf.org/html/rfc7519#section-4.1.7
            'redirect_uri' => $authorizationCode->getRedirectUri(),
            'scope' => $authorizationCode->getScope(),
        );

        return $this->symmetric->encrypt(Json::encode($payload));
    }

    public function retrieveAuthorizationCode($authorizationCode)
    {
        try {
            return AuthorizationCode::fromArray(
                Json::decode($this->symmetric->decrypt($authorizationCode), true)
            );
        } catch (Exception $e) {
            // if anything goes wrong, just return false
            return false;
        }
    }

    public function isFreshAuthorizationCode($authorizationCode)
    {
        // FIXME: implement log of used authorization codes, keep track of 
        // nonces I guess...
        return true;
    }

    public function storeAccessToken(AccessToken $accessToken)
    {
        // generate code
        $payload = array(
            'client_id' => $accessToken->getClientId(),
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

    public function retrieveAccessToken($accessToken)
    {
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
