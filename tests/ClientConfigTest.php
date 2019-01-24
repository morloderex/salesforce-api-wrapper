<?php

use \Mockery as m;

class ClientConfigTest extends TestCase {


    /** @test */
    public function client_config_can_be_instantiated()
    {
        $sfClientConfig = new \Crunch\Salesforce\ClientConfig('url', 'clientId', 'clientSecret');

        $this->assertInstanceOf(\Crunch\Salesforce\ClientConfig::class, $sfClientConfig);
        $this->assertInstanceOf(\Crunch\Salesforce\ClientConfigInterface::class, $sfClientConfig);
    }

    /** @test */
    public function client_config_data_can_be_accessed()
    {
        $sfClientConfig = new \Crunch\Salesforce\ClientConfig('url', 'clientId', 'clientSecret', 'v37.0');

        $this->assertEquals('url', $sfClientConfig->getLoginUrl());
        $this->assertEquals('clientId', $sfClientConfig->getClientId());
        $this->assertEquals('clientSecret', $sfClientConfig->getClientSecret());
        $this->assertEquals('v37.0', $sfClientConfig->getVersion());
    }

}