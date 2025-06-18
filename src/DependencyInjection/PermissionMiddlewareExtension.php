<?php declare(strict_types=1);

namespace Danilovl\PermissionMiddlewareBundle\DependencyInjection;

use Danilovl\PermissionMiddlewareBundle\EventListener\{
    ResponseListener,
    ControllerListener
};
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

class PermissionMiddlewareExtension extends Extension
{
    private const string DIR_CONFIG = '/../Resources/config';

    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration;
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . self::DIR_CONFIG));
        $loader->load('services.yaml');

        $kernelControllerPriority = $config['kernel_controller_priority'];
        if (!empty($kernelControllerPriority)) {
            ControllerListener::$priority = $kernelControllerPriority;
        }

        $kernelResponsePriority = $config['kernel_response_priority'];
        if (!empty($kernelResponsePriority)) {
            ResponseListener::$priority = $kernelResponsePriority;
        }
    }

    public function getAlias(): string
    {
        return Configuration::ALIAS;
    }
}
