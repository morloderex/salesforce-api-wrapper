<?php

use \Mockery as m;

class ClientConfigTest extends TestCase {


    /** @test */
    public function client_config_can_be_instantiated()
    {
        $sfClientConfig = new \Morloderex\Salesforce\ClientConfig('url', 'clientId', 'clientSecret', 'redirectUrl');

        $this->assertInstanceOf(\Morloderex\Salesforce\ClientConfig::class, $sfClientConfig);
        $this->assertInstanceOf(\Morloderex\Salesforce\ClientConfigInterface::class, $sfClientConfig);
    }

    /** @test */
    public function client_config_data_can_be_accessed()
    {
        $sfClientConfig = new \Morloderex\Salesforce\ClientConfig('url', 'clientId', 'clientSecret', 'redirectUrl');

        $this->assertEquals('url', $sfClientConfig->getLoginUrl());
        $this->assertEquals('clientId', $sfClientConfig->getClientId());
        $this->assertEquals('clientSecret', $sfClientConfig->getClientSecret());
        $this->assertEquals('v37.0', $sfClientConfig->getVersion());
    }

}