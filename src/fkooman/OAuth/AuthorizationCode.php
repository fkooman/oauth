<?php

namespace fkooman\OAuth;

class AuthorizationCode
{
    /** @var string */
    private $userId;

    /** @var int */
    private $issuedAt;

    /** @var string */
    private $redirectUri;

    /** @var string */
    private $scope;

    public function __construct($userId, $issuedAt, $redirectUri, $scope)
    {
        $this->userId = $userId;
        $this->issuedAt = $issuedAt;
        $this->redirectUri = $redirectUri;
        $this->scope = $scope;
    }

    public static function fromArray(array $authorizationCode)
    {
        return new self(
            $authorizationCode['user_id'],
            $authorizationCode['iat'],
            $authorizationCode['redirect_uri'],
            $authorizationCode['scope']
        );
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function getIssuedAt()
    {
        return $this->issuedAt;
    }

    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

    public function getScope()
    {
        return $this->scope;
    }
}
