<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Danilovl\PermissionMiddlewareBundle\EventListener\PermissionListener;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set('danilovl.listener.permission_middleware', PermissionListener::class)
        ->autowire()
        ->public()
        ->tag('kernel.event_listener', ['event' => 'kernel.controller', 'method' => 'onKernelController'])
        ->alias(PermissionListener::class, 'danilovl.listener.permission_middleware');
};
