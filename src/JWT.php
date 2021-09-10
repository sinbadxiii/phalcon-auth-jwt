<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT;

use Sinbadxiii\PhalconAuthJWT\Claims\HashedSubject;
use Sinbadxiii\PhalconAuthJWT\Contracts\JWTSubject;
use Sinbadxiii\PhalconAuthJWT\Exceptions\JWTException;
use Sinbadxiii\PhalconAuthJWT\Http\Parser\Parser;
use Sinbadxiii\PhalconAuthJWT\Support\CustomClaims;
use Sinbadxiii\PhalconAuthJWT\Support\ForwardsCalls;

class JWT
{
    use CustomClaims;
    use ForwardsCalls;

    protected array $customClaims = [];


    protected Builder $builder;
    protected JWTManager $manager;
    protected Parser $parser;
    protected ?Token $token = null;

    public function __construct(Builder $builder, JWTManager $manager, Parser $parser)
    {
        $this->builder = $builder;
        $this->manager = $manager;
        $this->parser = $parser;
    }

    public function fromSubject(JWTSubject $subject): Token
    {
        return $this->manager->tokenForSubject($subject, $this->customClaims);
    }

    public function fromUser(JWTSubject $user): Token
    {
        return $this->fromSubject($user);
    }

    public function invalidate(): self
    {
        $this->requireToken();

        $this->manager->invalidate($this->token);

        return $this;
    }

    public function refresh(): Token
    {
        $this->requireToken();

        return $this->manager->refresh($this->token);
    }

    public function checkOrFail(): Payload
    {
        return $this->payload();
    }

    public function check(bool $getPayload = false)
    {
        try {
            $payload = $this->checkOrFail();
        } catch (JWTException $e) {
            return false;
        }

        return $getPayload ? $payload : true;
    }

    public function getToken(bool $fresh = false): ?Token
    {
        if ($this->token === null || $fresh === true) {
            try {
                $this->parseToken();
            } catch (JWTException $e) {
                $this->token = null;
            }
        }

        return $this->token;
    }

    public function parseToken(): self
    {
        if (! $token = $this->parser->parseToken()) {
            throw new JWTException('The token could not be parsed from the request');
        }

        return $this->setToken($token);
    }

    public function payload()
    {
        $this->requireToken();

        return $this->manager->decode($this->token);
    }

    public function getClaim(string $claim)
    {
        return $this->payload()->get($claim);
    }

    public function checkSubjectModel($model, $payload = null): bool
    {
        $payload ??= $this->payload();

        if (! $hash = $payload->get(HashedSubject::NAME)) {
            return true;
        }

        return $this->builder->hashSubjectModel($model) === $hash;
    }

    public function setToken($token): self
    {
        $this->token = $token instanceof Token
            ? $token
            : new Token($token);

        return $this;
    }

    public function unsetToken(): self
    {
        $this->token = null;

        return $this;
    }

    protected function requireToken(): void
    {
        if (! $this->token) {
            throw new JWTException('A token is required');
        }
    }

    public function setRequest($request): self
    {
        $this->builder->setRequest($request);
        $this->parser->setRequest($request);

        return $this;
    }

    public function builder(): Builder
    {
        return $this->builder;
    }

    public function manager()
    {
        return $this->manager;
    }

    public function parser(?string $key = null)
    {
        return $key === null
            ? $this->parser
            : $this->parser->get($key);
    }

    public function blacklist()
    {
        return $this->manager->getBlacklist();
    }

    public function setTTL(?int $ttl): self
    {
        $this->builder->setTTL($ttl);

        return $this;
    }

    public function getTTL(): ?int
    {
        return $this->builder->getTTL();
    }

    public function setSecret(string $secret): self
    {
        $this->manager->getJWTProvider()
            ->setSecret($secret);

        return $this;
    }

    public function setRequiredClaims(array $claims = []): self
    {
        $this->builder->setRequiredClaims($claims);

        return $this;
    }

    public function registerCustomValidator(string $key, callable $validator): self
    {
        $this->builder->setCustomValidator($key, $validator);

        return $this;
    }

    public function __call(string $method, array $parameters)
    {
        return $this->forwardCallTo($this->manager, $method, $parameters);
    }

    public function customClaims(array $customClaims): self
    {
        $this->customClaims = $customClaims;

        return $this;
    }

    public function claims(array $customClaims): self
    {
        return $this->customClaims($customClaims);
    }

    public function getCustomClaims(): array
    {
        return $this->customClaims;
    }
}