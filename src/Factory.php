<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT;

use Sinbadxiii\PhalconAuthJWT\Claims\ClaimInterface;
use Sinbadxiii\PhalconAuthJWT\Claims\Collection;
use Sinbadxiii\PhalconAuthJWT\Claims\Factory as ClaimFactory;
use Sinbadxiii\PhalconAuthJWT\Validators\PayloadValidator;

class Factory
{
    /**
     * Create a Payload instance.
     */
    public static function make($claims = [], ?Options $options = null): Payload
    {
        $keys = array_keys($claims);

        $items = array_map(function ($value, $key) use ($options) {
                if ($value instanceof ClaimInterface) {
                    return $value;
                }

                if (! is_string($key)) {
                    $key = $value;
                    $value = null;
                }

                return ClaimFactory::get($key, $value, $options);
            }, $claims, $keys);

        $claims = new Collection(array_combine($keys, $items));

        return PayloadValidator::check($claims, $options);
    }
}