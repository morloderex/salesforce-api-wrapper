<?php

namespace Morloderex\Salesforce\Tests;

class AccessTokenTest extends TestCase {

    /** @test */
    public function can_create_access_token()
    {
        $issueDate  = \Carbon\Carbon::now();
        $expiryDate = \Carbon\Carbon::now()->addHour();

        $token = new \Morloderex\Salesforce\AccessToken(
            'abc123',
            $issueDate,
            $expiryDate,
            ['scopes'],
            'refresh-token',
            'signature',
            'access-token',
            'http://example.com'
        );

        $this->assertInstanceOf(\Morloderex\Salesforce\AccessToken::class, $token);

        $this->assertEquals('access-token', $token->accessToken());
        $this->assertEquals('refresh-token', $token->refreshToken());
        $this->assertEquals('http://example.com', $token->apiUrl());
        $this->assertEquals(['scopes'], $token->scopes());
    }

    /** @test */
    public function token_refresh_is_correct()
    {
        $token1 = new \Morloderex\Salesforce\AccessToken(
            'abc123',
            \Carbon\Carbon::now(),
            \Carbon\Carbon::now()->addHour(),
            ['scopes'],
            'refresh-token',
            'signature',
            'access-token',
            'http://example.com'
        );

        $this->assertFalse($token1->needsRefresh());

        $token2 = new \Morloderex\Salesforce\AccessToken(
            'abc123',
            \Carbon\Carbon::now()->subHours(2),
            \Carbon\Carbon::now()->subHour(),
            ['scopes'],
            'refresh-token',
            'signature',
            'access-token',
            'http://example.com'
        );
        $this->assertTrue($token2->needsRefresh());
    }

    /** @test */
    public function can_convert_to_json()
    {
        $token = new \Morloderex\Salesforce\AccessToken(
            'abc123',
            \Carbon\Carbon::now(),
            \Carbon\Carbon::now()->addHour(),
            ['scopes'],
            'refresh-token',
            'signature',
            'access-token',
            'http://example.com'
        );

        $this->assertJsonStringEqualsJsonString('{"tokenId": }', $token->toJson(), 'Token casts to a json string');
    }

    /** @test */
    public function updates_correctly()
    {
        $token = new \Morloderex\Salesforce\AccessToken(
            'abc123',
            \Carbon\Carbon::now(),
            \Carbon\Carbon::now()->addHour(),
            ['scopes'],
            'refresh-token',
            'signature',
            'access-token',
            'http://example.com'
        );


        $time = 1429281826;

        $token->refresh([
            'issued_at' => $time,
            'signature' => 'new-signature',
            'access_token' => 'new-access-token'
        ]);

        $this->assertEquals('new-access-token', $token->getAccessToken(), 'access token was not updated');
        $this->assertInstanceOf(\Carbon\Carbon::class, $token->getDateExpires());
        $this->assertInstanceOf(\Carbon\Carbon::class, $token->getDateIssued());
        $this->assertEquals($time, $token->getDateIssued()->timestamp, 'Timestamp saved and converted incorrectly');
    }
}
