<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT\Contracts\Providers;

use Sinbadxiii\PhalconAuthJWT\Options;
use Sinbadxiii\PhalconAuthJWT\Payload;
use Sinbadxiii\PhalconAuthJWT\Token;

interface JWT
{
    /**
     * Create a JSON Web Token.
     */
    public function encode(array $payload): string;

    /**
     * Decode a JSON Web Token.
     */
    public function decode(string $token): array;

    /**
     * Get the decoded token as a Payload instance.
     */
    public function payload(Token $token, ?Options $options = null): Payload;

    /**
     * Get an encoded Token instance.
     */
    public function token(Payload $payload): Token;
}