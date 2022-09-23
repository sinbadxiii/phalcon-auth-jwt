<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT\Http\Parser;

use Phalcon\Support\Helper\Arr\Get;

class Parser
{
    /**
     * The request.
     */
    protected $request;

    /**
     * The chain.
     */
    private array $chain;

    /**
     * Constructor.
     */
    public function __construct($request, array $chain = [])
    {
        $this->request = $request;
        $this->chain = $chain;
    }

    /**
     * Get the parser chain.
     */
    public function getChain(): array
    {
        return $this->chain;
    }

    /**
     * Set the order of the parser chain.
     */
    public function setChain(array $chain): self
    {
        $this->chain = $chain;

        return $this;
    }

    /**
     * Alias for setting the order of the chain.
     */
    public function setChainOrder(array $chain): self
    {
        return $this->setChain($chain);
    }

    /**
     * Get a parser by key.
     */
    public function get(string $key): ?ParserInterface
    {
        $arrGet = new Get();
        return $arrGet($this->chain, $key);
    }

    /**
     * Iterate through the parsers and attempt to retrieve
     * a value, otherwise return null.
     */
    public function parseToken(): ?string
    {
        foreach ($this->chain as $parser) {
            if ($token = $parser->parse($this->request)) {
                return $token;
            }
        }

        return null;
    }

    /**
     * Check whether a token exists in the chain.
     */
    public function hasToken(): bool
    {
        return $this->parseToken() !== null;
    }

    /**
     * Set the request instance.
     */
    public function setRequest($request): self
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Get the request instance.
     */
    public function getRequest()
    {
        return $this->request;
    }
}