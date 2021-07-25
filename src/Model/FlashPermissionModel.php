<?php declare(strict_types=1);

namespace Danilovl\PermissionMiddlewareBundle\Model;

use Danilovl\PermissionMiddlewareBundle\Interfaces\CheckInterface;

class FlashPermissionModel implements CheckInterface
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

    public function canCheck(): bool
    {
        return $this->type !== null;
    }
}
