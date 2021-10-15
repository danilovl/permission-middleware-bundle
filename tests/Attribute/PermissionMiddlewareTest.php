<?php declare(strict_types=1);

namespace App\Tests\Attribute;

use Danilovl\PermissionMiddlewareBundle\Attribute\PermissionMiddleware;
use Danilovl\PermissionMiddlewareBundle\Model\{
    UserPermissionModel,
    ClassPermissionModel,
    DatePermissionModel,
    ServicePermissionModel,
    RedirectPermissionModel
};
use Generator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class PermissionMiddlewareTest extends TestCase
{
    /**
     * @dataProvider attributeInstanceProvider
     */
    public function testAttributeInstance(object $object, string $method): void
    {
        $attribute = $this->getAttribute($object, $method);

        $this->assertEquals(PermissionMiddleware::class, get_class($attribute));
    }

    /**
     * @dataProvider separateOptionsProvider
     */
    public function testSeparateOptions(
        object $object,
        string $method,
        string $attributeOption,
        object $model
    ): void {
        $attribute = $this->getAttribute($object, $method);
        $attributeOptionModel = $attribute->$attributeOption;

        $this->assertEquals($model, $attributeOptionModel);
    }

    public function attributeInstanceProvider(): Generator
    {
        yield
        [
            new class {
                #[PermissionMiddleware([
                    'service' => [
                        'name' => 'app.middleware.create_article',
                        'method' => 'handle'
                    ]
                ])]
                public function show(): void
                {
                }
            },
            'show'
        ];
    }

    public function separateOptionsProvider(): Generator
    {
        yield
        [
            new class {
                #[PermissionMiddleware([
                    'service' => [
                        'name' => 'app.middleware.create_article',
                        'method' => 'handle'
                    ]
                ])]
                public function show(): void
                {
                }
            },
            'show',
            'service',
            new ServicePermissionModel([
                'name' => 'app.middleware.create_article',
                'method' => 'handle'
            ])
        ];

        yield
        [
            new class {
                #[PermissionMiddleware([
                    'class' => [
                        'name' => 'App\Middleware\ShowCalendarMiddleware',
                        'method' => 'handle'
                    ]
                ])]
                public function show(): void
                {
                }
            },
            'show',
            'class',
            new ClassPermissionModel([
                'name' => 'App\Middleware\ShowCalendarMiddleware',
                'method' => 'handle'
            ])
        ];

        yield
        [
            new class {
                #[PermissionMiddleware([
                    'user' => [
                        'userNames' => ['admin', 'editor', 'publisher'],
                        'exceptionMessage' => [
                            'message' => 'app.permission_denied'
                        ]
                    ]
                ])]
                public function show(): void
                {
                }
            },
            'show',
            'user',
            new UserPermissionModel([
                'userNames' => ['admin', 'editor', 'publisher'],
                'exceptionMessage' => [
                    'message' => 'app.permission_denied'
                ]
            ])
        ];

        yield
        [
            new class {
                #[PermissionMiddleware([
                    'user' => [
                        'roles' => ['ROLE_ADMIN'],
                        'userNames' => ['admin'],
                        'redirect' => [
                            'route' => 'login'
                        ]
                    ]
                ])]
                public function show(): void
                {
                }
            },
            'show',
            'user',
            new UserPermissionModel([
                'roles' => ['ROLE_ADMIN'],
                'userNames' => ['admin'],
                'redirect' => [
                    'route' => 'login'
                ]
            ])
        ];

        yield
        [
            new class {
                #[PermissionMiddleware([
                    'redirect' => [
                        'route' => 'homepage',
                        'flash' => [
                            'type' => 'success',
                            'trans' => [
                                'message' => 'app.redirect_success'
                            ]
                        ]
                    ]
                ])]
                public function show(): void
                {
                }
            },
            'show',
            'redirect',
            new RedirectPermissionModel([
                'route' => 'homepage',
                'flash' => [
                    'type' => 'success',
                    'trans' => [
                        'message' => 'app.redirect_success'
                    ]
                ]
            ])
        ];

        yield
        [
            new class {
                #[PermissionMiddleware([
                    'date' => [
                        'from' => '01-01-2021',
                        'redirect' => [
                            'route' => 'new_news',
                            'flash' => [
                                'type' => 'warning',
                                'trans' => [
                                    'message' => 'app.new_section_is_open',
                                    'parameters' => ['date' => '01-01-2021'],
                                    'domain' => 'flashes',
                                    'locale' => 'en'
                                ]
                            ]
                        ]
                    ]
                ])]
                public function show(): void
                {
                }
            },
            'show',
            'date',
            new DatePermissionModel([
                'from' => '01-01-2021',
                'redirect' => [
                    'route' => 'new_news',
                    'flash' => [
                        'type' => 'warning',
                        'trans' => [
                            'message' => 'app.new_section_is_open',
                            'parameters' => ['date' => '01-01-2021'],
                            'domain' => 'flashes',
                            'locale' => 'en'
                        ]
                    ]
                ]
            ])
        ];
    }

    private function getAttribute(object $object, string $method): ?PermissionMiddleware
    {
        $attributes = (new ReflectionClass($object))
            ->getMethod($method)
            ->getAttributes(PermissionMiddleware::class);

        return $attributes[0]?->newInstance();
    }
}
