<?php declare(strict_types=1);

namespace Danilovl\PermissionMiddlewareBundle;

use Danilovl\PermissionMiddlewareBundle\DependencyInjection\PermissionMiddlewareExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class PermissionMiddlewareBundle extends Bundle
{
    public function getContainerExtension(): PermissionMiddlewareExtension
    {
        return new PermissionMiddlewareExtension;
    }
}
