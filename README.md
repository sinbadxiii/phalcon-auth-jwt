# Phalcon JWT Auth 

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
Phalcon: ^4 || ^5

PHP: ^7.2 || ^8.0

## Installation

### Install via composer

Run the following command to pull in the latest version::

`composer require "sinbadxiii/phalcon-auth-jwt"`

### Add service provider

```php
$jwt = new \Sinbadxiii\PhalconAuthJWT\Providers\JWTServiceProvider();
$jwt->register($di);
```

### Configuration

Copy file from `config/jwt.php` in your folder config and merge your config

### Generate secret key

Update the JWT_SECRET value in config jwt.php or your .env file

## How use

### Update your User model

Firstly you need to implement the Sinbadxiii\PhalconAuthJWT\Contracts\JWTSubject contract on your User model, which requires that you implement the 2 methods `getJWTIdentifier()` and `getJWTCustomClaims()`.

The example below:

```php 
<?php

namespace App\Models;

use Phalcon\Mvc\Model;
use Sinbadxiii\PhalconAuthJWT\Contracts\JWTSubject;

class Users extends Model implements JWTSubject
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

### Configure Auth guard

Inside the `config/auth.php` file you will need to make a few changes to configure project to use the jwt guard to power your application authentication.

```php
'defaults' => [
    'guard' => 'api',
    'passwords' => 'users',
],
'guards' => [
//  'web' => [
//      'driver' => 'session',
//      'provider' => 'users',
//   ],
    'api' => [
        'driver' => 'jwt',
        'provider' => 'users',
    ],
'providers' => [
        'users' => [
            'driver' => 'model',
            'model'  => \App\Models\Users::class,
        ],
    ],
],
```

Here we are telling the `api` guard to use the `jwt` driver, and we are setting the api guard as the default.

We can now use [Phalcon Auth](https://github.com/sinbadxiii/phalcon-auth) with JWT guard.

### Add some basic authentication routes

An example of your route file with [Phalcon Foundation Auth](https://github.com/sinbadxiii/phalcon-foundation-auth) package :

```php 
use Sinbadxiii\PhalconFoundationAuth\Routes as AuthRoutes;

$router = $di->getRouter(false);
$router->setDefaultNamespace("App\Controllers");

//...

$router->mount(AuthRoutes::routes());
$router->mount(AuthRoutes::jwt());

$router->handle($_SERVER['REQUEST_URI']);
```

or write:

```php 

    $router = $di->getRouter(false);
    $router->setDefaultNamespace("App\Controllers");

    $routerJwt = new Group();
    $routerJwt->setPrefix("/auth");
  
    $routerJwt->addPost("/login", 'App\Controllers\Auth\Login::login')->setName("login");
    $routerJwt->addPost("/logout", 'App\Controllers\Auth\Login::logout')->setName("logout");
    $routerJwt->addPost("/refresh", 'App\Controllers\Auth\Login::refresh')->setName("refresh");
    $routerJwt->addPost("/me", 'App\Controllers\Auth\Login::me')->setName("me");
    
    $router->mount($routerJwt);

    $router->handle($_SERVER['REQUEST_URI']);
        
```

### Create the Auth Login Controller

```php 
<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use Phalcon\Mvc\Controller;

class LoginController extends Controller
{
    protected bool $authAccess = false;

    public function initialize()
    {
        $this->view->disable();
    }

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

    public function authAccess()
    {
        return $this->authAccess;
    }
}

```

### Attach Middleware

Attach in your dispatcher service provider middleware `JWTAutheticate`

```php 
$di->setShared("dispatcher", function () use ($di) {
    $dispatcher = new Dispatcher();

    $eventsManager = $di->getShared('eventsManager');
    $eventsManager->attach('dispatch', new JWTAutheticate());
    $dispatcher->setEventsManager($eventsManager);

    return $dispatcher;
});
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
$user = User::first();

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

###logout()
Log the user out - which will invalidate the current token and unset the authenticated user.

```php 
$this->auth->logout();

// Pass true to force the token to be blacklisted "forever"
$this->auth->logout(true);
```

###refresh()
Refresh a token, which invalidates the current one

```php 
$newToken = $this->auth->refresh();

// Pass true as the first param to force the token to be blacklisted "forever".
// The second parameter will reset the claims for the new token
$newToken = $this->auth->refresh(true, true);
```

###invalidate()
Invalidate the token (add it to the blacklist)

```php 
$this->auth->invalidate();

// Pass true as the first param to force the token to be blacklisted "forever".
$this->auth->invalidate(true);
```

###tokenById()
Get a token based on a given user's id.

```php 
$token = $this->auth->tokenById(1);
```

###payload()
Get the raw JWT payload

```php 
$payload = $this->auth->payload();

// then you can access the claims directly e.g.
$payload->get('sub'); // = 1
$payload['jti']; // = 'sFF32fsDfs'
$payload('exp') // = 1665544846
$payload->toArray(); // = ['sub' => 1, 'exp' => 1665544846, 'jti' => 'sFF32fsDfs'] etc
```

###validate()
Validate a user's credentials

```php 
if ($this->auth->validate($credentials)) {
    // credentials are valid
}
```

##More advanced usage

###Adding custom claims

```php 
$token = $this->auth->claims(['username' => 'phalconist'])->attempt($credentials);
```

###Set the token explicitly

```php 
$user = $this->auth->setToken('eyJhb...')->user();
```








### License
The MIT License (MIT). Please see [License File](https://github.com/sinbadxiii/phalcon-auth/blob/master/LICENSE) for more information.