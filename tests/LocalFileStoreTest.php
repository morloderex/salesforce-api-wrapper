<?php

use \Mockery as m;

class LocalFileStoreTest extends TestCase
{

    /** @test */
    public function file_store_can_be_instantiated()
    {
        $tokenGenerator = m::mock('Morloderex\Salesforce\AccessTokenGenerator');
        $config = m::mock('Morloderex\Salesforce\TokenStore\LocalFileConfigInterface');
        $config->shouldReceive('getFilePath')->once()->andReturn('/foo');
        $fileStore = new \Morloderex\Salesforce\TokenStore\LocalFile($tokenGenerator, $config);

        $this->assertInstanceOf(\Morloderex\Salesforce\TokenStore\LocalFile::class, $fileStore);
    }


}