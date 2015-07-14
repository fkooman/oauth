#!/usr/bin/php
<?php

require_once 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Url;

class IndieCertTest
{
    private $instanceUrl;
    private $me;
    private $introspectCredential;
    private $scope;

    public function __construct($instanceUrl, $me, $introspectCredential, $scope = 'post')
    {
        $this->instanceUrl = $instanceUrl;
        $this->me = $me;
        $this->introspectCredential = $introspectCredential;
        $this->scope = $scope;
    }

    public function runAuthorization()
    {
        $generatedState = bin2hex(openssl_random_pseudo_bytes(8));

        $authParams = array(
            'me' => $this->me,
            'scope' => $this->scope,
            'response_type' => 'code',
            'client_id' => 'https://example.org/',
            'redirect_uri' => 'https://example.org/callback',
            'state' => $generatedState,
        );

        $authUri = sprintf(
            '%s/authorize?%s',
            $this->instanceUrl,
            http_build_query($authParams)
        );
        $confirmUri = sprintf(
            '%s/authorize?%s',
            $this->instanceUrl,
            http_build_query($authParams)
        );

        $client = new Client(
            array(
                'defaults' => array(
                    'verify' => false,
                ),
            )
        );

        // AUTH
        $response = $client->get(
            $authUri,
            array(
                'auth' => array('admin', 'adm1n'),
            )
        );

        // CONFIRM
        $response = $client->post(
            $confirmUri,
            array(
                'headers' => array(
                    'Referer' => $this->instanceUrl,
                ),
                'body' => array(
                    'approval' => 'yes',
                ),
                'allow_redirects' => false,
                'auth' => array('admin', 'adm1n'),
            )
        );

        $u = Url::fromString($response->getHeader('Location'));

        //echo $u.PHP_EOL.PHP_EOL;

        $q = $u->getQuery();
        $code = $q['code'];
        $state = $q['state'];

        if ($state !== $generatedState) {
            throw new Exception('non matching state');
        }

        $verifyUri = sprintf(
            '%s/token',
            $this->instanceUrl
        );

        // ACCESS_TOKEN
        $response = $client->post(
            $verifyUri,
            array(
                'headers' => array(
                    'Accept' => 'application/json',
                ),
                'body' => array(
                    'code' => $code,
                    'state' => $generatedState,
                    'grant_type' => 'authorization_code',
                    'redirect_uri' => 'https://example.org/callback',
                    'client_id' => 'https://example.org/',
                ),
            )
        );

        $responseData = $response->json();

        //var_dump($responseData);

        $accessToken = $responseData['access_token'];

        // INTROSPECT
        $introspectUri = sprintf(
            '%s/introspect',
            $this->instanceUrl
        );

        $response = $client->post(
            $introspectUri,
            array(
                'headers' => array(
                    'Accept' => 'application/json',
                    'Authorization' => sprintf('Bearer %s', $this->introspectCredential),
                ),
                'body' => array(
                    'token' => $accessToken,
                ),
            )
        );
        $responseData = $response->json();

#        if ($responseData['sub'] !== $this->me) {
#            throw new Exception('non matching me');
#        }
#        if ($responseData['active'] !== true) {
#            throw new Exception('token does not appear to be valid');
#        }
#        if ($responseData['scope'] !== $this->scope) {
#            throw new Exception('we received unexpected scope');
#        }

//        var_dump($responseData);

        echo 'DONE'.PHP_EOL;
    }
}

try {
    $i = new IndieCertTest('https://localhost/oauth', 'foo@example.org', 'eyJpIjoiZm9vIiwicyI6InNlY3JldCJ9', 'post');
    $i->runAuthorization();
} catch (Exception $e) {
    echo $e->getMessage();
    echo $e->getRequest();
    echo $e->getResponse();
}
