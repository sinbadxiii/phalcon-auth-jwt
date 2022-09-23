<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT\Exceptions;

use Exception;
use Sinbadxiii\PhalconAuthJWT\Claims\ClaimInterface;

class InvalidClaimException extends JWTException
{
    public function __construct(ClaimInterface $claim, int $code = 0, ?Exception $previous = null)
    {
        parent::__construct('Invalid value provided for claim ['.$claim->getName().']', $code, $previous);
    }
}