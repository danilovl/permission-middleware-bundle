<?php declare(strict_types=1);

namespace Model;

use Danilovl\PermissionMiddlewareBundle\Exception\LogicException;
use Danilovl\PermissionMiddlewareBundle\Model\UserPermissionModel;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class UserPermissionModelTest extends TestCase
{
    #[DataProvider('provideOptionsSuccessCases')]
    public function testOptionsSuccess(array $options): void
    {
        $this->expectNotToPerformAssertions();

        new UserPermissionModel($options);
    }

    #[DataProvider('provideOptionsFailedCases')]
    public function testOptionsFailed(array $options): void
    {
        $this->expectException(LogicException::class);

        new UserPermissionModel($options);
    }

    public static function provideOptionsSuccessCases(): Generator
    {
        yield [['roles' => []]];
        yield [['roles' => [], 'userNames' => []]];
        yield [['roles' => [], 'userNames' => []]];
        yield [['roles' => [], 'userNames' => [], 'exceptionMessage' => ['message' => 'Exception message']]];
    }

    public static function provideOptionsFailedCases(): Generator
    {
        yield [['roless' => []]];
        yield [['roles' => [], 'userNamess' => []]];
        yield [['exceptionMessage' => ['message' => 'Exception message']]];
    }
}
