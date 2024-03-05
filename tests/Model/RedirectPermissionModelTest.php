<?php declare(strict_types=1);

namespace Model;

use Danilovl\PermissionMiddlewareBundle\Exception\LogicException;
use Danilovl\PermissionMiddlewareBundle\Model\RedirectPermissionModel;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RedirectPermissionModelTest extends TestCase
{
    #[DataProvider('optionsSuccessProvider')]
    public function testOptionsSuccess(array $options): void
    {
        new RedirectPermissionModel($options);

        $this->assertTrue(true);
    }

    #[DataProvider('optionsFailedProvider')]
    public function testOptionsFailed(array $options): void
    {
        $this->expectException(LogicException::class);

        new RedirectPermissionModel($options);
    }

    public static function optionsSuccessProvider(): Generator
    {
        yield [['route' => 'route']];
        yield [['route' => 'route', 'parameters' => ['id' => 1]]];
        yield [['route' => 'route', 'flash' => ['type' => 'success', 'trans' => ['message' => 'Flash message']]]];
    }

    public static function optionsFailedProvider(): Generator
    {
        yield [['routes' => 'warning']];
        yield [['parameterss' => 'warning']];
        yield [['route' => 'route', 'parameterss' => ['id' => 1]]];
    }
}
