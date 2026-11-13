<?php declare(strict_types=1);

namespace Danilovl\PermissionMiddlewareBundle\Tests\Fixtures;

use Danilovl\PermissionMiddlewareBundle\Attribute\PermissionMiddleware;

#[PermissionMiddleware(class: ['name' => Checker::class, 'method' => 'deny', 'redirect' => ['route' => 'login']])]
#[PermissionMiddleware(class: ['name' => Checker::class, 'method' => 'deny'], afterResponse: true)]
class ClassDeniedController
{
    #[PermissionMiddleware(class: ['name' => Checker::class, 'method' => 'record'])]
    #[PermissionMiddleware(class: ['name' => Checker::class, 'method' => 'record'], afterResponse: true)]
    public function index(): void {}
}
