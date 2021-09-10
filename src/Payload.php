<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT;

use ArrayAccess;
use BadMethodCallException;
use Countable;
use JsonSerializable;
use Phalcon\Helper\Arr;
use Sinbadxiii\PhalconAuthJWT\Claims\Claim;
use Sinbadxiii\PhalconAuthJWT\Claims\Collection;
use Sinbadxiii\PhalconAuthJWT\Contracts\Claim as ClaimContract;
use Sinbadxiii\PhalconAuthJWT\Exceptions\PayloadException;
use Sinbadxiii\PhalconAuthJWT\Support\ForwardsCalls;

class Payload implements ArrayAccess, Countable, JsonSerializable
{
    use ForwardsCalls;

    private Collection $claims;

    public function __construct(Collection $claims)
    {
        $this->claims = $claims;
    }

    public function getClaims(): Collection
    {
        return $this->claims;
    }

    public function matches(array $values, bool $strict = false): bool
    {
        if (empty($values)) {
            return false;
        }

        $claims = $this->getClaims();

        foreach ($values as $key => $value) {
            if (! $claims->has($key) || ! $claims->get($key)->matches($value, $strict)) {
                return false;
            }
        }

        return true;
    }

    public function matchesStrict(array $values): bool
    {
        return $this->matches($values, true);
    }

    public function get($claim = null)
    {
        if ($claim !== null) {
            if (is_array($claim)) {
                return array_map([$this, 'get'], $claim);
            }

            return Arr::get($this->toArray(), $claim);
        }

        return $this->toArray();
    }

    public function getInternal(string $claim): ?ClaimContract
    {
        return $this->claims->getByClaimName($claim);
    }

    public function has(Claim $claim): bool
    {
        return $this->claims->has($claim->getName());
    }

    public function hasKey(string $claim): bool
    {
        return $this->offsetExists($claim);
    }

    public function token(): Token
    {
        return JWTManager::encode($this);
    }

    public function toArray(): array
    {
        return $this->claims->toPlainArray();
    }

    /**
     * Convert the object into something JSON serializable.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Get the payload as JSON.
     */
    public function toJson($options = JSON_UNESCAPED_SLASHES): string
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Get the payload as a string.
     */
    public function __toString(): string
    {
        return $this->toJson();
    }

    /**
     * Determine if an item exists at an offset.
     *
     * @param  mixed  $key
     */
    public function offsetExists($key): bool
    {
        return Arr::has($this->toArray(), $key);
    }

    /**
     * Get an item at a given offset.
     *
     * @param  mixed  $key
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        return Arr::get($this->toArray(), $key);
    }

    /**
     * Don't allow changing the payload as it should be immutable.
     *
     * @param  mixed  $key
     * @param  mixed  $value
     *
     * @throws PayloadException
     */
    public function offsetSet($key, $value)
    {
        throw new PayloadException();
    }

    public function offsetUnset($key)
    {
        throw new PayloadException();
    }

    /**
     * Count the number of claims.
     */
    public function count(): int
    {
        return count($this->toArray());
    }

    /**
     * Invoke the Payload as a callable function.
     *
     * @param  mixed  $claim
     *
     * @return mixed
     */
    public function __invoke($claim = null)
    {
        return $this->get($claim);
    }

    /**
     * Magically get a claim value.
     *
     * @throws \BadMethodCallException
     *
     * @return mixed
     */
    public function __call(string $method, array $parameters)
    {
        if (preg_match('/get(.+)\b/i', $method, $matches)) {
            $match = $matches[1];

            foreach ($this->claims as $claim) {
                if (get_class($claim) === 'Sinbadxiii\\PhalconAuthJWT\\Claims\\'.$match) {
                    return $claim->getValue();
                }
            }

            throw new BadMethodCallException(
                sprintf('The claim [%s] does not exist on the payload.', $match ?? $method)
            );
        }

        static::throwBadMethodCallException($method);
    }
}