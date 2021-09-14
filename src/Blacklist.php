<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT;

use Sinbadxiii\PhalconAuthJWT\Claims\Expiration;
use Sinbadxiii\PhalconAuthJWT\Claims\JwtId;
use Sinbadxiii\PhalconAuthJWT\Contracts\Providers\Storage;
use Sinbadxiii\PhalconAuthJWT\Support\Helpers;

class Blacklist
{
    /**
     * The storage.
     */
    protected Storage $storage;

    /**
     * The grace period when a token is blacklisted. In seconds.
     */
    protected int $gracePeriod = 0;

    /**
     * The unique key held within the blacklist.
     */
    protected string $key = JwtId::NAME;

    /**
     * The value to store when blacklisting forever.
     *
     * @var string
     */
    const FOREVER = 'FOREVER';

    /**
     * The key to use for the blacklist value.
     *
     * @var string
     */
    const VALID_UNTIL = 'valid_until';

    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Add the token (jti claim) to the blacklist.
     */
    public function add(Payload $payload): void
    {
        if (! $payload->hasKey(Expiration::NAME)) {
            $this->addForever($payload);

            return;
        }

        if (! empty($this->storage->has($this->getKey($payload)))) {
            return;
        }

        $this->storage->add(
            $this->getKey($payload),
            [static::VALID_UNTIL => $this->getGraceTimestamp()],
            $this->getSecondsUntilExpired($payload)
        );
    }

    protected function getMinutesUntilExpired(Payload $payload): int
    {
        $exp = Helpers::timestamp($payload[Expiration::NAME]);

        return Helpers::now()
            ->subMinute()
            ->diffInRealMinutes($exp);
    }

    protected function getSecondsUntilExpired(Payload $payload): int
    {
        $exp = Helpers::timestamp($payload[Expiration::NAME]);

        return Helpers::now()
            ->subSecond()
            ->diffInRealSeconds($exp);
    }

    /**
     * Add the token (jti claim) to the blacklist indefinitely.
     */
    public function addForever(Payload $payload): void
    {
        $this->storage->forever($this->getKey($payload), static::FOREVER);
    }

    /**
     * Determine whether the token has been blacklisted.
     */
    public function has(Payload $payload): bool
    {
        $val = $this->storage->get($this->getKey($payload));

        if ($val === static::FOREVER) {
            return true;
        }

        return ! empty($val) && ! Helpers::is_future($val[static::VALID_UNTIL]);
    }

    /**
     * Remove the token from the blacklist.
     */
    public function remove(Payload $payload): void
    {
        $this->storage->delete($this->getKey($payload));
    }

    /**
     * Remove all tokens from the blacklist.
     */
    public function clear(): void
    {
        $this->storage->flush();
    }

    /**
     * Get the timestamp when the blacklist comes into effect
     * This defaults to immediate (0 seconds).
     */
    protected function getGraceTimestamp(): int
    {
        return Helpers::now()
            ->addSeconds($this->gracePeriod)
            ->getTimestamp();
    }

    /**
     * Set the grace period.
     */
    public function setGracePeriod(int $gracePeriod): self
    {
        $this->gracePeriod = $gracePeriod;

        return $this;
    }

    /**
     * Get the grace period.
     */
    public function getGracePeriod(): int
    {
        return $this->gracePeriod;
    }

    /**
     * Get the unique key held within the blacklist.
     */
    public function getKey(Payload $payload): string
    {
        return (string) $payload[$this->key];
    }

    /**
     * Set the unique key held within the blacklist.
     */
    public function setKey(string $key): self
    {
        $this->key = $key;

        return $this;
    }
}