<?php

namespace Morloderex\Salesforce\Tests;

use \Mockery as m;
use Morloderex\Salesforce\AccessToken;
use Morloderex\Salesforce\AccessTokenGenerator;
use Morloderex\Salesforce\Client;
use Morloderex\Salesforce\ClientConfigInterface;
use Psr\Http\Message\ResponseInterface;

class ClientTest extends TestCase
{
    /** @test **/
    public function client_can_be_instantiated()
    {
        $guzzle = m::mock('\GuzzleHttp\Client');

        $sfClient = new Client($this->getClientConfigMock(), $guzzle, new AccessTokenGenerator);

        $this->assertInstanceOf(Client::class, $sfClient);
    }

    /** @test */
    public function client_can_be_statically_instantiated()
    {
        $sfClient = Client::create('loginUrl', 'clientId', 'clientSecret', 'redirectUrl');

        $this->assertInstanceOf(Client::class, $sfClient);
    }


    /** @test */
    public function client_will_login()
    {
        $response = m::mock(ResponseInterface::class);
        $response->shouldReceive('getBody')->once()->andReturn('{
"access_token": "00D2C0000000gq5!AQMAQEG0Dm3KzpyleUCsq3_NcvC4Dm4h_54SJ_Y4OlrTawlaz57dsrdFkUPHMj3pH7WNz7oMp1l49QooZsew3fqkj1p_oKRm",
"instance_url": "https://API.salesforce.com",
"id": "https://test.salesforce.com/id/JSDKLFJDIOFJIODFJIO/DJFKLJSDKFJOSDFJK",
"token_type": "Bearer",
"issued_at": "1470764688209",
"signature": "VGVzdCBsaWJyYXJ5LiBZb3UgcmVhbGx5IHRob3VnaHQgdGhpcyB3b3VsZCBiZSBzb21ldGhpbmcgaW50ZXJlc3RpbmcgPw=="
}');
        $response->shouldReceive('getStatusCode')->twice()->andReturn(200);


        $guzzle = m::mock(\GuzzleHttp\Client::class);
        //Make sure the url contains the passed in data
        $guzzle->shouldReceive('request')->with('post', stringContainsInOrder('token'), [
            'headers'     => ['Accept' => 'application/json'],
            'form_params' => [
                'client_id'     => 'client_id',
                'client_secret' => 'client_secret',
                'grant_type'    => 'password',
                'username'      => 'superuser',
                'password'      => 'superpasswd',
            ],
        ])->once()->andReturn($response);


        $sfClient = new Client($this->getClientConfigMock(), $guzzle, new AccessTokenGenerator());
        $sfClient->setAccessToken($this->getAccessTokenMock());
        $sfClient->login('superuser', 'superpasswd');
    }

    /** @test */
    public function client_will_get_record()
    {
        $recordId = 'abc' . rand(1000, 9999999);

        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->once()->andReturn(json_encode(['foo' => 'bar']));


        $guzzle = m::mock('\GuzzleHttp\Client');
        //Make sure the url contains the passed in data
        $guzzle->shouldReceive('get')->with(stringContainsInOrder('Test', $recordId, 'field1,field2') , \Mockery::type('array'))->once()->andReturn($response);


        $sfClient = new Client($this->getClientConfigMock(), $guzzle, new AccessTokenGenerator());
        $sfClient->setAccessToken($this->getAccessTokenMock());


        $data = $sfClient->getRecord('Test', $recordId, ['field1', 'field2']);

        $this->assertEquals(['foo' => 'bar'], $data);
    }

    /** @test */
    public function client_will_get_record_complete()
    {
        $recordId = 'abc' . rand(1000, 9999999);

        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->once()->andReturn(json_encode(['foo' => 'bar']));


        $guzzle = m::mock('\GuzzleHttp\Client');
        //Make sure the url contains the passed in data
        $guzzle->shouldReceive('get')->with(stringContainsInOrder('Test', $recordId) , \Mockery::type('array'))->once()->andReturn($response);


        $sfClient = new Client($this->getClientConfigMock(), $guzzle, new AccessTokenGenerator());
        $sfClient->setAccessToken($this->getAccessTokenMock());


        $data = $sfClient->getRecord('Test', $recordId);

        $this->assertEquals(['foo' => 'bar'], $data);
    }

    /** @test */
    public function client_can_search()
    {
        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->once()->andReturn(json_encode(['records' => [], 'done' => true]));


        $guzzle = m::mock('\GuzzleHttp\Client');
        //Make sure the url contains the passed in data
        $guzzle->shouldReceive('request')->with('get', m::type('string') , \Mockery::type('array'))->once()->andReturn($response);


        $sfClient = new Client($this->getClientConfigMock(), $guzzle, new AccessTokenGenerator());
        $sfClient->setAccessToken($this->getAccessTokenMock());


        $sfClient->search('SELECT Name FROM Lead LIMIT 10');
    }

    /** @test */
    public function client_can_create_record()
    {
        $recordId = 'abc' . rand(1000, 9999999);

        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->once()->andReturn(json_encode(['id' => $recordId]));


        $guzzle = m::mock('\GuzzleHttp\Client');
        //Make sure the url contains the passed in data
        $guzzle->shouldReceive('request')->with('post', m::type('string'), \Mockery::type('array'))->once()->andReturn($response);


        $sfClient = new Client($this->getClientConfigMock(), $guzzle, new AccessTokenGenerator());
        $sfClient->setAccessToken($this->getAccessTokenMock());


        $data = $sfClient->createRecord('Test', ['field1', 'field2']);

        $this->assertEquals($recordId, $data);
    }

