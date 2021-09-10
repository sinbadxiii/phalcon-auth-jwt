<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT\Claims;

use Sinbadxiii\PhalconAuthJWT\Contracts\Claim as ClaimContract;
use Sinbadxiii\PhalconAuthJWT\Options;

class Factory
{
    /**
     * The class map.
     */
    private static array $classMap = [
        Audience::NAME => Audience::class,
        Expiration::NAME => Expiration::class,
        IssuedAt::NAME => IssuedAt::class,
        Issuer::NAME => Issuer::class,
        JwtId::NAME => JwtId::class,
        NotBefore::NAME => NotBefore::class,
        Subject::NAME => Subject::class,
        HashedSubject::NAME => HashedSubject::class,
    ];

    /**
     * Get the instance of the claim when passing the name and value.
     */
    public static function get(string $name, $value = null, ?Options $options = null): ClaimContract
    {
        $options ??= new Options();

        $claim = static::has($name)
            ? call_user_func([static::$classMap[$name], 'make'], $value)
            : new Custom($name, $value);

        return static::applyClaimMethods($claim, [
            'setLeeway' => $options->leeway(),
            'setMaxRefreshPeriod' => $options->maxRefreshPeriod(),
        ]);
    }

    /**
     * Check whether the claim exists.
     */
    public static function has(string $name): bool
    {
        return array_key_exists($name, static::$classMap);
    }

    /**
     * Apply a multiple methods to the given claim if they exist.
     */
    protected static function applyClaimMethods(ClaimContract $claim, array $data): ClaimContract
    {
        foreach ($data as $method => $value) {
            $claim = method_exists($claim, $method)
                ? $claim->{$method}($value)
                : $claim;
        }

        return $claim;
    }
}