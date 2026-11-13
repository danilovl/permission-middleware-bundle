<?php declare(strict_types=1);

namespace Model;

use Danilovl\PermissionMiddlewareBundle\Exception\LogicException;
use Danilovl\PermissionMiddlewareBundle\Model\TransPermissionModel;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TransPermissionModelTest extends TestCase
{
    #[DataProvider('provideOptionsSuccessCases')]
    public function testOptionsSuccess(array $options): void
    {
        $this->expectNotToPerformAssertions();

        new TransPermissionModel($options);
    }

    #[DataProvider('provideOptionsFailedCases')]
    public function testOptionsFailed(array $options): void
    {
        $this->expectException(LogicException::class);

        new TransPermissionModel($options);
    }

    #[DataProvider('provideGetArgumentsCases')]
    public function testGetArguments(array $options, array $expected): void
    {
        $model = new TransPermissionModel($options);

        $this->assertSame($expected, $model->getArguments());
    }

    public static function provideOptionsSuccessCases(): Generator
    {
        yield [['message' => 'message']];
        yield [['message' => 'message', 'messageParameters' => []]];
        yield [['message' => 'message', 'domain' => 'domain']];
        yield [['message' => 'message', 'locale' => 'en']];
    }

    public static function provideOptionsFailedCases(): Generator
    {
        yield [['messages' => 'message']];
        yield [['message' => 'message', 'messageParameterss' => []]];
        yield [['messageParameters' => []]];
    }

    public static function provideGetArgumentsCases(): Generator
    {
        yield [['message' => 'message'], ['message', [], null, null]];
        yield [['message' => 'message', 'messageParameters' => ['%a%' => 1]], ['message', ['%a%' => 1], null, null]];
        yield [['message' => 'message', 'domain' => 'domain'], ['message', [], 'domain', null]];
        yield [['message' => 'message', 'locale' => 'en'], ['message', [], null, 'en']];
        yield [['message' => 'message', 'domain' => 'domain', 'locale' => 'en'], ['message', [], 'domain', 'en']];
        yield [['message' => 'message', 'domain' => '', 'locale' => ''], ['message', [], null, null]];
    }
}
