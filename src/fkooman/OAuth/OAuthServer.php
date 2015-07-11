<?php

namespace fkooman\OAuth;

use fkooman\Rest\Plugin\Authentication\UserInfoInterface;
use fkooman\Http\Request;
use fkooman\Http\RedirectResponse;
use fkooman\Http\JsonResponse;

class OAuthServer
{
    /** @var TemplateInterface */
    private $templateManager;

    /** @var AuthorizationCodeInterface */
    private $authorizationCode;

    /** @var AccessTokenInterface */
    private $accessToken;

    public function __construct(TemplateInterface $templateManager, AuthorizationCodeInterface $authorizationCode, AccessTokenInterface $accessToken)
    {
        $this->templateManager = $templateManager;
        $this->authorizationCode = $authorizationCode;
        $this->accessToken = $accessToken;
    }

    public function getAuthorize(Request $request, UserInfoInterface $userInfo)
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

    public function postAuthorize(Request $request, UserInfoInterface $userInfo)
    {
        // FIXME: referrer url MUST be request URL?
        $p = RequestValidation::validateAuthorizeRequest($request);

        $approval = $request->getPostParameter('approval');
        if ('yes' === $approval) {
            $code = $this->authorizationCode->create(
                $userInfo->getUserId(),
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
        // FIXME: check for expired code! >= 10 minutes old
        // FIXME: keep log of used codes (must not allowed to be replayed)

        // create an access token
        $accessToken = $this->accessToken->create(
            $authorizationCode['user_id'],
            time(),
            $authorizationCode['redirect_uri'],
            $authorizationCode['scope']
        );

        $response = new JsonResponse();
        // FIXME: caching headers
        $response->setBody(
            array(
                'access_token' => $accessToken,
                'scope' => $authorizationCode['scope'],
            )
        );

        return $response;
    }

    public function postIntrospect(Request $request)
    {
        // FIXME: must be Bearer authenticated
        $p = RequestValidation::validateIntrospectRequest($request);
        $accessToken = $this->accessToken->validate($p['token']);

        $response = new JsonResponse();
        // FIXME: caching headers
        $response->setBody(
            $accessToken
        );

        return $response;
    }
}
