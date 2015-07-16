<?php

/**
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace fkooman\OAuth;

require_once __DIR__.'/TestTemplateManager.php';
require_once __DIR__.'/TestAuthorizationCode.php';
require_once __DIR__.'/TestAccessToken.php';

use PHPUnit_Framework_TestCase;
use fkooman\OAuth\Impl\TestTemplateManager;
use fkooman\OAuth\Impl\TestAuthorizationCode;
use fkooman\OAuth\Impl\TestAccessToken;
use fkooman\OAuth\Impl\NoRegistrationClient;
use fkooman\Http\Request;

class OAuthServerTest extends PHPUnit_Framework_TestCase
{
    /** @var \fkooman\OAuth\OAuthServer */
    private $oauthServer;

    /** @var \fkooman\Rest\Plugin\Authentication\UserInfoInterface */
    private $userInfo;

    public function setUp()
    {
        $testTemplateManager = new TestTemplateManager();
        $testAuthorizationCode = new TestAuthorizationCode();
        $testAccessToken = new TestAccessToken();

        $this->userInfo = $this->getMockBuilder('fkooman\Rest\Plugin\Authentication\UserInfoInterface')->getMock();
        $this->userInfo->method('getUserId')->willReturn('admin');

        $io = $this->getMockBuilder('fkooman\OAuth\IO')->getMock();
        $io->method('getTime')->willReturn(1234567890);

        $this->oauthServer = new OAuthServer(
            $testTemplateManager,
            new NoRegistrationClient(),
            $testAuthorizationCode,
            $testAccessToken,
            $io
        );
    }

    public function testGetAuthorize()
    {
        $query = array(
            'client_id' => 'https://localhost',
            'redirect_uri' => 'https://localhost/cb',
            'state' => '12345',
            'scope' => 'post',
        );
        $request = $this->getAuthorizeRequest($query, 'GET');

        $this->assertSame(
            array(
                'getAuthorize' => array(
                    'client_id' => 'https://localhost',
                    'redirect_uri' => 'https://localhost/cb',
                    'scope' => 'post',
                    'request_url' => 'https://oauth.example/authorize?client_id=https%3A%2F%2Flocalhost&redirect_uri=https%3A%2F%2Flocalhost%2Fcb&state=12345&scope=post',
                ),
            ),
            $this->oauthServer->getAuthorize($request, $this->userInfo)
        );
    }

    public function testPostAuthorize()
    {
        $query = array(
            'redirect_uri' => 'https://localhost/cb',
            'state' => '12345',
            'scope' => 'post',
        );
        $request = $this->getAuthorizeRequest($query, 'POST', array('approval' => 'yes'));

        $this->assertSame(
            array(
                'HTTP/1.1 302 Found',
                'Content-Type: text/html;charset=UTF-8',
                'Location: https://localhost/cb?code=eyJ1c2VyX2lkIjoiYWRtaW4iLCJpYXQiOjEyMzQ1Njc4OTAsInJlZGlyZWN0X3VyaSI6Imh0dHBzOlwvXC9sb2NhbGhvc3RcL2NiIiwic2NvcGUiOiJwb3N0In0&state=12345',
                '',
                '',
            ),
            $this->oauthServer->postAuthorize($request, $this->userInfo)->toArray()
        );
    }

    public function testPostToken()
    {
        $request = new Request(
            array(
                'HTTPS' => 'on',
                'SERVER_NAME' => 'oauth.example',
                'SERVER_PORT' => '443',
                'REQUEST_URI' => '/token',
                'SCRIPT_NAME' => '/index.php',
                'PATH_INFO' => '/token',
                'QUERY_STRING' => '',
                'REQUEST_METHOD' => 'POST',
            ),
            array(
                'code' => 'eyJ1c2VyX2lkIjoiYWRtaW4iLCJpYXQiOjEyMzQ1Njc4OTAsInJlZGlyZWN0X3VyaSI6Imh0dHBzOlwvXC9sb2NhbGhvc3RcL2NiIiwic2NvcGUiOiJwb3N0In0',
                'redirect_uri' => 'https://localhost/cb',
            )
        );

        $this->assertSame(
            array(
                'HTTP/1.1 200 OK',
                'Content-Type: application/json',
                'Cache-Control: no-store',
                'Pragma: no-cache',
                '',
                '{"access_token":"eyJ1c2VyX2lkIjoiYWRtaW4iLCJpYXQiOjEyMzQ1Njc4OTAsInJlZGlyZWN0X3VyaSI6Imh0dHBzOlwvXC9sb2NhbGhvc3RcL2NiIiwic2NvcGUiOiJwb3N0In0","scope":"post"}',
            ),
            $this->oauthServer->postToken($request)->toArray()
        );
    }

    public function testPostIntrospect()
    {
        $request = new Request(
            array(
                'HTTPS' => 'on',
                'SERVER_NAME' => 'oauth.example',
                'SERVER_PORT' => '443',
                'REQUEST_URI' => '/introspect',
                'SCRIPT_NAME' => '/index.php',
                'PATH_INFO' => '/introspect',
                'QUERY_STRING' => '',
                'REQUEST_METHOD' => 'POST',
            ),
            array(
                'token' => 'eyJ1c2VyX2lkIjoiYWRtaW4iLCJpYXQiOjEyMzQ1Njc4OTAsInJlZGlyZWN0X3VyaSI6Imh0dHBzOlwvXC9sb2NhbGhvc3RcL2NiIiwic2NvcGUiOiJwb3N0In0',
            )
        );

        $this->assertSame(
            array(
                'HTTP/1.1 200 OK',
                'Content-Type: application/json',
                '',
                '{"active":true,"scope":"post","token_type":"bearer","iat":1234567890,"sub":"admin"}',
            ),
            $this->oauthServer->postIntrospect($request, $this->userInfo)->toArray()
        );
    }

    private function getAuthorizeRequest(array $query, $requestMethod = 'GET', $postBody = array())
    {
        $q = http_build_query($query);

        return new Request(
            array(
                'HTTPS' => 'on',
                'SERVER_NAME' => 'oauth.example',
                'SERVER_PORT' => '443',
                'REQUEST_URI' => sprintf('/authorize?%s', $q),
                'SCRIPT_NAME' => '/index.php',
                'PATH_INFO' => '/authorize',
                'QUERY_STRING' => $q,
                'REQUEST_METHOD' => $requestMethod,
            ),
            $postBody
        );
    }
}
