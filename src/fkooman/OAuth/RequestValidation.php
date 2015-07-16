<?php

namespace fkooman\OAuth;

use fkooman\Http\Request;
use fkooman\Http\Exception\BadRequestException;

class RequestValidation
{
    public static function validateAuthorizeRequest(Request $request)
    {
        // client_id
        $clientId = $request->getUrl()->getQueryParameter('client_id');
        if (false === InputValidation::clientId($clientId)) {
            throw new BadRequestException('invalid client_id');
        }

        // redirect_uri
        $redirectUri = $request->getUrl()->getQueryParameter('redirect_uri');
        if (false === InputValidation::redirectUri($redirectUri)) {
            throw new BadRequestException('invalid redirect_uri');
        }

        // scope
        $scope = $request->getUrl()->getQueryParameter('scope');
        if (null !== $scope) {
            if (false === InputValidation::scope($scope)) {
                throw new BadRequestException('invalid scope');
            }
        }

        // state
        $state = $request->getUrl()->getQueryParameter('state');
        if (false === InputValidation::state($state)) {
            throw new BadRequestException('invalid state');
        }

        return array(
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'scope' => $scope,
            'state' => $state,
        );
    }

    public static function validatePostAuthorizeRequest(Request $request)
    {
        $requestData = self::validateAuthorizeRequest($request);

        // approval
        // FIXME: must only be yes or no
        $requestData['approval'] = $request->getPostParameter('approval');

        return $requestData;
    }

    public static function validateTokenRequest(Request $request)
    {
        // code
        $code = $request->getPostParameter('code');
        if (false === InputValidation::code($code)) {
            throw new BadRequestException('invalid code');
        }

        // ...
        // ...

#        // scope
#        $scope = $request->getPostParameter('scope');
#        if (false === InputValidation::scope($scope)) {
#            throw new BadRequestException('invalid scope');
#        }

        // redirect_uri
        $redirectUri = $request->getPostParameter('redirect_uri');
        if (false === InputValidation::redirectUri($redirectUri)) {
            throw new BadRequestException('invalid redirect_uri');
        }

        return array(
            'code' => $code,
            'redirect_uri' => $redirectUri,
        );
    }

    public static function validateIntrospectRequest(Request $request)
    {
        // token
        $token = $request->getPostParameter('token');
        if (false === InputValidation::token($token)) {
            throw new BadRequestException('invalid token');
        }

        return array(
            'token' => $token,
        );
    }
}
