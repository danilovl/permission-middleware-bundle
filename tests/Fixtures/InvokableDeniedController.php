<?php declare(strict_types=1);

namespace Danilovl\PermissionMiddlewareBundle\Tests\Fixtures;

use Danilovl\PermissionMiddlewareBundle\Attribute\PermissionMiddleware;

class InvokableDeniedController
{
    #[PermissionMiddleware(class: ['name' => Checker::class, 'method' => 'deny', 'exceptionMessage' => ['message' => 'invokable.denied']])]
    public function __invoke(): void {}
}
