<?php

namespace fkooman\OAuth;

use fkooman\Http\Request;
use fkooman\Http\RedirectResponse;
use fkooman\Http\Exception\BadRequestException;

class OAuthServer
{
    /** @var TemplateManager */
    private $templateManager;

    /** @var AuthorizationCodeInterface */
    private $authorizationCode;

    public function __construct(TemplateManager $templateManager, AuthorizationCodeInterface $authorizationCode)
    {
        $this->templateManager = $templateManager;
        $this->authorizationCode = $authorizationCode;
    }

    public function getAuthorize(Request $request)
    {
        $this->validateAuthorizeParameters($request);

        // show the approval dialog
        return $this->templateManager->render(
            'getAuthorize',
            array(
                'redirect_uri' => $redirectUri,
                'scope' => $scope,
                'request_url' => $request->getUrl()->toString(),
            )
        );
    }

    public function postAuthorize(Request $request)
    {
        $this->validateAuthorizeParameters($request);

        $approval = $request->getPostParameter('approval');
        if ('yes' === $approval) {
            $code = $this->authorizationCode->generate(
                time(),
                $redirectUri,
                $scope
            );

            return new RedirectResponse(
                // FIXME: append, not just simply add
                $redirectUri.'?code='.$code,
                302
            );
        }

        // not approved
        return new RedirectResponse(
            $redirectUri.'?error=XXX',
            302
        );
    }

    public function postToken(Request $request)
    {
        return '';
    }

    private static function validateAuthorizeParameters(Request $request)
    {
        // redirect_uri
        $redirectUri = $request->getUrl()->getQueryParameter('redirect_uri');
        if (false === InputValidation::redirectUri($redirectUri)) {
            throw new BadRequestException('invalid redirect_uri');
        }

        // scope
        $scope = $request->getUrl()->getQueryParameter('scope');
        if (false === InputValidation::scope($scope)) {
            throw new BadRequestException('invalid scope');
        }
    }
}
