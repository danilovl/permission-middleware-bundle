<?php declare(strict_types=1);

namespace Model;

use Danilovl\PermissionMiddlewareBundle\Exception\LogicException;
use Danilovl\PermissionMiddlewareBundle\Model\DatePermissionModel;
use DateTimeImmutable;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DatePermissionModelTest extends TestCase
{
    #[DataProvider('optionsSuccessProvider')]
    public function testOptionsSuccess(array $options): void
    {
        new DatePermissionModel($options);

        $this->assertTrue(true);
    }

    #[DataProvider('optionsFailedProvider')]
    public function testOptionsFailed(array $options): void
    {
        $this->expectException(LogicException::class);

        new DatePermissionModel($options);
    }

    public static function optionsSuccessProvider(): Generator
    {
        yield [['from' => '2020-01-01']];
        yield [['from' => '2020-01-01', 'exceptionMessage' => ['message' => 'Exception message']]];
        yield [['from' => new DateTimeImmutable('2020-01-01')]];
        yield [['from' => new DateTimeImmutable('now'), 'to' => new DateTimeImmutable('now + 1 day'),]];
    }

    public static function optionsFailedProvider(): Generator
    {
        yield [['from' => new DateTimeImmutable('now'), 'to' => new DateTimeImmutable('now - 1 day'),]];
        yield [['froms' => '2020-01-01']];
        yield [['test' => '2020-01-01', 'exceptionMessage' => ['messages' => 'Exception message']]];
        yield [['from' => '2020-01-01', 'exceptionMessage' => ['from' => 'Exception message']]];
    }
}
