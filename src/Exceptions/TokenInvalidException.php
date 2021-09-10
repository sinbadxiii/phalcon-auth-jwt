<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT\Exceptions;

class TokenInvalidException extends JWTException
{
    protected $message = 'The token is invalid';
}