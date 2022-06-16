<?php

namespace Reindeer\SymfonyMiddleware\EventSubscriber;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Reindeer\SymfonyMiddleware\Contracts\MiddlewareInterface;
use Reindeer\SymfonyMiddleware\Middleware\RequestHandler;

class MiddlewareSubscriber implements EventSubscriberInterface
{
    protected static array $options = [];

    protected static array $attributes = [];

    /** @param MiddlewareInterface[] */
    protected array $middlewares = [];

    public function __construct(
        protected RouterInterface $router,
        iterable $middlewareList,
    ) {
        foreach ($middlewareList as $key => $middleware) {
            if (is_numeric($key)) {
                $key = $middleware::class;
            }
            $this->middlewares[$key] = $middleware;
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER_ARGUMENTS => 'onControllerArguments',
        ];
    }

    public function onControllerArguments(ControllerArgumentsEvent $event): void
    {
        $routeMiddlewares = array_map(
            static fn (string $middlewareName) => $this->getMiddlewareByName($middlewareName),
            $this->getRouteMiddlewares($event->getRequest()),
        );

        if ($routeMiddlewares) {
            $controller = $event->getController();
            $initialArguments = $event->getArguments();

            $attributes = $this->getControllerArguments($event->getRequest()->get('_controller'));
            $getRequestArguments = static fn (Request $request) => array_map(
                static fn ($attribute) => ($argument = $request->attributes->get($attribute)) instanceof Request ? $request : $argument,
                $attributes,
            );

            $chain = array_reduce(
                array_reverse($routeMiddlewares),
                static fn (\Closure $chain, MiddlewareInterface $middleware): \Closure => static fn (Request $request) => $middleware->process($request, new RequestHandler($chain)),
                static fn (Request $request) => $controller(...($request === $event->getRequest() ? $initialArguments : $getRequestArguments($request))),
            );

            $event->setController(static fn () => $chain($event->getRequest()));
        }
    }

    protected function getMiddlewareByName(string $name): MiddlewareInterface
    {
        return $this->middlewares[$name] ?? throw new \RuntimeException("Middleware $name is not found");
    }

    /** @return string[] */
    protected function getRouteMiddlewares(Request $request): array
    {
        $options = $this->fetchOptions($request);

        if (!$options || !array_key_exists('middleware', $options)) {
            return [];
        }

        if (!is_array($options['middleware'])) {
            throw new InvalidConfigurationException('Array expected but something else provided in option.middleware');
        }

        return $options['middleware'];
    }

    protected function fetchOptions(Request $request): ?array
    {
        if ($routeName = $request->attributes->get('_route')) {
            return static::$options[$routeName] ??= $this->router->getRouteCollection()->get($routeName)?->getOptions();
        }
        return null;
    }

    protected function getControllerArguments($method): array
    {
        return self::$attributes[$method] ??= $this->getArgumentsForController($method);
    }

    protected function getArgumentsForController($method): array
    {
        $reflection = new \ReflectionMethod($method);
        return array_map(
            static fn (\ReflectionParameter $parameter) => $parameter->getName(),
            $reflection->getParameters(),
        );
    }
}
