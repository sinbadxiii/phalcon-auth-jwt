<?php

namespace Sinbadxiii\PhalconAuthJWT\Tests;

use Carbon\Carbon;
use Mockery;
use PHPUnit\Framework\TestCase;

abstract class AbstractTestCase extends TestCase
{
    protected $testNowTimestamp;

    public function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow($now = Carbon::now());
        $this->testNowTimestamp = $now->getTimestamp();

        $this->flushAll();
    }

    public function tearDown(): void
    {
        Carbon::setTestNow();
        Mockery::close();

        parent::tearDown();
    }

    public function flushAll()
    {
        $_SERVER  = [];
        $_REQUEST = [];
        $_POST    = [];
        $_GET     = [];
    }
}