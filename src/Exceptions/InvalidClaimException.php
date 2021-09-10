<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT\Exceptions;

use Phalcon\Exception;
use Sinbadxiii\PhalconAuthJWT\Claims\Claim;

class InvalidClaimException extends JWTException
{
    public function __construct(Claim $claim, int $code = 0, ?Exception $previous = null)
    {
        parent::__construct('Invalid value provided for claim ['.$claim->getName().']', $code, $previous);
    }
}