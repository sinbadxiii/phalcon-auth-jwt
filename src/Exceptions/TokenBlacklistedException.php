<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT\Exceptions;

class TokenBlacklistedException extends TokenInvalidException
{
    /**
     * {@inheritdoc}
     */
    protected $message = 'The token has been blacklisted';
}