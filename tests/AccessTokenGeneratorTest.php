<?php

namespace Morloderex\Salesforce\Tests;

class AccessTokenGeneratorTest extends TestCase
{
    /** @test */
    public function token_gets_generated_from_json()
    {
        $jsonToken = json_encode([
            'id' => '',
            'dateIssued' => '',
            'dateExpires' => '',
            'scope' => '',
            'tokenType' => '',
            'refreshToken' => '',
            'signature' => '',
            'accessToken' => '',
            'apiUrl' => '',
        ]);
        $tokenGenerator = new \Morloderex\Salesforce\AccessTokenGenerator();
        $token = $tokenGenerator->createFromJson($jsonToken);

        $this->assertInstanceOf(\Morloderex\Salesforce\AccessToken::class, $token, 'Token generated not an instance of AccessToken');
    }

    /** @test */
    public function token_gets_generated_from_sf_response()
    {
        $responseData = [
            'id' => '',
            'issued_at' => '',
            'scope' => '',
            'token_type' => '',
            'refresh_token' => '',
            'signature' => '',
            'access_token' => '',
            'instance_url' => '',
        ];
        $tokenGenerator = new \Morloderex\Salesforce\AccessTokenGenerator();
        $token = $tokenGenerator->createFromSalesforceResponse($responseData);

        $this->assertInstanceOf(\Morloderex\Salesforce\AccessToken::class, $token, 'Token generated not an instance of AccessToken');
    }

    /** @test */
    public function token_gets_generated_from_limited_sf_response()
    {
        $responseData = [
            'id' => '',
            'issued_at' => time(),
            'access_token' => '',
            'instance_url' => '',
        ];
        $tokenGenerator = new \Morloderex\Salesforce\AccessTokenGenerator();
        $token = $tokenGenerator->createFromSalesforceResponse($responseData);

        $this->assertInstanceOf(\Morloderex\Salesforce\AccessToken::class, $token, 'Token generated not an instance of AccessToken');
    }

    /** @test */
    public function token_date_generated_from_sf_response()
    {
        $time = time();
        $responseData = [
            'id' => '',
            'issued_at' => $time,
            'scope' => '',
            'token_type' => '',
            'refresh_token' => '',
            'signature' => '',
            'access_token' => '',
            'instance_url' => '',
        ];
        $tokenGenerator = new \Morloderex\Salesforce\AccessTokenGenerator();
        $token = $tokenGenerator->createFromSalesforceResponse($responseData);

        $this->assertInstanceOf(\Carbon\Carbon::class, $token->getDateIssued(), 'Token issued date not a carbon instance');
        $this->assertEquals($time, $token->getDateIssued()->timestamp, 'Token issue timestamp doesnt match');
        $this->assertEquals($time + (60*55), $token->getDateExpires()->timestamp, 'Token expiry time not 55 minutes after creation');
    }

    /** @test */
    public function token_date_generated_from_stored_json()
    {
        $issueDate = '2015-01-02 10:11:12';
        $expiryDate = '2015-01-02 11:11:12';
        $jsonToken = json_encode([
            'id' => '',
            'dateIssued' => $issueDate,
            'dateExpires' => $expiryDate,
            'scope' => '',
            'tokenType' => '',
            'refreshToken' => '',
            'signature' => '',
            'accessToken' => '',
            'apiUrl' => '',
        ]);
        $tokenGenerator = new \Morloderex\Salesforce\AccessTokenGenerator();
        $token = $tokenGenerator->createFromJson($jsonToken);

        $this->assertInstanceOf(\Carbon\Carbon::class, $token->getDateIssued(), 'Token issued date not a carbon instance');
        $this->assertEquals($issueDate, $token->getDateIssued()->format('Y-m-d H:i:s'), 'Token issue timestamp doesnt match');
        $this->assertEquals($expiryDate, $token->getDateExpires()->format('Y-m-d H:i:s'), 'Token expiry time not 1 hour after creation');
    }
}
