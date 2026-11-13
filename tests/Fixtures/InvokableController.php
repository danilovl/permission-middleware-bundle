<?php declare(strict_types=1);

namespace Danilovl\PermissionMiddlewareBundle\Tests\Fixtures;

use Danilovl\PermissionMiddlewareBundle\Attribute\PermissionMiddleware;

#[PermissionMiddleware(class: ['name' => Checker::class, 'method' => 'allow'])]
class InvokableController
{
    #[PermissionMiddleware(class: ['name' => Checker::class, 'method' => 'record'])]
    #[PermissionMiddleware(class: ['name' => Checker::class, 'method' => 'record'], afterResponse: true)]
    public function __invoke(): void {}
}
