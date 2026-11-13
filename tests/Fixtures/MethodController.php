<?php declare(strict_types=1);

namespace Danilovl\PermissionMiddlewareBundle\Tests\Fixtures;

use Danilovl\PermissionMiddlewareBundle\Attribute\PermissionMiddleware;

class MethodController
{
    public function free(): void {}

    #[PermissionMiddleware(class: ['name' => Checker::class, 'method' => 'record'])]
    public function recorded(): void {}

    #[PermissionMiddleware(class: [
        'name' => Checker::class,
        'method' => 'deny',
        'exceptionMessage' => ['message' => 'class.denied', 'messageParameters' => ['%p%' => 1], 'domain' => 'security']
    ])]
    public function classDenied(): void {}

    #[PermissionMiddleware(class: ['name' => Checker::class, 'method' => 'deny'])]
    public function classDeniedWithoutMessage(): void {}

    #[PermissionMiddleware(date: ['to' => '2000-01-01'])]
    public function dateExpired(): void {}

    #[PermissionMiddleware(date: ['from' => '+10 years'])]
    public function dateNotStarted(): void {}

    #[PermissionMiddleware(date: ['from' => '2000-01-01', 'to' => '+10 years'])]
    public function dateActive(): void {}

    #[PermissionMiddleware(redirect: [
        'route' => 'login',
        'parameters' => ['next' => 'redirect'],
        'flash' => ['type' => 'info', 'trans' => ['message' => 'flash.redirect']]
    ])]
    public function redirect(): void {}

    #[PermissionMiddleware(user: [
        'roles' => ['ROLE_SUPER_ADMIN'],
        'redirect' => [
            'route' => 'login',
            'flash' => ['type' => 'warning', 'trans' => ['message' => 'flash.user']]
        ]
    ])]
    public function userRedirect(): void {}

    #[PermissionMiddleware(user: ['roles' => ['ROLE_USER', 'ROLE_ADMIN']])]
    public function userRoles(): void {}

    #[PermissionMiddleware(user: ['roles' => ['ROLE_SUPER_ADMIN'], 'exceptionMessage' => ['message' => 'user.denied']])]
    public function userMissingRole(): void {}

    #[PermissionMiddleware(user: ['userNames' => ['admin']])]
    public function userNameAdmin(): void {}

    #[PermissionMiddleware(user: ['userNames' => ['other']])]
    public function userNameOther(): void {}

    #[PermissionMiddleware(service: ['name' => 'app.checker', 'method' => 'allow'])]
    public function serviceAllowed(): void {}

    #[PermissionMiddleware(service: ['name' => 'app.checker', 'method' => 'deny'])]
    public function serviceDenied(): void {}

    #[PermissionMiddleware(class: ['name' => Checker::class, 'method' => 'deny'], environment: ['prod'])]
    public function prodOnly(): void {}

    #[PermissionMiddleware(class: ['name' => Checker::class, 'method' => 'deny'], environment: ['test'])]
    public function testOnly(): void {}

    #[PermissionMiddleware(class: ['name' => Checker::class, 'method' => 'deny'], afterResponse: true)]
    public function afterResponseDeny(): void {}

    #[PermissionMiddleware(class: ['name' => Checker::class, 'method' => 'deny'])]
    #[PermissionMiddleware(class: ['name' => Checker::class, 'method' => 'record'], afterResponse: true)]
    public function mixed(): void {}

    #[PermissionMiddleware(class: ['name' => Checker::class, 'method' => 'record'], afterResponse: true)]
    #[PermissionMiddleware(service: ['name' => 'app.checker', 'method' => 'allow'], afterResponse: true)]
    public function afterResponseBoth(): void {}

    #[PermissionMiddleware(class: ['name' => Checker::class, 'method' => 'record'], environment: ['prod'], afterResponse: true)]
    public function afterResponseProdOnly(): void {}
}
