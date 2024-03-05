<?php declare(strict_types=1);

namespace Model;

use Danilovl\PermissionMiddlewareBundle\Exception\LogicException;
use Danilovl\PermissionMiddlewareBundle\Model\TransPermissionModel;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TransPermissionModelTest extends TestCase
{
    #[DataProvider('optionsSuccessProvider')]
    public function testOptionsSuccess(array $options): void
    {
        new TransPermissionModel($options);

        $this->assertTrue(true);
    }

    #[DataProvider('optionsFailedProvider')]
    public function testOptionsFailed(array $options): void
    {
        $this->expectException(LogicException::class);

        new TransPermissionModel($options);
    }

    public static function optionsSuccessProvider(): Generator
    {
        yield [['message' => 'message']];
        yield [['message' => 'message', 'messageParameters' => []]];
        yield [['message' => 'message', 'domain' => 'domain']];
        yield [['message' => 'message', 'locale' => 'en']];
    }

    public static function optionsFailedProvider(): Generator
    {
        yield [['messages' => 'message']];
        yield [['message' => 'message', 'messageParameterss' => []]];
        yield [['messageParameters' => []]];
    }
}
