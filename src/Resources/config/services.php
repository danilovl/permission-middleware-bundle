<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Danilovl\PermissionMiddlewareBundle\EventListener\{
    ResponseListener,
    ControllerListener
};

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set(ControllerListener::class, ControllerListener::class)
        ->public()
        ->autowire()
        ->autoconfigure()
        ->arg('$container', service('service_container'))
        ->arg('$environment', env('kernel.environment'));

    $container->services()
        ->set(ResponseListener::class, ResponseListener::class)
        ->public()
        ->autowire()
        ->autoconfigure()
        ->arg('$container', service('service_container'))
        ->arg('$environment', env('kernel.environment'));
};
