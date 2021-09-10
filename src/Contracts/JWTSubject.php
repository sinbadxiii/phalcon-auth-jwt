<?php

namespace Sinbadxiii\PhalconAuthJWT\Contracts;

interface JWTSubject
{
    public function getJWTIdentifier();
    public function getJWTCustomClaims();
}
