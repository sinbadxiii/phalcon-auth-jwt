<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT\Providers\JWT;

use DateTimeImmutable;
use Exception;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use ReflectionClass;
use Sinbadxiii\PhalconAuthJWT\Claims\Audience;
use Sinbadxiii\PhalconAuthJWT\Claims\Custom;
use Sinbadxiii\PhalconAuthJWT\Claims\Expiration;
use Sinbadxiii\PhalconAuthJWT\Claims\IssuedAt;
use Sinbadxiii\PhalconAuthJWT\Claims\Issuer;
use Sinbadxiii\PhalconAuthJWT\Claims\JwtId;
use Sinbadxiii\PhalconAuthJWT\Claims\NotBefore;
use Sinbadxiii\PhalconAuthJWT\Claims\Subject;
use Sinbadxiii\PhalconAuthJWT\Exceptions\JWTException;
use Sinbadxiii\PhalconAuthJWT\Exceptions\TokenInvalidException;

class Lcobucci extends AbstractProvider
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
            $builder = $this->configuration->builder()
                ->issuedBy($payload[Issuer::NAME]);

            if (!empty($payload[Audience::NAME])) {
                $builder->permittedFor($payload[Audience::NAME]);
            }

            $builder->identifiedBy($payload[JwtId::NAME])
                ->issuedAt(
                    (new DateTimeImmutable())->setTimestamp($payload[IssuedAt::NAME])
                )
                ->relatedTo($payload[Subject::NAME]);

            if (!empty($payload[NotBefore::NAME])) {
                $builder->canOnlyBeUsedAfter(
                    (new DateTimeImmutable())->setTimestamp($payload[NotBefore::NAME])
                );
            }

            $builder->expiresAt(
                (new DateTimeImmutable())->setTimestamp($payload[Expiration::NAME])
            );

            foreach ($payload[Custom::NAME] as $name => $value) {
                $builder->withClaim($name, $value);
            }

            $token = $builder->getToken($this->configuration->signer(), $this->configuration->signingKey());

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