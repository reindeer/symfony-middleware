<?php

declare(strict_types=1);

namespace Tarandro\SymfonyMiddleware\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Tarandro\SymfonyMiddleware\Contracts\MiddlewareInterface;

class TarandroSymfonyMiddlewareExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @inheritdoc
     */
    public function prepend(ContainerBuilder $container): void
    {
    }

    /**
     * @inheritdoc
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        $container->registerForAutoconfiguration(MiddlewareInterface::class)->addTag('tarandro_middleware');
    }

    public function getAlias(): string
    {
        return Configuration::CONFIG_NAME;
    }

    /**
     * @inheritdoc
     */
    public function getConfiguration(array $config, ContainerBuilder $container): Configuration
    {
        return Configuration::fromTreeBuilder();
    }
}
