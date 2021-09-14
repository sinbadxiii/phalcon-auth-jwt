<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT;

use Sinbadxiii\PhalconAuthJWT\Contracts\JWTSubject;
use Sinbadxiii\PhalconAuthJWT\Exceptions\JWTException;
use Sinbadxiii\PhalconAuthJWT\Exceptions\TokenBlacklistedException;
use Sinbadxiii\PhalconAuthJWT\Support\CustomClaims;

/**
 * Class JWTManager
 * @package Sinbadxiii\PhalconAuthJWT
 */
class JWTManager
{
    use CustomClaims;

    protected $provider;

    protected $blacklist;

    protected $builder;

    protected bool $blacklistEnabled = true;

    public function __construct($provider, $blacklist, $builder)
    {
        $this->provider = $provider;
        $this->blacklist = $blacklist;
        $this->builder = $builder;
    }

    public function encode($payload): Token
    {
        return $this->provider->token($payload);
    }

    public function decode(Token $token, bool $checkBlacklist = true)
    {
        $payload = $this->provider->payload($token, $this->builder->getOptions());
//var_dump($this->blacklist->has($payload));exit;
        if ($checkBlacklist && $this->blacklistEnabled && $this->blacklist->has($payload)) {
            throw new TokenBlacklistedException();
        }

        return $payload;
    }

    public function refresh(Token $token): Token
    {
        $claims = $this->builder->buildRefreshClaims($this->decode($token));

        if ($this->blacklistEnabled) {
            $this->invalidate($token);
        }

        return $this->encode($this->builder->make($claims));
    }

    public function invalidate(Token $token): void
    {
        if (! $this->blacklistEnabled) {
            throw new JWTException('You must have the blacklist enabled to invalidate a token.');
        }

        $this->blacklist->add($this->decode($token, false));
    }

    public function tokenForSubject(JWTSubject $subject, array $claims = []): Token
    {
        $payload = $this->builder->makeForSubject($subject, $claims);

        return $this->encode($payload);
    }

    public function getJWTProvider()
    {
        return $this->provider;
    }

    public function getBlacklist()
    {
        return $this->blacklist;
    }

    public function builder()
    {
        return $this->builder;
    }

    public function setBlacklistEnabled(bool $enabled): self
    {
        $this->blacklistEnabled = $enabled;

        return $this;
    }
}