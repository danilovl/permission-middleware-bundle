<?php declare(strict_types=1);

namespace Danilovl\PermissionMiddlewareBundle\Model;

class FlashPermissionModel
{
    public ?string $type = null;
    public TransPermissionModel $trans;

    public function __construct(?array $options)
    {
        if (empty($options)) {
            return;
        }

        $this->type = !empty($options['type']) ? $options['type'] : null;
        $this->trans = new TransPermissionModel($options['trans'] ?? null);
    }
}