    /** @test */
    public function client_can_update_record()
    {
        $recordId = 'abc' . rand(1000, 9999999);

        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->once()->andReturn('{}');

        $guzzle = m::mock('\GuzzleHttp\Client');
        //Make sure the url contains the passed in data
        $guzzle->shouldReceive('request')->with('patch', stringContainsInOrder('Test', $recordId) , \Mockery::type('array'))->once()->andReturn($response);


        $sfClient = new Client($this->getClientConfigMock(), $guzzle, new AccessTokenGenerator());
        $sfClient->setAccessToken($this->getAccessTokenMock());


        $data = $sfClient->updateRecord('Test', $recordId, ['field1', 'field2']);

        $this->assertTrue($data);
    }

    /** @test */
    public function client_can_delete_record()
    {
        $recordId = 'abc' . rand(1000, 9999999);

        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->andReturn('{}');


        $guzzle = m::mock('\GuzzleHttp\Client');
        //Make sure the url contains the passed in data
        $guzzle->shouldReceive('request')->with('delete', m::type('string'), \Mockery::type('array'))->once()->andReturn($response);

        $sfClient = new Client($this->getClientConfigMock(), $guzzle, new AccessTokenGenerator());
        $sfClient->setAccessToken($this->getAccessTokenMock());


        $data = $sfClient->deleteRecord('Test', $recordId);

        $this->assertTrue($data);
    }

    /** @test */
    public function client_can_complete_auth_process()
    {
        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->once()->andReturn(json_encode([
            'issued_at' => time(),
            'id' => 'some-fake-id',
            'access_token' => 'some-fake-token',
            'instance_url' => 'some-instance',
            'signature' => 'string'
        ]));


        $guzzle = m::mock('\GuzzleHttp\Client');
        //Make sure the url contains the passed in data
        $guzzle->shouldReceive('request')->with('post', stringContainsInOrder('services/oauth2/token'), \Mockery::type('array'))->once()->andReturn($response);

        $sfClient = new Client($this->getClientConfigMock(), $guzzle, new AccessTokenGenerator());

        $response = $sfClient->authorizeConfirm('authCode', 'redirect');

        $this->assertNotNull($sfClient->getAccessToken());

        $this->assertInstanceOf(AccessToken::class, $response);
    }

    /** @test */
    public function client_can_complete_token_refresh_process()
    {
        $recordId = 'abc' . rand(1000, 9999999);

        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->once()->andReturn(json_encode(['id' => $recordId]));


        $guzzle = m::mock('\GuzzleHttp\Client');
        //Make sure the url contains the passed in data
        $guzzle->shouldReceive('request')->with('post', stringContainsInOrder('services/oauth2/token'), \Mockery::type('array'))->once()->andReturn($response);


        $sfClient = new Client($this->getClientConfigMock(), $guzzle, new AccessTokenGenerator());
        $accessToken = $this->getAccessTokenMock();
        $accessToken->shouldReceive('refresh')->once()->andReturnSelf();
        $sfClient->setAccessToken($accessToken);

        $response = $sfClient->refreshToken();
        $this->assertInstanceOf(AccessToken::class, $response);

    }

    /** @test */
    public function client_can_get_login_url()
    {
        $guzzle = m::mock('\GuzzleHttp\Client');

        $sfClient = new Client($this->getClientConfigMock(), $guzzle, new AccessTokenGenerator());
        $sfClient->setAccessToken($this->getAccessTokenMock());

        $url = $sfClient->getLoginUrl();

        $this->assertEquals('http://login.example.comservices/oauth2/authorize?client_id=client_id&redirect_uri=https%3A%2F%2Fexample.dk%2Fredirect&response_type=code&grant_type=authorization_code', $url);
    }

    /**
     * @test
     * @expectedException        Morloderex\Salesforce\Exceptions\RequestException
     * @expectedExceptionMessage expired authorization code
     */
    public function client_can_parse_auth_flow_error()
    {
        //Error Response
        $errorResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $errorResponse->shouldReceive('getBody')->once()->andReturn('{"error_description":"expired authorization code","error":"invalid_grant"}');
        $errorResponse->shouldReceive('getStatusCode')->once()->andReturn(400);

        //Make guzzle throw an exception with the above message
        $guzzle = m::mock('\GuzzleHttp\Client');
        $guzzleException = m::mock('GuzzleHttp\Exception\RequestException');
        $guzzleException->shouldReceive('getResponse')->andReturn($errorResponse);
        $guzzleException->shouldReceive('hasResponse')->andReturn(true);

        //Make sure the url contains the passed in data
        $guzzle->shouldReceive('request')->with(
            m::type('string'),
            m::type('string'),
            m::type('array')
        )->once()->andThrow($guzzleException);

        //Setup the client
        $sfClient = new Client($this->getClientConfigMock(), $guzzle, new AccessTokenGenerator);
        $sfClient->setAccessToken($this->getAccessTokenMock());

        //Try the auth flow - this should generate an exception
        $sfClient->authorizeConfirm('authCode');
    }


    /**
     * Mock the client config interface
     * @return m\MockInterface
     */
    private function getClientConfigMock()
    {
        $config = m::mock(ClientConfigInterface::class);
        $config->shouldReceive('getLoginUrl')->andReturn('http://login.example.com');
        $config->shouldReceive('getClientId')->andReturn('client_id');
        $config->shouldReceive('getClientSecret')->andReturn('client_secret');
        $config->shouldReceive('getVersion')->andReturn('v37.0');
        return $config;
    }

    private function getAccessTokenMock()
    {
        $accessToken = m::mock(AccessToken::class);
        $accessToken->shouldReceive('apiUrl')->andReturn('http://api.example.com');
        $accessToken->shouldReceive('accessToken')->andReturn('123456789abcdefghijk');
        $accessToken->shouldReceive('refreshToken')->andReturn('refresh123456789abcdefghijk');
        return $accessToken;
    }
}
