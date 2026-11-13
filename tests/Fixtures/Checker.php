<?php declare(strict_types=1);

namespace Danilovl\PermissionMiddlewareBundle\Tests\Fixtures;

use Symfony\Contracts\EventDispatcher\Event;

class Checker
{
    /** @var list<array{string, class-string}> */
    public static array $calls = [];

    public static function allow(Event $event): bool
    {
        self::$calls[] = ['allow', $event::class];

        return true;
    }

    public static function deny(Event $event): bool
    {
        self::$calls[] = ['deny', $event::class];

        return false;
    }

    public static function record(Event $event): bool
    {
        self::$calls[] = ['record', $event::class];

        return true;
    }

    /**
     * @return list<string>
     */
    public static function names(): array
    {
        return array_map(static fn (array $call): string => $call[0], self::$calls);
    }

    public static function reset(): void
    {
        self::$calls = [];
    }
}
