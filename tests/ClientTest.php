<?php

use \Mockery as m;

class ClientTest extends TestCase {



    /** @test */
    public function client_can_be_instantiated()
    {
        $guzzle = m::mock('\GuzzleHttp\Client');

        $sfClient = new \Crunch\Salesforce\Client($this->getClientConfigMock(), $guzzle);

        $this->assertInstanceOf(\Crunch\Salesforce\Client::class, $sfClient);
    }

    /** @test */
    public function client_can_be_statically_instantiated()
    {
        $sfClient = \Crunch\Salesforce\Client::create('loginUrl', 'clientId', 'clientSecret');

        $this->assertInstanceOf(\Crunch\Salesforce\Client::class, $sfClient);
    }


    /** @test */
    public function client_will_login()
    {
        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->once()->andReturn('{
"access_token": "00D2C0000000gq5!AQMAQEG0Dm3KzpyleUCsq3_NcvC4Dm4h_54SJ_Y4OlrTawlaz57dsrdFkUPHMj3pH7WNz7oMp1l49QooZsew3fqkj1p_oKRm",
"instance_url": "https://API.salesforce.com",
"id": "https://test.salesforce.com/id/JSDKLFJDIOFJIODFJIO/DJFKLJSDKFJOSDFJK",
"token_type": "Bearer",
"issued_at": "1470764688209",
"signature": "VGVzdCBsaWJyYXJ5LiBZb3UgcmVhbGx5IHRob3VnaHQgdGhpcyB3b3VsZCBiZSBzb21ldGhpbmcgaW50ZXJlc3RpbmcgPw=="
}');
        $response->shouldReceive('getStatusCode')->twice()->andReturn(200);


        $guzzle = m::mock('\GuzzleHttp\Client');
        //Make sure the url contains the passed in data
        $guzzle->shouldReceive('post')->with(stringContainsInOrder('token'), [
            'headers'     => ['Accept' => 'application/json'],
            'form_params' => [
                'client_id'     => 'client_id',
                'client_secret' => 'client_secret',
                'grant_type'    => 'password',
                'username'      => 'superuser',
                'password'      => 'superpasswd',
            ],
        ])->once()->andReturn($response);


        $sfClient = new \Crunch\Salesforce\Client($this->getClientConfigMock(), $guzzle);
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


        $sfClient = new \Crunch\Salesforce\Client($this->getClientConfigMock(), $guzzle);
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


        $sfClient = new \Crunch\Salesforce\Client($this->getClientConfigMock(), $guzzle);
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
        $guzzle->shouldReceive('get')->with(stringContainsInOrder('SELECT+Name+FROM+Lead+LIMIT+10') , \Mockery::type('array'))->once()->andReturn($response);


        $sfClient = new \Crunch\Salesforce\Client($this->getClientConfigMock(), $guzzle);
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
        $guzzle->shouldReceive('post')->with(containsString('Test') , \Mockery::type('array'))->once()->andReturn($response);


        $sfClient = new \Crunch\Salesforce\Client($this->getClientConfigMock(), $guzzle);
        $sfClient->setAccessToken($this->getAccessTokenMock());


        $data = $sfClient->createRecord('Test', ['field1', 'field2']);

        $this->assertEquals($recordId, $data);
    }

    /** @test */
    public function client_can_update_record()
    {
        $recordId = 'abc' . rand(1000, 9999999);

        $response = m::mock('Psr\Http\Message\ResponseInterface');

        $guzzle = m::mock('\GuzzleHttp\Client');
        //Make sure the url contains the passed in data
        $guzzle->shouldReceive('patch')->with(stringContainsInOrder('Test', $recordId) , \Mockery::type('array'))->once()->andReturn($response);


        $sfClient = new \Crunch\Salesforce\Client($this->getClientConfigMock(), $guzzle);
        $sfClient->setAccessToken($this->getAccessTokenMock());


        $data = $sfClient->updateRecord('Test', $recordId, ['field1', 'field2']);

        $this->assertTrue($data);
    }

