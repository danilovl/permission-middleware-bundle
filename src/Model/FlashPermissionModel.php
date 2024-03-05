<?php declare(strict_types=1);

namespace Danilovl\PermissionMiddlewareBundle\Model;

use Danilovl\PermissionMiddlewareBundle\Attribute\RequireModelOption;
use Danilovl\PermissionMiddlewareBundle\Interfaces\CheckInterface;
use Danilovl\PermissionMiddlewareBundle\Traits\OptionsCheckTrait;

#[RequireModelOption(['type' , 'trans'])]
class FlashPermissionModel implements CheckInterface
{
    use OptionsCheckTrait;

    public readonly string $type;
    public readonly TransPermissionModel $trans;

    public function __construct(array $options)
    {
        $this->checkOptions($options);

        $this->type = $options['type'];
        $this->trans = new TransPermissionModel($options['trans']);
    }

    public function checkOptions(array $options): void
    {
        $this->checkOptionsNames($options);
        $this->checkRequiredOptions($options);
    }
}
