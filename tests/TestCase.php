<?php

namespace Morloderex\Salesforce\Tests;

use \Mockery as m;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    public function tearDown()
    {
        m::close();
    }
}
