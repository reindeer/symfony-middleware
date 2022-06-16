<?php

namespace Reindeer\SymfonyMiddleware\Contracts;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface MiddlewareInterface
{
    public function process(Request $request, RequestHandlerInterface $handler): Response;
}
