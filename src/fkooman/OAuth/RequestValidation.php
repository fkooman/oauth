<?php

namespace fkooman\OAuth;

use fkooman\Http\Request;
use fkooman\Http\Exception\BadRequestException;

class RequestValidation
{
    public static function validateAuthorizeRequest(Request $request)
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

        // state
        $state = $request->getUrl()->getQueryParameter('state');
        if (false === InputValidation::state($state)) {
            throw new BadRequestException('invalid state');
        }

        return array(
            'redirect_uri' => $redirectUri,
            'scope' => $scope,
            'state' => $state,
        );
    }

    public static function validateTokenRequest(Request $request)
    {
        // code

        // ...
        // ...

        // scope
        $scope = $request->getPostParameter('scope');
        if (false === InputValidation::scope($scope)) {
            throw new BadRequestException('invalid scope');
        }

        // redirect_uri
        $redirectUri = $request->getPostParameter('redirect_uri');
        if (false === InputValidation::redirectUri($redirectUri)) {
            throw new BadRequestException('invalid redirect_uri');
        }

        return array(
            'scope' => $scope,
            'redirect_uri' => $redirectUri,
        );
    }
}
