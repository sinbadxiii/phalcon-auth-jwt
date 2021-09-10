<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT\Providers;

use Exception;
use Phalcon\Security\JWT\Builder;
use Phalcon\Security\JWT\Token\Parser;
use Phalcon\Security\JWT\Signer\Hmac;
use Phalcon\Security\JWT\Validator;
use Sinbadxiii\PhalconAuthJWT\Exceptions\JWTException;
use Sinbadxiii\PhalconAuthJWT\Exceptions\TokenInvalidException;

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
        //$notBefore  = strtotime('-1 day');

        $tokenObject = $this->builder
            ->setAudience('my-audience')
            ->setExpirationTime($payload['exp'])
            ->setIssuer($payload['iss'])
            ->setIssuedAt($payload['iat'])
            ->setId($payload['jti'])
          //  ->setNotBefore($notBefore)
            ->setSubject($payload['sub'])
            ->getToken();

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