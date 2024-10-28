<?php declare(strict_types=1);

namespace Danilovl\PermissionMiddlewareBundle\Model;

use Danilovl\PermissionMiddlewareBundle\Attribute\RequireModelOption;
use Danilovl\PermissionMiddlewareBundle\Interfaces\CheckInterface;
use Danilovl\PermissionMiddlewareBundle\Traits\OptionsCheckTrait;

#[RequireModelOption(optionNames: ['roles', 'userNames'])]
class UserPermissionModel implements CheckInterface
{
    use OptionsCheckTrait;

    public readonly ?array $roles;

    public readonly ?array $userNames;

    public readonly ?TransPermissionModel $exceptionMessage;

    public readonly ?RedirectPermissionModel $redirect;

    public function __construct(array $options)
    {
        $this->checkOptions($options);

        $exceptionMessage = $options['exceptionMessage'] ?? null;
        $redirect = $options['redirect'] ?? null;

        $this->roles = $options['roles'] ?? null;
        $this->userNames = $options['userNames'] ?? null;
        $this->exceptionMessage = $exceptionMessage !== null ? new TransPermissionModel($exceptionMessage) : null;
        $this->redirect = $redirect !== null ? new RedirectPermissionModel($redirect) : null;
    }

    public function checkOptions(array $options): void
    {
        $this->checkOptionsNames($options);
        $this->checkRequiredOptions($options);
    }
}
