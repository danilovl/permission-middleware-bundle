<?php declare(strict_types=1);

namespace Danilovl\PermissionMiddlewareBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class PermissionMiddlewareExtension extends Extension
{
    private const DIR_CONFIG = '/../Resources/config';

    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . self::DIR_CONFIG));
        $loader->load('services.yaml');
    }
}
