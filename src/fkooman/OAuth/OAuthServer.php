<?php

namespace fkooman\OAuth;

use fkooman\Rest\Plugin\Authentication\UserInfoInterface;
use fkooman\Http\Request;
use fkooman\Http\RedirectResponse;
use fkooman\Http\JsonResponse;
use fkooman\Http\Exception\BadRequestException;

class OAuthServer
{
    /** @var TemplateInterface */
    private $templateManager;

    /** @var AuthorizationCodeInterface */
    private $authorizationCode;

    /** @var AccessTokenInterface */
    private $accessToken;

    /** @var IO */
    private $io;

    public function __construct(TemplateInterface $templateManager, AuthorizationCodeInterface $authorizationCode, AccessTokenInterface $accessToken, IO $io = null)
    {
        $this->templateManager = $templateManager;
        $this->authorizationCode = $authorizationCode;
        $this->accessToken = $accessToken;
        if (null === $io) {
            $io = new IO();
        }
        $this->io = $io;
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
        $authorizeRequest = RequestValidation::validateAuthorizeRequest($request);

        $approval = $request->getPostParameter('approval');
        if ('yes' === $approval) {
            $code = $this->authorizationCode->store(
                new AuthorizationCode(
                    $userInfo->getUserId(),
                    $this->io->getTime(),
                    $authorizeRequest['redirect_uri'],
                    $authorizeRequest['scope']
                )
            );

            return new RedirectResponse(
                // FIXME: append, not just simply add
                $authorizeRequest['redirect_uri'].'?code='.$code.'&state='.$authorizeRequest['state'],
                302
            );
        }

        // not approved
        return new RedirectResponse(
            $authorizeRequest['redirect_uri'].'?error=XXX&state='.$authorizeRequest['state'],
            302
        );
    }

    public function postToken(Request $request)
    {
        $tokenRequest = RequestValidation::validateTokenRequest($request);
        $authorizationCode = $this->authorizationCode->retrieve($tokenRequest['code']);

        $iat = $authorizationCode->getIssuedAt();
        if ($this->io->getTime() > $iat + 600) {
            throw new BadRequestException('authorization code expired');
        }
        // FIXME: values in code should match values from this request!
        // FIXME: the scope could be less than authorized!
        // FIXME: keep log of used codes (must not allowed to be replayed)

        // create an access token
        $accessToken = $this->accessToken->store(
            new AccessToken(
                $authorizationCode->getUserId(),
                $this->io->getTime(),
                $authorizationCode->getRedirectUri(),
                $authorizationCode->getScope()
            )
        );

        $response = new JsonResponse();
        $response->setHeader('Cache-Control', 'no-store');
        $response->setHeader('Pragma', 'no-cache');
        $response->setBody(
            array(
                'access_token' => $accessToken,
                'scope' => $authorizationCode->getScope(),
            )
        );

        return $response;
    }

    public function postIntrospect(Request $request, UserInfoInterface $userInfo)
    {
        $introspectRequest = RequestValidation::validateIntrospectRequest($request);
        $accessToken = $this->accessToken->retrieve($introspectRequest['token']);

        if (false === $accessToken) {
            $body = array(
                'active' => false,
            );
        } else {
            $body = array(
                'active' => true,
                'scope' => $accessToken->getScope(),
                'token_type' => 'bearer',
                'iat' => $accessToken->getIssuedAt(),
                'sub' => $accessToken->getUserId(),
            );
        }

        $response = new JsonResponse();
        $response->setBody($body);

        return $response;
    }
}
