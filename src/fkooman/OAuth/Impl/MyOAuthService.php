<?php

namespace fkooman\OAuth\Impl;

use fkooman\OAuth\OAuthService;
use fkooman\OAuth\OAuthServer;
use fkooman\Http\Request;

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
                $templateManager = $this->server->getTemplateManager();

                return $templateManager->render(
                    'getIdentify',
                    array(
                        'redirectTo' => urldecode($request->getUrl()->getQueryParameter('redirect_to')),
                    )
                );
            },
            array(
                'fkooman\Rest\Plugin\Authentication\AuthenticationPlugin' => array('enabled' => false),
            )
        );
    }
}
