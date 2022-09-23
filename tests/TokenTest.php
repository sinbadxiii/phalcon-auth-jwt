<?php

namespace Sinbadxiii\PhalconAuthJWT\Tests;

use Sinbadxiii\PhalconAuthJWT\Token;

class TokenTest extends AbstractTestCase
{
    protected $token;

    public function setUp(): void
    {
        parent::setUp();

        $this->token = new Token('foo.bar.baz');
    }

    /** @test */
    public function itShouldReturnTheTokenWhenCastingToAString()
    {
        $this->assertEquals((string) $this->token, $this->token);
    }

    /** @test */
    public function itShouldReturnTheTokenWhenCallingGetMethod()
    {
        $this->assertIsString($this->token->get());
    }
}