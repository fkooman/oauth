<?php

namespace fkooman\OAuth;

use Namshi\JOSE\SimpleJWS;

class JwsAuthorizationCode implements AuthorizationCodeInterface
{
    /** @var string */
    private $signKey;

    public function __construct($signKey)
    {
        $this->signKey = $signKey;
    }

    public function generate($issuedAt, $redirectUri, $scope)
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

        $jws = new SimpleJWS(
            array(
                'alg' => 'HS256',
            )
        );

        $jws->setPayload($payload);
        $jws->sign($this->signKey);

        return $jws->getTokenString();
    }

    public function validate($code)
    {
        // FIXME: protection against replaying must be implemented somewhere,
        // maybe here??

        $jws = SimpleJWS::load($code);
        if ($jws->isValid($this->signKey, 'HS256')) {
            return $jws->getPayload();
        }

        return false;
    }
}
