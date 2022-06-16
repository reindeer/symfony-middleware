<?php

declare(strict_types=1);

namespace Tarandro\SymfonyMiddleware\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public const CONFIG_NAME = 'tarandro_symfony_middleware';

    public function __construct(
        protected TreeBuilder $builder,
    ) {
    }

    public function getConfigTreeBuilder(): TreeBuilder
    {
        return $this->builder;
    }

    public static function fromTreeBuilder(): self
    {
        $treeBuilderClass = TreeBuilder::class;

        return new self(new $treeBuilderClass(self::CONFIG_NAME));
    }
}
