<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT;

use Phalcon\Di\Di;
use Sinbadxiii\PhalconAuthJWT\Claims\Custom;
use Sinbadxiii\PhalconAuthJWT\Claims\Factory as ClaimFactory;
use Sinbadxiii\PhalconAuthJWT\Support\Helpers;


class Builder
{
    protected $request;

    protected int $ttl = 30;

    protected bool $lockSubject = true;

    protected int $leeway = 0;

    protected ?int $maxRefreshPeriod = null;

    protected array $requiredClaims = [];

    protected array $defaultClaims = [
        Claims\IssuedAt::NAME,
        Claims\JwtId::NAME,
        Claims\Issuer::NAME,
    ];

    protected array $customValidators = [];

    public function __construct()
    {
        $this->request = Di::getDefault()->getShared("request");
    }

    public function make($claims = []): Payload
    {
        return Factory::make($claims, $this->getOptions());
    }

    public function makeForSubject(JWTSubject $subject, array $claims = []): Payload
    {
        return $this->make(array_merge(
            $this->getDefaultClaims(),
            $this->getClaimsForSubject($subject),
            [Custom::NAME => $subject->getJWTCustomClaims()],
            $claims
        ));
    }

    public function buildRefreshClaims(Payload $payload): array
    {
        return array_merge($payload->toArray(), [
            Claims\JwtId::NAME => ClaimFactory::get(Claims\JwtId::NAME),
            Claims\Expiration::NAME => Helpers::timestamp($payload[Claims\Expiration::NAME])
                ->addMinutes($this->getTTL())
                ->getTimestamp(),
        ]);
    }

    public function getOptions(): Options
    {
        return new Options([
            Options::LEEWAY => $this->leeway,
            Options::REQUIRED_CLAIMS => $this->requiredClaims,
            Options::MAX_REFRESH_PERIOD => $this->maxRefreshPeriod,
            Options::VALIDATORS => $this->customValidators,
        ]);
    }

    protected function getDefaultClaims(): array
    {
        if ($key = array_search(Claims\Issuer::NAME, $this->defaultClaims)) {
            $iss = Helpers::pull($this->defaultClaims, $key);
        }

        return array_merge(
            $this->defaultClaims,
            isset($iss) ? [$this->issClaim()] : [],
            $this->getTTL() !== null ? [$this->expClaim()] : []
        );
    }

    protected function issClaim()
    {
        return ClaimFactory::get(
            Claims\Issuer::NAME,
            $this->request->getHttpHost(),
            $this->getOptions()
        );
    }

    protected function expClaim()
    {
        return ClaimFactory::get(
            Claims\Expiration::NAME,
            Helpers::now()->addMinutes($this->getTTL())->getTimestamp(),
            $this->getOptions()
        );
    }

    protected function getClaimsForSubject(JWTSubject $subject): array
    {
        return array_merge([
            Claims\Subject::NAME => $subject->getJWTIdentifier(),
        ], $this->lockSubject ? [
            Claims\HashedSubject::NAME => $this->hashSubjectModel($subject),
        ] : []);
    }

    public function hashSubjectModel($model): string
    {
        return sha1(is_object($model) ? get_class($model) : $model);
    }

    public function setRequest($request): self
    {
        $this->request = $request;

        return $this;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function lockSubject(bool $lock): self
    {
        $this->lockSubject = $lock;

        return $this;
    }

    public function setTTL(?int $ttl): self
    {
        $this->ttl = $ttl;

        return $this;
    }

    public function getTTL(): ?int
    {
        return $this->ttl;
    }

    public function setDefaultClaims(array $claims = []): self
    {
        $this->defaultClaims = $claims;

        return $this;
    }

    public function setRequiredClaims(array $claims = []): self
    {
        $this->requiredClaims = $claims;

        return $this;
    }

    public function setLeeway(int $leeway): self
    {
        $this->leeway = $leeway;

        return $this;
    }

    public function setMaxRefreshPeriod(?int $period): self
    {
        $this->maxRefreshPeriod = $period;

        return $this;
    }

    public function setCustomValidator(string $key, callable $validator): self
    {
        $this->customValidators[$key] = $validator;

        return $this;
    }
}