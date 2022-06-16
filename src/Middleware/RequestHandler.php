<?php

namespace Reindeer\SymfonyMiddleware\Middleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Reindeer\SymfonyMiddleware\Contracts\RequestHandlerInterface;

class RequestHandler implements RequestHandlerInterface
{
    public function __construct(
        protected \Closure $handler,
    ) {
    }

    public function handle(Request $request): Response
    {
        return ($this->handler)($request);
    }
}
