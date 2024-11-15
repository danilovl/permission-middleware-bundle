<?php declare(strict_types=1);

namespace Model;

use Danilovl\PermissionMiddlewareBundle\Exception\LogicException;
use Danilovl\PermissionMiddlewareBundle\Model\UserPermissionModel;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class UserPermissionModelTest extends TestCase
{
    #[DataProvider('optionsSuccessProvider')]
    public function testOptionsSuccess(array $options): void
    {
        $this->expectNotToPerformAssertions();

        new UserPermissionModel($options);
    }

    #[DataProvider('optionsFailedProvider')]
    public function testOptionsFailed(array $options): void
    {
        $this->expectException(LogicException::class);

        new UserPermissionModel($options);
    }

    public static function optionsSuccessProvider(): Generator
    {
        yield [['roles' => []]];
        yield [['roles' => [], 'userNames' => []]];
        yield [['roles' => [], 'userNames' => []]];
        yield [['roles' => [], 'userNames' => [], 'exceptionMessage' => ['message' => 'Exception message']]];
    }

    public static function optionsFailedProvider(): Generator
    {
        yield [['roless' => []]];
        yield [['roles' => [], 'userNamess' => []]];
        yield [['exceptionMessage' => ['message' => 'Exception message']]];
    }
}
