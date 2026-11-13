<?php declare(strict_types=1);

namespace Danilovl\PermissionMiddlewareBundle\Tests\Fixtures;

use Symfony\Contracts\EventDispatcher\Event;

class ServiceChecker
{
    /** @var list<string> */
    public array $calls = [];

    public function allow(Event $event): bool
    {
        $this->calls[] = 'allow';

        return true;
    }

    public function deny(Event $event): bool
    {
        $this->calls[] = 'deny';

        return false;
    }
}
