<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT\Validators;

use Sinbadxiii\PhalconAuthJWT\Exceptions\JWTException;
use Sinbadxiii\PhalconAuthJWT\Exceptions\TokenInvalidException;

abstract class Validator
{
    public static function isValid(...$args): bool
    {
        try {
            forward_static_call('static::check', ...$args);
        } catch (JWTException $e) {
            return false;
        }

        return true;
    }

    public static function throwFailed(string $message = 'Invalid'): void
    {
        throw new TokenInvalidException($message);
    }
}
