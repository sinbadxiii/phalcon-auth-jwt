<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT\Http;

use Sinbadxiii\PhalconAuthJWT\Support\ForwardsCalls;
use Sinbadxiii\PhalconAuthJWT\Token;

class TokenResponse
{
    use ForwardsCalls;

    /**
     * The token itself.
     */
    protected Token $token;

    /**
     * The token ttl.
     */
    protected int $ttl;

    /**
     * The token type.
     */
    protected string $type;

    /**
     * Constructor.
     */
    public function __construct(Token $token, int $ttl, string $type = 'bearer')
    {
        $this->token = $token;
        $this->ttl = $ttl;
        $this->type = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function toResponse()
    {
        return [
            'access_token' => $this->token->get(),
            'token_type' => $this->type,
            'expires_in' => $this->ttl * 60,
        ];
    }

    /**
     * Get the token when casting to string.
     */
    public function __toString(): string
    {
        return $this->token->get();
    }

    /**
     * Magically call the Token.
     *
     * @throws \BadMethodCallException
     *
     * @return mixed
     */
    public function __call(string $method, array $parameters)
    {
        return $this->forwardCallTo($this->token, $method, $parameters);
    }
}