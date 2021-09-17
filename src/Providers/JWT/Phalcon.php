<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT\Providers\JWT;

use Exception;
use Phalcon\Security\JWT\Token\Parser;
use Phalcon\Security\JWT\Signer\Hmac;
use Phalcon\Security\JWT\Validator;
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
use Sinbadxiii\PhalconAuthJWT\Providers\JWT\Phalcon\Builder;

class Phalcon extends Provider
{
    protected Builder $builder;
    protected Parser $parser;
    protected Hmac $signer;

    public function __construct(
        string $secret,
        string $algo,
        array $keys,
        $builder = null,
        $parser = null,
    ) {
        parent::__construct($secret, $algo, $keys);

        $this->signer = $this->getSigner();
        $this->builder = $builder ?? new Builder($this->signer);

        $this->builder->setPassphrase($secret);

        $this->parser = $parser ?? new Parser();
    }

    /**
     * Algorithms that this provider supports.
     *
     * @var array
     */
    protected $algorithms = [
        'HS512' => 'sha512',
        'HS384' => 'sha384',
        'HS256' => 'sha256'
    ];

    public function encode(array $payload): string
    {
        if (!empty($payload[Audience::NAME])) {
            $this->builder->setAudience($payload[Audience::NAME]);
        }

        $this->builder->setExpirationTime($payload[Expiration::NAME])
            ->setIssuer($payload[Issuer::NAME])
            ->setIssuedAt($payload[IssuedAt::NAME])
            ->setId($payload[JwtId::NAME]);

        if (!empty($payload[NotBefore::NAME])) {
            $this->builder->setNotBefore($payload[NotBefore::NAME]);
        }

        $this->builder->setSubject($payload[Subject::NAME]);

        foreach ($payload[Custom::KEY] as $name => $value) {
            $this->builder->withClaim($name, $value);
        }

        $tokenObject = $this->builder->getToken();

        return $tokenObject->getToken();
    }

    public function decode(string $token): array
    {
        try {
            $jwtToken = $this->parser->parse($token);
        } catch (Exception $e) {
            throw new TokenInvalidException(
                'Could not decode token: '.$e->getMessage(),
                $e->getCode(),
                $e
            );
        }

        $validator = new Validator($jwtToken);

        if (!$validator->validateSignature($this->getSigner(), $this->getSecret())) {
            throw new TokenInvalidException('Token Signature could not be verified.');
        }

        $claims = $jwtToken->getClaims();

        return $claims->getPayload();
    }

    protected function getSigner(): Hmac
    {
        if (! isset($this->algorithms[$this->algo])) {
            throw new JWTException('The given algorithm could not be found');
        }

        return new Hmac($this->algorithms[$this->algo]);
    }

    protected function isAsymmetric(): bool
    {
        return false;
    }
}