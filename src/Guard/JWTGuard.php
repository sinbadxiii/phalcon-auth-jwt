<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT\Guard;

use Phalcon\Events\EventsAwareInterface;
use Phalcon\Events\ManagerInterface;
use Phalcon\Http\Request;
use Sinbadxiii\PhalconAuth\Adapter\AdapterInterface;
use Sinbadxiii\PhalconAuth\Guard\GuardHelper;
use Sinbadxiii\PhalconAuth\Guard\GuardInterface;
use Sinbadxiii\PhalconAuthJWT\Claims\Subject;
use Sinbadxiii\PhalconAuthJWT\JWTSubject;
use Sinbadxiii\PhalconAuthJWT\Exceptions\JWTException;
use Sinbadxiii\PhalconAuthJWT\Http\TokenResponse;
use Sinbadxiii\PhalconAuthJWT\JWT;
use Sinbadxiii\PhalconAuthJWT\Payload;
use Sinbadxiii\PhalconAuthJWT\Token;
use Phalcon\Support\Helper\Str\StartsWith;

/**
 * Class JWTGuard
 * @package Sinbadxiii\PhalconAuth\Guards
 */
class JWTGuard implements GuardInterface, EventsAwareInterface
{
    use GuardHelper;

    protected JWT $jwt;
    protected $lastAttempted;
    protected ManagerInterface $eventsManager;
    protected Request $request;
    protected AdapterInterface $adapter;
    protected bool $useResponsable = true;

    public function __construct(AdapterInterface $adapter, JWT $jwt, Request $request, ManagerInterface $eventsManager)
    {
        $this->adapter       = $adapter;
        $this->jwt           = $jwt;
        $this->request       = $request;
        $this->eventsManager = $eventsManager;
    }

    public function attempt(array $credentials = [], $login = true)
    {
        $this->lastAttempted = $user = $this->adapter->findFirstByCredentials($credentials);

//        $this->event(new JWTAttempt());

        if ($this->hasValidCredentials($user, $credentials)) {
            return $login ? $this->login($user) : true;
        }

        return false;
    }

    protected function hasValidCredentials($user, $credentials)
    {
        return !is_null($user) && $this->adapter->validateCredentials($user, $credentials);
    }

    public function login(JWTSubject $user)
    {
//        $this->event(new BeforeLogin());

        $token = $this->jwt->fromUser($user);

         $this->setToken($token)->setUser($user);

//        $this->event(new AfterLogin());

        return $this->tokenResponse($token);
    }

    public function user()
    {
        if (!is_null($this->user)) {
            return $this->user;
        }

        if (($payload = $this->getPayload()) && $this->validateSubject($payload)) {
            return $this->user = $this->adapter->findFirstById($payload[Subject::NAME]);
        }

        return $this->user;
    }

    public function validate(array $credentials = []): bool
    {
        return (bool) $this->attempt($credentials, false);
    }

    public function logout()
    {
        $this->requireToken()->invalidate();

//        $this->event(new JWTLogout());

        $this->user = null;
        $this->jwt->unsetToken();
    }

    public function invalidate($forceForever = false)
    {
        return $this->requireToken()->invalidate($forceForever);
    }

    public function getTokenForRequest()
    {
        if (empty(false)) {
            $token = $this->bearerToken();
        }

        return $token;
    }

    private function bearerToken()
    {
        $header = $this->request->getHeader('Authorization');

        $startsWith = new StartsWith();

        if ($startsWith($header, 'Bearer ')) {
            return mb_substr($header, 7, null, 'UTF-8');
        }
    }

    public function setToken($token): self
    {
        $this->jwt->setToken($token);

        return $this;
    }

    public function getLastAttempted()
    {
        return $this->lastAttempted;
    }

    protected function tokenResponse(Token $token)
    {
        return $this->useResponsable
            ? new TokenResponse($token, $this->jwt->getTTL())
            : $token;
    }

    protected function requireToken(): JWT
    {
        if (! $this->jwt->setRequest($this->getRequest())->getToken()) {
            throw new JWTException('Token could not be parsed from the request.');
        }

        return $this->jwt;
    }

    public function refresh()
    {
        $token = $this->requireToken()->refresh();

//        $this->event(
//            new JWTRefresh()
//        );

        return $this->tokenResponse($token);
    }

    public function setTTL($ttl)
    {
        $this->jwt->factory()->setTTL($ttl);

        return $this;
    }

    public function __call(string $method, array $parameters)
    {
        return $this->jwt->{$method}(...$parameters);
    }

    public function getRequest()
    {
        return $this->request;
    }

    protected function validateSubject(?Payload $payload = null): bool
    {
        if (! method_exists($this->adapter, 'getModel')) {
            return true;
        }

        return $this->jwt->checkSubjectModel($this->adapter->getModel());
    }

    public function payload(): Payload
    {
        return $this->requireToken()->payload();
    }

    protected function getPayload(): ?Payload
    {
        if ($this->jwt->setRequest($this->request)->getToken() === null) {
            return null;
        }

        return $this->jwt->check(true) ?: null;
    }

    public function claims(array $claims)
    {
        $this->jwt->claims($claims);

        return $this;
    }

    public function onceUsingId($id)
    {
        if ($user = $this->adapter->findFirstById($id)) {
            $this->setUser($user);

            return true;
        }

        return false;
    }

    public function byId($id)
    {
        return $this->onceUsingId($id);
    }

    public function once(array $credentials = [])
    {
        if ($this->validate($credentials)) {
            $this->setUser($this->lastAttempted);

            return true;
        }

        return false;
    }

    public function tokenById($id)
    {
        if ($user = $this->adapter->findFirstById($id)) {
            return $this->jwt->fromUser($user);
        }
    }

    /**
     * @return ManagerInterface|null
     */
    public function getEventsManager(): ?ManagerInterface
    {
        return $this->eventsManager;
    }

    /**
     * @param ManagerInterface $eventsManager
     * @return void
     */
    public function setEventsManager(ManagerInterface $eventsManager): void
    {
        $this->eventsManager = $eventsManager;
    }
}