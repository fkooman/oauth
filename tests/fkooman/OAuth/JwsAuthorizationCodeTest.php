<?php

namespace fkooman\OAuth\Impl;

use PHPUnit_Framework_TestCase;

class JwsAuthorizationCodeTest extends PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $jwsAuthorizationCode = new JwsAuthorizationCode('secret');
        $this->assertSame(
            'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXUyJ9.eyJpYXQiOjEyMzQ1NiwianRpIjoic29tZV9ub25jZV90aGF0X211c3RfYmVfcmVjb3JkZWRfYWdhaW5zdF9yZXBsYXkiLCJyZWRpcmVjdF91cmkiOiJodHRwczpcL1wvZXhhbXBsZS5vcmdcL2NhbGxiYWNrIiwic2NvcGUiOiJmb28gYmFyIn0.kWdjQ69Mk-PQ61fvdsun1Y_iUxGyJC0IVEvhbC1r4vQ',
            $jwsAuthorizationCode->create(123456, 'https://example.org/callback', 'foo bar')
        );
    }

    public function testVerify()
    {
        $jwsAuthorizationCode = new JwsAuthorizationCode('secret');
        $this->assertSame(
            array(
                'iat' => 123456,
                'jti' => 'some_nonce_that_must_be_recorded_against_replay',
                'redirect_uri' => 'https://example.org/callback',
                'scope' => 'foo bar',
            ),
            $jwsAuthorizationCode->validate(
                'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXUyJ9.eyJpYXQiOjEyMzQ1NiwianRpIjoic29tZV9ub25jZV90aGF0X211c3RfYmVfcmVjb3JkZWRfYWdhaW5zdF9yZXBsYXkiLCJyZWRpcmVjdF91cmkiOiJodHRwczpcL1wvZXhhbXBsZS5vcmdcL2NhbGxiYWNrIiwic2NvcGUiOiJmb28gYmFyIn0.kWdjQ69Mk-PQ61fvdsun1Y_iUxGyJC0IVEvhbC1r4vQ'
            )
        );
    }

    public function testVerifyWrongKey()
    {
        $jwsAuthorizationCode = new JwsAuthorizationCode('x_secret');
        $this->assertFalse(
            $jwsAuthorizationCode->validate(
                'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXUyJ9.eyJpYXQiOjEyMzQ1NiwieHh4Ijoic29tZV9ub25jZV90aGF0X211c3RfYmVfcmVjb3JkZWRfYWdhaW5zdF9yZXBsYXkiLCJyZWRpcmVjdF91cmkiOiJodHRwczpcL1wvZXhhbXBsZS5vcmdcL2NhbGxiYWNrIiwic2NvcGUiOiJmb28gYmFyIn0.pKcEY1U8DQKER8El4WAJHjlbe_fDovvge5Wd77Zpkxw'
            )
        );
    }
}
