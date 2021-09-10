<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT\Validators;

use Sinbadxiii\PhalconAuthJWT\Builder;
use Sinbadxiii\PhalconAuthJWT\Claims\Collection;
use Sinbadxiii\PhalconAuthJWT\Claims\Expiration;
use Sinbadxiii\PhalconAuthJWT\Options;
use Sinbadxiii\PhalconAuthJWT\Payload;

class PayloadValidator extends Validator
{
    public static function check(Collection $claims, ?Options $options = null): Payload
    {
        $options ??= new Options();

        if (! static::hasRequiredClaims($claims, $options)) {
            static::throwFailed('JWT does not contain the required claims');
        }

        $claims->verify();

        foreach ($options->validators() as $name => $validator) {
            if ($claim = $claims->getByClaimName($name)) {
                if ($validator($claim->getValue(), $name) === false) {
                    static::throwFailed('Validation failed for claim ['.$name.']');
                }
            }
        }

        return new Payload($claims);
    }

    protected static function hasRequiredClaims(Collection $claims, ?Options $options = null): bool
    {
        $requiredClaims = $claims->has(Expiration::NAME)
            ? $options->requiredClaims()
            : static::except($options->requiredClaims(), [Expiration::NAME]);

        return $claims->hasAllClaims($requiredClaims);
    }

    private static function except(array $array, array $keys)
    {
        Builder::forget($array, $keys);

        return $array;
    }
}
