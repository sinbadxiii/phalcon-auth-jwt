<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT\Exceptions;

class TokenExpiredException extends JWTException
{
    protected $message = 'The token has expired';
}