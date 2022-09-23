<?php

namespace Sinbadxiii\PhalconAuthJWT\Tests\Http;

use Phalcon\Http\Request;
use Sinbadxiii\PhalconAuthJWT\Http\Parser\Chains\AuthHeaders;
use Sinbadxiii\PhalconAuthJWT\Http\Parser\Chains\InputSource;
use Sinbadxiii\PhalconAuthJWT\Http\Parser\Chains\QueryString;
use Sinbadxiii\PhalconAuthJWT\Http\Parser\Parser;
use Sinbadxiii\PhalconAuthJWT\Http\Parser\ParserInterface;
use Sinbadxiii\PhalconAuthJWT\Tests\AbstractTestCase;

class ParserTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function itShouldReturnTheTokenFromTheAuthorizationHeader()
    {
        $request = new Request();
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_SERVER['HTTP_AUTHORIZATION'] = "Bearer myToken";

        $parser = new Parser($request);

        $parser->setChain([
            'query' => new QueryString(),
            'input' => new InputSource(),
            'header' => new AuthHeaders()
        ]);

        $this->assertSame($parser->parseToken(), 'myToken');
        $this->assertTrue($parser->hasToken());
    }

    /** @test */
    public function itShouldReturnTheTokenFromThePrefixedAuthenticationHeader()
    {
        $request = new Request();
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_SERVER['HTTP_AUTHORIZATION'] = "Bearer Custom myToken";

        $parser = new Parser($request);

        $parser->setChain([
            'query' => new QueryString(),
            'input' => new InputSource(),
            'header' => new AuthHeaders()
        ]);

        $parser->get('header')->setHeaderPrefix('Custom');

        $this->assertSame($parser->parseToken(), 'myToken');
        $this->assertTrue($parser->hasToken());
    }

    /** @test */
    public function itShouldReturnTheTokenFromTheCustomAuthenticationHeader()
    {
        $request = new Request();
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_SERVER['HTTP_CUSTOM_AUTHORIZATION'] = "Bearer myToken";

        $parser = new Parser($request);

        $parser->setChain([
            'query' => new QueryString(),
            'input' => new InputSource(),
            'header' => (new AuthHeaders())->setHeaderName('CUSTOM_AUTHORIZATION')
        ]);

        $this->assertSame($parser->parseToken(), 'myToken');
        $this->assertTrue($parser->hasToken());
    }

    /** @test */
    public function itShouldReturnTheTokenFromTheAltAuthorizationHeaders()
    {
        $request = new Request();
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_SERVER['HTTP_AUTHORIZATION'] = "Bearer myToken";

        $parser = new Parser($request, [
            'header' => new AuthHeaders(),
            'query' => new QueryString(),
            'input' => new InputSource()
        ]);

        $this->assertSame($parser->parseToken(), 'myToken');
        $this->assertTrue($parser->hasToken());

        $_SERVER = [];

        $request2 = new Request();
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_SERVER['HTTP_AUTHORIZATION'] = "Bearer myTokenFoo";

        $parser->setRequest($request2);

        $this->assertSame($parser->parseToken(), 'myTokenFoo');
        $this->assertTrue($parser->hasToken());
    }

    /** @test */
    public function itShouldReturnTheTokenFromQueryString()
    {
        $request = new Request();
        $_SERVER["REQUEST_METHOD"] = "GET";
        $_GET['token'] = "myToken";
        $_REQUEST["token"] = "myToken";


        $parser = new Parser($request);
        $parser->setChain([
            'header' => new AuthHeaders(),
            'query' => new QueryString(),
            'input' => new InputSource()
        ]);

        $this->assertSame($parser->parseToken(), 'myToken');
        $this->assertTrue($parser->hasToken());
    }

    /** @test */
    public function itShouldReturnTheTokenFromTheCustomQueryString()
    {
        $request = new Request();
        $_SERVER["REQUEST_METHOD"] = "GET";
        $_GET['custom_token_key'] = $_REQUEST["custom_token_key"]  = "myToken";

        $parser = new Parser($request);
        $parser->setChain([
            'header' => new AuthHeaders(),
            'query' => (new QueryString())->setKey('custom_token_key'),
            'input' => new InputSource()
        ]);

        $this->assertSame($parser->parseToken(), 'myToken');
        $this->assertTrue($parser->hasToken());
    }

    /** @test */
    public function itShouldReturnTheTokenFromTheQueryStringNotTheInputSource()
    {
        $request = new Request();
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_GET['token'] = $_REQUEST['token'] = "myToken";
        $_POST["token"] = "myTokenFoo";

        $parser = new Parser($request);
        $parser->setChain([
            'header' => new AuthHeaders(),
            'query' => new QueryString(),
            'input' => new InputSource()
        ]);

        $this->assertSame($parser->parseToken(), 'myToken');
        $this->assertTrue($parser->hasToken());
    }

    /** @test */
    public function itShouldReturnTheTokenFromTheCustomQueryStringNotTheCustomInputSource()
    {
        $request = new Request();
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_GET['custom_token_key'] = $_REQUEST['custom_token_key'] = "myToken";
        $_POST["custom_token_key"] = "myTokenFoo";

        $parser = new Parser($request);
        $parser->setChain([
            'header' => new AuthHeaders(),
            'query' => (new QueryString())->setKey('custom_token_key'),
            'input' => (new InputSource())->setKey('custom_token_key')
        ]);

        $this->assertSame($parser->parseToken(), 'myToken');
        $this->assertTrue($parser->hasToken());
    }

    /** @test */
    public function itShouldReturnTheTokenFromInputSource()
    {
        $request = new Request();
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_POST["token"] = "myToken";
        $_SERVER['HTTP_CONTENT_TYPE'] = "application/json";

        $parser = new Parser($request);
        $parser->setChain([
            'header' => new AuthHeaders(),
            'query' => new QueryString(),
            'input' => new InputSource()
        ]);

        $this->assertSame($parser->parseToken(), 'myToken');
        $this->assertTrue($parser->hasToken());
    }

    /** @test */
    public function itShouldReturnTheTokenFromTheCustomInputSource()
    {
        $request = new Request();
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_POST["custom_token_key"] = "myToken";

        $parser = new Parser($request);
        $parser->setChain([
            'header' => new AuthHeaders(),
            'query' => new QueryString(),
            'input' => (new InputSource())->setKey('custom_token_key')
        ]);

        $this->assertSame($parser->parseToken(), 'myToken');
        $this->assertTrue($parser->hasToken());

        $this->flushAll();
    }
}