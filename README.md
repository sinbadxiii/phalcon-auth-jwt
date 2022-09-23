# Phalcon JWT Auth 

Example micro app [sinbadxiii/phalcon-auth-jwt-example](https://github.com/sinbadxiii/phalcon-auth-jwt-example)

Additional JWT guard for the Phalcon authentication library [sinbadxiii/phalcon-auth](https://github.com/sinbadxiii/phalcon-auth)

![Banner](https://github.com/sinbadxiii/images/blob/master/phalcon-auth-jwt/logo.png?raw=true)

<p align="center">
<a href="LICENSE"><img src="https://img.shields.io/badge/license-MIT-brightgreen?style=flat-square" alt="Software License"></img></a>
<a href="https://packagist.org/packages/sinbadxiii/phalcon-auth-jwt"><img src="https://img.shields.io/packagist/dt/sinbadxiii/phalcon-auth-jwt?style=flat-square" alt="Packagist Downloads"></img></a>
<a href="https://github.com/sinbadxiii/phalcon-auth-jwt/releases"><img src="https://img.shields.io/github/release/sinbadxiii/phalcon-auth-jwt?style=flat-square" alt="Latest Version"></img></a>
</p>

## Demo

![Banner](https://github.com/sinbadxiii/images/blob/master/phalcon-auth-jwt/howusage.gif?raw=true)

## Requirements
Phalcon: ^5

PHP: ^7.4 || ^8.1

## Installation

### Install via composer

Run the following command to pull in the latest version::

`composer require "sinbadxiii/phalcon-auth-jwt"`

### Add service provider

```php
use Sinbadxiii\PhalconAuthJWT\Blacklist;
use Sinbadxiii\PhalconAuthJWT\Builder;
use Sinbadxiii\PhalconAuthJWT\Http\Parser\Chains\AuthHeaders;
use Sinbadxiii\PhalconAuthJWT\Http\Parser\Chains\InputSource;
use Sinbadxiii\PhalconAuthJWT\Http\Parser\Chains\QueryString;
use Sinbadxiii\PhalconAuthJWT\Http\Parser\Parser;
use Sinbadxiii\PhalconAuthJWT\JWT;
use Sinbadxiii\PhalconAuthJWT\Manager as JWTManager;


$di->setShared("jwt", function () {

    $configJwt = $this->getConfig()->path('jwt');

    $providerJwt = $configJwt->providers->jwt;

    $builder = new Builder();

    $builder->lockSubject($configJwt->lock_subject)
        ->setTTL($configJwt->ttl)
        ->setRequiredClaims($configJwt->required_claims->toArray())
        ->setLeeway($configJwt->leeway)
        ->setMaxRefreshPeriod($configJwt->max_refresh_period);

    $parser = new Parser($this->getRequest(), [
        new AuthHeaders,
        new QueryString,
        new InputSource,
    ]);

    $providerStorage = $configJwt->providers->storage;

    $blacklist = new Blacklist(new $providerStorage($this->getCache()));

    $blacklist->setGracePeriod($configJwt->blacklist_grace_period);

    $manager = new JWTManager(new $providerJwt(
        $configJwt->secret,
        $configJwt->algo,
        $configJwt->keys->toArray()
    ), $blacklist, $builder);

    $manager->setBlacklistEnabled((bool) $configJwt->blacklist_enabled);

    return new JWT($builder, $manager, $parser);
});
```

### Configuration

Copy file from `config/jwt.php` in your folder config and merge your config

### Generate secret key

Update the `secret` value in config jwt.php or JWT_SECRET value in your .env file. 

*Generate a 32 character secret phrase like here* https://passwordsgenerator.net/
### Update your User model

Firstly you need to implement the Sinbadxiii\PhalconAuthJWT\JWTSubject contract on your User model, which requires that you implement the 2 methods `getJWTIdentifier()` and `getJWTCustomClaims()`.

The example below:

```php 
<?php

namespace App\Models;

use Phalcon\Mvc\Model;
use Sinbadxiii\PhalconAuthJWT\JWTSubject;

class User extends Model implements JWTSubject
{

    //...
    
    public function getJWTIdentifier()
    {
        return $this->id;
    }

    public function getJWTCustomClaims()
    {
        return [
            "email" => $this->email,
            "username" => $this->username
        ];
    }
}

```

### Create auth access, for example "jwt"

```php 
<?php

namespace App\Security\Access;

use Sinbadxiii\PhalconAuth\Access\AbstractAccess;

class Jwt extends AbstractAccess
{
    /**
     * @return bool
     */
    public function allowedIf(): bool
    {
        return $this->auth->parseToken()->check();
    }
}
```

### Set as a guard JWT

```php
$di->setShared("auth", function () {

    $security = $this->getSecurity();

    $adapter     = new \Sinbadxiii\PhalconAuth\Adapter\Model($security);
    $adapter->setModel(App\Models\User::class);

    $guard = new \Sinbadxiii\PhalconAuthJWT\Guard\JWTGuard(
        $adapter,
        $this->getJwt(),
        $this->getRequest(),
        $this->getEventsManager(),
    );

    $manager = new Manager();
    $manager->addGuard("jwt", $guard);
    $manager->setDefaultGuard($guard);

    $manager->setAccess(new \App\Security\Access\Jwt());
    $manager->except("/auth/login");

    return $manager;
});
```

Here we are telling the `api` guard to use the `jwt` driver, and we are setting the api guard as the default.

We can now use [Phalcon Auth](https://github.com/sinbadxiii/phalcon-auth) with JWT guard.

### Add some basic handlers

```php 
    $application = new \Phalcon\Mvc\Micro($di);

    $eventsManager = new Manager();
   
    $application->post(
        "/auth/logout",
        function () {
            $this->auth->logout();

            return ['message' => 'Successfully logged out'];
        }
    );

    $application->post(
        "/auth/refresh",
        function () {
            $token = $this->auth->refresh();

            return $token->toResponse();
        }
    );

    $application->post(
        '/auth/login',
        function () {

            $credentials = [
                'email' => $this->request->getJsonRawBody()->email,
                'password' => $this->request->getJsonRawBody()->password
            ];

            $this->auth->claims(['aud' => [
                $this->request->getURI()
            ]]);

            if (! $token = $this->auth->attempt($credentials)) {
                return ['error' => 'Unauthorized'];
            }

            return $token->toResponse();
        }
    );

    $application->get(
        '/',
        function () {
            return [
                'message' => 'hello, my friend'
            ];
        }
    );
        
```

### Example Auth Login Controller

```php 
<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use Phalcon\Mvc\Controller;

class LoginController extends Controller
{
    public function loginAction()
    {
        $credentials = [
            'email' => $this->request->getJsonRawBody()->email,
            'password' => $this->request->getJsonRawBody()->password
        ];

        $this->auth->claims(['aud' => [
            $this->request->getURI()
        ]]);

        if (! $token = $this->auth->attempt($credentials)) {
            $this->response->setJsonContent(['error' => 'Unauthorized'])->send();
        }

        return $this->respondWithToken($token);
    }

    public function meAction()
    {
        $this->response->setJsonContent($this->auth->user())->send();
    }

    public function logoutAction()
    {
        $this->auth->logout();

        $this->response->setJsonContent(['message' => 'Successfully logged out'])->send();
    }

    public function refreshAction()
    {
         return $this->respondWithToken($this->auth->refresh());
    }

    protected function respondWithToken($token)
    {
        $this->response->setJsonContent($token->toResponse())->send();
    }
}

```

### Attach Middleware

Example code for middleware:

```php 
<?php

namespace App\Middlewares;

use Phalcon\Mvc\Micro\MiddlewareInterface;
use Phalcon\Mvc\Micro;

use function in_array;

class AuthMiddleware implements MiddlewareInterface
{
    public function call(Micro $application)
    {
        $authService = $application->getDI()->get("auth");

        if ($access = $authService->getAccess()) {
            $excepts = $access->getExceptActions();

            $uri = $application->getDI()->get("request")->getURI(true);

            if (!in_array($uri, $excepts)) {
                try {
                     $authService->parseToken()->checkOrFail();
                } catch (\Throwable $t) {
                    $responseService = $application->getDI()->get("response");
                    $responseService->setStatusCode(401, 'Unauthorized');
                    $responseService->setJsonContent(
                        [
                            "error" => "Unauthorized: " . $t->getMessage(),
                            "code" => 401
                        ]
                    );
                    if (!$responseService->isSent()) {
                        $responseService->send();
                    }
                }
            }
        }

        return true;
    }
}
```

and attach:

```php 
$application = new \Phalcon\Mvc\Micro($di);

$eventsManager = new Manager();

$eventsManager->attach('micro', new AuthMiddleware());
$application->before(new AuthMiddleware());
```

You should now be able to POST to the login endpoint (e.g. http://0.0.0.0:8000/auth/login) with some valid credentials and see a response like:

```json
{
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJhdWQiOlsiXC9hdXRoXC9sb2dpbiJdLCJleHAiOjE2MzE2OTkwOTMsImlzcyI6IjAuMC4wLjA6ODAwMCIsImlhdCI6MTYzMTY5NzI5MywianRpIjoiZFNhaGhyeUciLCJzdWIiOiIzIiwiZW1haWwiOiIxMjM0NUAxMjM0NS5ydSIsInVzZXJuYW1lIjoiMTIzNDUgdXNlcm5hbWUifQ.bTyngpVQt86IwtySdRUxPgZH_xk-44hYHTkmiA3BC_0s75TvkuLqTC9WN1jzBIR7Q_H4dWb_ErPR2MlTaw9VQA",
    "token_type": "bearer",
    "expires_in": 1800
}
```

There are a number of ways to send the token via http:

**Authorization header:**

`Authorization Bearer eyJ0eXAiOiJKV1QiLC...`

**Query string param:**

`http://0.0.0.0:8000/me?token=eyJ0eXAiOiJKV1QiLC...`

## Exceptions




## Methods

### Multiple Guards

```php
$credentials = ['email' => 'eaxample@gmail.com', 'password' => '1234'];

$token = $this->auth->guard('api')->attempt($credentials);
```

### attempt()
Attempt to authenticate a user via some credentials.

```php 
// Generate a token for the user if the credentials are valid
$token = $this->auth->attempt($credentials);
```
This will return either a jwt or `null`

### login()
Log a user in and return a jwt for them.

```php 
// Get some user from somewhere
$user = User::findFirst(1);

// Get the token
$token = $this->auth->login($user);
```

### user()
Get the currently authenticated user.

```php 
// Get the currently authenticated user
$user =  $this->auth->user();
```
If the user is not then authenticated, then null will be returned.

### logout()
Log the user out - which will invalidate the current token and unset the authenticated user.

```php 
$this->auth->logout();
```

### refresh()
Refresh a token, which invalidates the current one

```php 
$newToken = $this->auth->refresh();
```

### invalidate()
Invalidate the token (add it to the blacklist)

```php 
$this->auth->invalidate();
```

### tokenById()
Get a token based on a given user's id.

```php 
$token = $this->auth->tokenById(1);
```

### payload()
Get the raw JWT payload

```php 
$payload = $this->auth->payload();

// then you can access the claims directly e.g.
$payload->get('sub'); // = 1
$payload['jti']; // = 'sFF32fsDfs'
$payload('exp') // = 1665544846
$payload->toArray(); // = ['sub' => 1, 'exp' => 1665544846, 'jti' => 'sFF32fsDfs'] etc
```

### validate()
Validate a user's credentials

```php 
if ($this->auth->validate($credentials)) {
    // credentials are valid
}
```

## More advanced usage

### Adding custom claims

```php 
$token = $this->auth->claims(['username' => 'phalconist'])->attempt($credentials);
```

### Set the token explicitly

```php 
$user = $this->auth->setToken('eyJhb...')->user();
```

### Check token
Checking the token for correctness

```php 
$this->auth->parseToken()->checkOrFail()
```

Will return `true` if everything is ok or Exceptions:
- Sinbadxiii\PhalconAuthJWT\Exceptions\TokenExpiredException ('The token has expired')
- Sinbadxiii\PhalconAuthJWT\Exceptions\TokenBlacklistedException ('The token has been blacklisted')
- Sinbadxiii\PhalconAuthJWT\Exceptions\TokenInvalidException









### License
The MIT License (MIT). Please see [License File](https://github.com/sinbadxiii/phalcon-auth/blob/master/LICENSE) for more information.