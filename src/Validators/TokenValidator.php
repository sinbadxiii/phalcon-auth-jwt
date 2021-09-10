<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT\Validators;

class TokenValidator extends Validator
{
    /**
     * Check the structure of the token.
     *
     * @throws \Sinbadxiii\PhalconAuthJWT\Exceptions\TokenInvalidException
     */
    public static function check(string $token): string
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            static::throwFailed('Wrong number of segments');
        }

        $parts = array_filter(array_map('trim', $parts));

        if (count($parts) !== 3 || implode('.', $parts) !== $token) {
            static::throwFailed('Malformed token');
        }

        return $token;
    }
}