    /** @test */
    public function client_can_delete_record()
    {
        $recordId = 'abc' . rand(1000, 9999999);

        $response = m::mock('Psr\Http\Message\ResponseInterface');


        $guzzle = m::mock('\GuzzleHttp\Client');
        //Make sure the url contains the passed in data
        $guzzle->shouldReceive('delete')->with(stringContainsInOrder('Test', $recordId), \Mockery::type('array'))->once()->andReturn($response);


        $sfClient = new \Crunch\Salesforce\Client($this->getClientConfigMock(), $guzzle);
        $sfClient->setAccessToken($this->getAccessTokenMock());


        $data = $sfClient->deleteRecord('Test', $recordId);

        $this->assertTrue($data);
    }

    /** @test */
    public function client_can_complete_auth_process()
    {
        $recordId = 'abc' . rand(1000, 9999999);

        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->once()->andReturn(json_encode(['id' => $recordId]));


        $guzzle = m::mock('\GuzzleHttp\Client');
        //Make sure the url contains the passed in data
        $guzzle->shouldReceive('post')->with(stringContainsInOrder('services/oauth2/token'), \Mockery::type('array'))->once()->andReturn($response);


        $sfClient = new \Crunch\Salesforce\Client($this->getClientConfigMock(), $guzzle);
        $sfClient->setAccessToken($this->getAccessTokenMock());


        $sfClient->authorizeConfirm('authCode', 'redirectUrl');
    }

    /** @test */
    public function client_can_complete_token_refresh_process()
    {
        $recordId = 'abc' . rand(1000, 9999999);

        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->once()->andReturn(json_encode(['id' => $recordId]));


        $guzzle = m::mock('\GuzzleHttp\Client');
        //Make sure the url contains the passed in data
        $guzzle->shouldReceive('post')->with(stringContainsInOrder('services/oauth2/token'), \Mockery::type('array'))->once()->andReturn($response);


        $sfClient = new \Crunch\Salesforce\Client($this->getClientConfigMock(), $guzzle);
        $accessToken = $this->getAccessTokenMock();
        $accessToken->shouldReceive('updateFromSalesforceRefresh');
        $sfClient->setAccessToken($accessToken);

        $sfClient->refreshToken();
    }

    /** @test */
    public function client_can_get_login_url()
    {
        $guzzle = m::mock('\GuzzleHttp\Client');


        $sfClient = new \Crunch\Salesforce\Client($this->getClientConfigMock(), $guzzle);
        $sfClient->setAccessToken($this->getAccessTokenMock());


        $url = $sfClient->getLoginUrl('redirectUrl');

        $this->assertNotFalse(strpos($url, 'redirectUrl'));
    }

    /**
     * @test
     * @expectedException        Crunch\Salesforce\Exceptions\RequestException
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

        //Make sure the url contains the passed in data
        $guzzle->shouldReceive('post')->with(stringContainsInOrder('services/oauth2/token'), \Mockery::type('array'))->once()->andThrow($guzzleException);

        //Setup the client
        $sfClient = new \Crunch\Salesforce\Client($this->getClientConfigMock(), $guzzle);
        $sfClient->setAccessToken($this->getAccessTokenMock());

        //Try the auth flow - this should generate an exception
        $sfClient->authorizeConfirm('authCode', 'redirectUrl');
    }


    /**
     * Mock the client config interface
     * @return m\MockInterface
     */
    private function getClientConfigMock()
    {
        $config = m::mock('Crunch\Salesforce\ClientConfigInterface');
        $config->shouldReceive('getLoginUrl')->andReturn('http://login.example.com');
        $config->shouldReceive('getClientId')->andReturn('client_id');
        $config->shouldReceive('getClientSecret')->andReturn('client_secret');
        $config->shouldReceive('getVersion')->andReturn('v37.0');
        return $config;
    }

    private function getAccessTokenMock()
    {
        $accessToken = m::mock('Crunch\Salesforce\AccessToken');
        $accessToken->shouldReceive('getApiUrl')->andReturn('http://api.example.com');
        $accessToken->shouldReceive('getAccessToken')->andReturn('123456789abcdefghijk');
        $accessToken->shouldReceive('getRefreshToken')->andReturn('refresh123456789abcdefghijk');
        return $accessToken;
    }
}