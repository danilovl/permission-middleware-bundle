<?php declare(strict_types=1);

namespace Danilovl\PermissionMiddlewareBundle\Model;

use Danilovl\PermissionMiddlewareBundle\Attribute\RequireModelOption;
use Danilovl\PermissionMiddlewareBundle\Interfaces\CheckInterface;
use Danilovl\PermissionMiddlewareBundle\Traits\OptionsCheckTrait;

#[RequireModelOption(['route'])]
class RedirectPermissionModel implements CheckInterface
{
    use OptionsCheckTrait;

    public readonly string $route;

    public readonly array $parameters;

    public readonly ?FlashPermissionModel $flash;

    public function __construct(array $options)
    {
        $this->checkOptions($options);

        $flash = $options['flash'] ?? null;

        $this->route = $options['route'];
        $this->parameters = $options['parameters'] ?? [];
        $this->flash = $flash !== null ? new FlashPermissionModel($flash) : null;
    }

    public function checkOptions(array $options): void
    {
        $this->checkOptionsNames($options);
        $this->checkRequiredOptions($options);
    }
}
