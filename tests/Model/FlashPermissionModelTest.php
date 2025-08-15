<?php declare(strict_types=1);

namespace Model;

use Danilovl\PermissionMiddlewareBundle\Exception\LogicException;
use Danilovl\PermissionMiddlewareBundle\Model\FlashPermissionModel;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FlashPermissionModelTest extends TestCase
{
    #[DataProvider('provideOptionsSuccessCases')]
    public function testOptionsSuccess(array $options): void
    {
        $this->expectNotToPerformAssertions();

        new FlashPermissionModel($options);
    }

    #[DataProvider('provideOptionsFailedCases')]
    public function testOptionsFailed(array $options): void
    {
        $this->expectException(LogicException::class);

        new FlashPermissionModel($options);
    }

    public static function provideOptionsSuccessCases(): Generator
    {
        yield [['type' => 'warning', 'trans' => ['message' => 'Flash message']]];
    }

    public static function provideOptionsFailedCases(): Generator
    {
        yield [['types' => 'warning']];
        yield [['trans' => 'warning']];
        yield [['type' => 'warning', 'translation' => ['message' => 'Flash message']]];
        yield [['type' => 'warning', 'trans' => ['messages' => 'Flash message']]];

    }
}
