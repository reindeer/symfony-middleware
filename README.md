# Symfony middleware

## Introduction

This bundle provides PSR-15-like middlewares in Symfony. Unlike PSR-15 it uses common Symfony Requests and Responses.

## Installation

```bash
composer require tarandro/symfony-middleware
```

## Usage

Each middleware must implement the `Tarandro\SymfonyMiddleware\Contracts\MiddlewareInterface`.
This interface is very similar to `Psr\Http\Server\MiddlewareInterface`
but uses `Symfony\Component\HttpFoundation\Request` and `Symfony\Component\HttpFoundation\Response` instead of
`Psr\Http\Message\RequestInterface` and `Psr\Http\Message\ResponseInterface` respectively.

Some middleware for a route can be applied using options array in route description.

### Protect route with a middleware

Let's create a middleware which authentication checks:

`src/Middleware/AuthMiddleware.php`
```php
<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tarandro\SymfonyMiddleware\Contracts\MiddlewareInterface;
use Tarandro\SymfonyMiddleware\Contracts\RequestHandlerInterface;

class CheckDeposit implements MiddlewareInterface
{
    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        // Check credentials
        
        return $handler->handle($request);
    }
}
```

Now we need to protect the route using our middleware:

`config/routes.yaml`
```yaml
custom.route:
  path: /some/path
  options:
    middleware:
      - \App\Middleware\AuthMiddleware
```

That's all.

You can add as much middlewares for every route as you need. 

**Please note: if you use import routes and set middlewares both in routes and collection only collection middlewares are used.**

`config/imported-routes.yaml`
```yaml
custom.route:
  path: /some/path
  options:
    middleware:
      - \App\Middleware\YetAnotherMiddleware # this middleware will be ignored
```

`config/routes.yaml`
```yaml
collection:
  resource: 'imported-routes.yaml'
  options:
    middleware:
      - \App\Middleware\CustomMiddleware
```

## Examples

### Before middleware to check authentication

```php
<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tarandro\SymfonyMiddleware\Contracts\MiddlewareInterface;
use Tarandro\SymfonyMiddleware\Contracts\RequestHandlerInterface;

class CheckDeposit implements MiddlewareInterface
{
    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        if (!isAuthenticated($request->headers->get('Authorization')) {
            throw new UnauthorizedHttpException();
        }
        
        return $handler->handle($request);
    }
    
    protected function isAuthenticated($token): bool
    {
        return true; // Do some stuff
    }
}
```

### After middleware to send logs to external service

```php
<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tarandro\SymfonyMiddleware\Contracts\MiddlewareInterface;
use Tarandro\SymfonyMiddleware\Contracts\RequestHandlerInterface;

class CheckDeposit implements MiddlewareInterface
{
    public function process(Request $request, RequestHandlerInterface $handler): Response
    {   
        $response = $handler->handle($request);
        
        // Send logs to kibana
        
        return $response
    }
}
```

## License

[The MIT License (MIT)](https://opensource.org/licenses/MIT).
