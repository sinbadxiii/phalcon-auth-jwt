<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT\Exceptions;

class PayloadException extends JWTException
{
    protected $message = 'The payload is immutable';
}