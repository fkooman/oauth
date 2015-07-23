<?php

namespace fkooman\OAuth\Impl;

class MyOAuthService extends OAuthService
{
    public function __construct(OAuthServer $oauthServer)
    {
        parent::__construct($oauthServer);
        $this->registerMyRoutes();
    }

    public function registerMyRoutes()
    {
        $this->get(
            '/identify',
            function (Request $request) {
                return 'please authenticate';
            }
        );
    }
}
