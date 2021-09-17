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
    public function it_should_return_the_token_when_casting_to_a_string()
    {
        $this->assertEquals((string) $this->token, $this->token);
    }

    /** @test */
    public function it_should_return_the_token_when_calling_get_method()
    {
        $this->assertIsString($this->token->get());
    }
}