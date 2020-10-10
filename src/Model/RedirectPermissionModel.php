<?php declare(strict_types=1);

namespace Danilovl\PermissionMiddlewareBundle\Model;

class RedirectPermissionModel
{
    public ?string $route = null;
    public array $parameters = [];
    public FlashPermissionModel $flash;

    public function __construct(?array $options)
    {
        if (empty($options)) {
            return;
        }

        $this->route = !empty($options['route']) ? $options['route'] : null;
        $this->parameters = !empty($options['parameters']) ? $options['parameters'] : [];
        $this->flash = new FlashPermissionModel($options['flash'] ?? null);
    }
}
