<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT;

use ArrayAccess;
use BadMethodCallException;
use Countable;
use JsonSerializable;
use Phalcon\Support\Helper\Arr\Get;
use Phalcon\Support\Helper\Arr\Has;
use Sinbadxiii\PhalconAuthJWT\Claims\ClaimInterface;
use Sinbadxiii\PhalconAuthJWT\Claims\Collection;
use Sinbadxiii\PhalconAuthJWT\Exceptions\PayloadException;

class Payload implements ArrayAccess, Countable, JsonSerializable
{
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

            $arrGet = new Get();
            return $arrGet($this->toArray(), $claim);
        }

        return $this->toArray();
    }

    public function getInternal(string $claim): ?ClaimInterface
    {
        return $this->claims->getByClaimName($claim);
    }

    public function has(ClaimInterface $claim): bool
    {
        return $this->claims->has($claim->getName());
    }

    public function hasKey(string $claim): bool
    {
        return $this->offsetExists($claim);
    }

    public function token(): Token
    {
        return Manager::encode($this);
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
    public function offsetExists(mixed $key): bool
    {
        $arrHas = new Has();
        return $arrHas($this->toArray(), $key);
    }

    /**
     * @param mixed $key
     * @return mixed
     */
    public function offsetGet(mixed $key): mixed
    {
        $arrGet = new Get();
        return $arrGet($this->toArray(), $key);
    }

    /**
     * @param $key
     * @param $value
     * @return void
     * @throws PayloadException
     */
    public function offsetSet($key, $value): void
    {
        throw new PayloadException();
    }

    public function offsetUnset($key): void
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

        throw new BadMethodCallException(sprintf(
            'Call to undefined method %s::%s()', static::class, $method
        ));
    }
}