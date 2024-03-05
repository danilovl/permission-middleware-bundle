<?php declare(strict_types=1);

namespace Model;

use Danilovl\PermissionMiddlewareBundle\Exception\LogicException;
use Danilovl\PermissionMiddlewareBundle\Model\ServicePermissionModel;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ServicePermissionModelTest extends TestCase
{
    #[DataProvider('optionsSuccessProvider')]
    public function testOptionsSuccess(array $options): void
    {
        new ServicePermissionModel($options);

        $this->assertTrue(true);
    }

    #[DataProvider('optionsFailedProvider')]
    public function testOptionsFailed(array $options): void
    {
        $this->expectException(LogicException::class);

        new ServicePermissionModel($options);
    }

    public static function optionsSuccessProvider(): Generator
    {
        yield [['name' => 'service']];
        yield [['name' => 'service', 'method' => '__invoke']];
        yield [['name' => 'service', 'method' => '__invoke', 'exceptionMessage' => ['message' => 'Exception message']]];
    }

    public static function optionsFailedProvider(): Generator
    {
        yield [['names' => 'service']];
        yield [['name' => 'service', 'methods' => '__invoke']];
        yield [['name' => 'service', 'method' => '__invoke', 'exceptionMessage' => ['messages' => 'Exception message']]];
    }
}
