<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT\Exceptions;

/**
 * Class UnauthorizedHttpException
 * @package Sinbadxiii\PhalconAuthJWT\Exceptions
 */
class UnauthorizedHttpException extends JWTException
{
    protected $message = 'Unauthorized';
}