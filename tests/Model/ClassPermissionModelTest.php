<?php declare(strict_types=1);

namespace App\Tests\Model;

use Danilovl\PermissionMiddlewareBundle\Exception\LogicException;
use Danilovl\PermissionMiddlewareBundle\Model\ClassPermissionModel;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ClassPermissionModelTest extends TestCase
{
    #[DataProvider('optionsSuccessProvider')]
    public function testOptionsSuccess(array $options): void
    {
        $this->expectNotToPerformAssertions();

        new ClassPermissionModel($options);
    }

    #[DataProvider('optionsFailedProvider')]
    public function testOptionsFailed(array $options): void
    {
        $this->expectException(LogicException::class);

        new ClassPermissionModel($options);
    }

    public static function optionsSuccessProvider(): Generator
    {
        yield [['name' => 'Class']];
        yield [['name' => 'Class', 'method' => '__invoke']];
        yield [['name' => 'Class', 'method' => '__invoke', 'exceptionMessage' => ['message' => 'Exception message']]];
    }

    public static function optionsFailedProvider(): Generator
    {
        yield [['names' => 'Class']];
        yield [['name' => 'Class', 'methods' => '__invoke']];
        yield [['name' => 'Class', 'method' => '__invoke', 'exceptionMessage' => ['messages' => 'Exception message']]];
    }
}
