<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT\Providers;

use DateTimeImmutable;
use Exception;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use ReflectionClass;
use Sinbadxiii\PhalconAuthJWT\Exceptions\JWTException;
use Sinbadxiii\PhalconAuthJWT\Exceptions\TokenInvalidException;

class Lcobucci extends Provider
{
    protected Configuration $configuration;

    /**
     * Constructor.
     */
    public function __construct(
        string $secret,
        string $algo,
        array $keys
    ) {
        parent::__construct($secret, $algo, $keys);
        $this->configuration = $this->getConfiguration($secret);

        $this->configuration->setValidationConstraints(
            new SignedWith($this->configuration->signer(), $this->configuration->verificationKey())
        );
    }

    /**
     * Algorithms that this provider supports.
     *
     * @var array
     */
    protected $algorithms = [
        'HS256' => Signer\Hmac\Sha256::class,
        'HS384' => Signer\Hmac\Sha384::class,
        'HS512' => Signer\Hmac\Sha512::class,
        'RS256' => Signer\Rsa\Sha256::class,
        'RS384' => Signer\Rsa\Sha384::class,
        'RS512' => Signer\Rsa\Sha512::class,
        'ES256' => Signer\Ecdsa\Sha256::class,
        'ES384' => Signer\Ecdsa\Sha384::class,
        'ES512' => Signer\Ecdsa\Sha512::class,
    ];

    public function encode(array $payload): string
    {
        try {
            $token = $this->configuration->builder()
                ->issuedBy($payload['iss'])
                ->permittedFor('my-audience')
                ->identifiedBy($payload['jti'])
                ->issuedAt((new DateTimeImmutable())->setTimestamp($payload['iat']))
                ->relatedTo($payload['sub'])
//                ->canOnlyBeUsedAfter((new DateTimeImmutable())->modify('+1 minute'))
                ->expiresAt((new DateTimeImmutable())->setTimestamp($payload['exp']))
                ->getToken($this->configuration->signer(), $this->configuration->signingKey());

        } catch (Exception $e) {
            throw new JWTException('Could not create token: '.$e->getMessage(), $e->getCode(), $e);
        }

        return $token->toString();
    }

    public function decode(string $token): array
    {
        try {
            $jwtToken = $this->configuration->parser()->parse($token);
        } catch (Exception $e) {
            throw new TokenInvalidException(
                'Could not decode token: '.$e->getMessage(),
                $e->getCode(),
                $e
            );
        }

        $constraints = $this->configuration->validationConstraints();

        if (!$this->configuration->validator()->validate($jwtToken, ...$constraints)) {
            throw new TokenInvalidException('Token Signature could not be verified.');
        }

        $claims = $jwtToken->claims()->all();

        $keys = array_keys($claims);
        $items = array_map(fn ($claim) => ($claim instanceof DateTimeImmutable) ? $claim->getTimestamp() : $claim, $claims, $keys);

        return array_combine($keys, $items);
    }

    protected function getSigner(): Signer
    {
        if (! array_key_exists($this->algo, $this->algorithms)) {
            throw new JWTException('The given algorithm could not be found');
        }

        return new $this->algorithms[$this->algo];
    }

    protected function isAsymmetric(): bool
    {
        $reflect = new ReflectionClass($this->configuration->signer());

        return $reflect->isSubclassOf(Signer\Rsa::class)
            || $reflect->isSubclassOf(Signer\Ecdsa::class);
    }

    protected function getConfiguration(string $secret)
    {
        $configuration = Configuration::forSymmetricSigner(
                   $this->getSigner(),
            InMemory::base64Encoded(base64_encode($secret))
        );

        return $configuration;
    }
}