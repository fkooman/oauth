<?php

namespace fkooman\OAuth;

use fkooman\Http\Request;
use fkooman\Http\RedirectResponse;

class OAuthServer
{
    /** @var TemplateInterface */
    private $templateManager;

    /** @var AuthorizationCodeInterface */
    private $authorizationCode;

    public function __construct(TemplateInterface $templateManager, AuthorizationCodeInterface $authorizationCode)
    {
        $this->templateManager = $templateManager;
        $this->authorizationCode = $authorizationCode;
    }

    public function getAuthorize(Request $request)
    {
        RequestValidation::validateAuthorizeRequest($request);
        $redirectUri = $request->getUrl()->getQueryParameter('redirect_uri');
        $scope = $request->getUrl()->getQueryParameter('scope');

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
        // FIXME: referrer url MUST be request URL?
        $p = RequestValidation::validateAuthorizeRequest($request);

        $approval = $request->getPostParameter('approval');
        if ('yes' === $approval) {
            $code = $this->authorizationCode->generate(
                time(),
                $p['redirect_uri'],
                $p['scope']
            );

            return new RedirectResponse(
                // FIXME: append, not just simply add
                $p['redirect_uri'].'?code='.$code.'&state='.$p['state'],
                302
            );
        }

        // not approved
        return new RedirectResponse(
            $p['redirect_uri'].'?error=XXX&state='.$p['state'],
            302
        );
    }

    public function postToken(Request $request)
    {
        $p = RequestValidation::validateTokenRequest($request);
        $authorizationCode = $this->authorizationCode->validate($p['code']);

        // FIXME: values in code should match values from this request!

        // generate an access token
        $accessToken = $this->accessToken->generate(
            time(),
            $p['redirect_uri'],
            $p['scope']
        );

        $response = new JsonResponse();
        // FIXME: caching headers
        $response->setBody(
            array(
                'access_token' => $accessToken,
                'scope' => $p['scope'],
            )
        );

        return $response;
    }
}
