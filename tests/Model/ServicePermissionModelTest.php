<?php declare(strict_types=1);

namespace Model;

use Danilovl\PermissionMiddlewareBundle\Exception\LogicException;
use Danilovl\PermissionMiddlewareBundle\Model\ServicePermissionModel;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ServicePermissionModelTest extends TestCase
{
    #[DataProvider('provideOptionsSuccessCases')]
    public function testOptionsSuccess(array $options): void
    {
        $this->expectNotToPerformAssertions();

        new ServicePermissionModel($options);
    }

    #[DataProvider('provideOptionsFailedCases')]
    public function testOptionsFailed(array $options): void
    {
        $this->expectException(LogicException::class);

        new ServicePermissionModel($options);
    }

    public static function provideOptionsSuccessCases(): Generator
    {
        yield [['name' => 'service']];
        yield [['name' => 'service', 'method' => '__invoke']];
        yield [['name' => 'service', 'method' => '__invoke', 'exceptionMessage' => ['message' => 'Exception message']]];
    }

    public static function provideOptionsFailedCases(): Generator
    {
        yield [['names' => 'service']];
        yield [['name' => 'service', 'methods' => '__invoke']];
        yield [['name' => 'service', 'method' => '__invoke', 'exceptionMessage' => ['messages' => 'Exception message']]];
    }
}
