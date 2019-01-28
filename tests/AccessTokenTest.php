<?php 

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
            'scopes',
            'type',
            'refresh-token',
            'signature',
            'access-token',
            'http://example.com'
        );

        $this->assertInstanceOf(\Morloderex\Salesforce\AccessToken::class, $token);

        $this->assertEquals('access-token', $token->getAccessToken());
        $this->assertEquals('refresh-token', $token->getRefreshToken());
        $this->assertEquals('http://example.com', $token->getApiUrl());
        $this->assertEquals('scopes', $token->getScope());

        $this->assertInstanceOf(\Carbon\Carbon::class, $token->getDateExpires());
        $this->assertInstanceOf(\Carbon\Carbon::class, $token->getDateIssued());
    }

    /** @test */
    public function token_refresh_is_correct()
    {
        $token1 = new \Morloderex\Salesforce\AccessToken(
            'abc123',
            \Carbon\Carbon::now(),
            \Carbon\Carbon::now()->addHour(),
            'scopes',
            'type',
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
            'scopes',
            'type',
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
            'scopes',
            'type',
            'refresh-token',
            'signature',
            'access-token',
            'http://example.com'
        );

        $this->isJson($token->toJson());
        $this->isJson((string)$token, 'Token casts to a json string');

    }


    /** @test */
    public function updates_correctly()
    {
        $token = new \Morloderex\Salesforce\AccessToken(
            'abc123',
            \Carbon\Carbon::now(),
            \Carbon\Carbon::now()->addHour(),
            'scopes',
            'type',
            'refresh-token',
            'signature',
            'access-token',
            'http://example.com'
        );


        $time = 1429281826;

        $token->updateFromSalesforceRefresh([
            'issued_at' => $time * 1000,
            'signature' => 'new-signature',
            'access_token' => 'new-access-token'
        ]);

        $this->assertEquals('new-access-token', $token->getAccessToken(), 'access token was updated');
        $this->assertInstanceOf(\Carbon\Carbon::class, $token->getDateExpires());
        $this->assertInstanceOf(\Carbon\Carbon::class, $token->getDateIssued());

        $this->assertEquals($time, $token->getDateIssued()->timestamp, 'Timestamp saved and converted correctly');

    }
}