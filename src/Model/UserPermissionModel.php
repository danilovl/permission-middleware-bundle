<?php declare(strict_types=1);

namespace Danilovl\PermissionMiddlewareBundle\Model;

class UserPermissionModel
{
    public ?array $roles = null;
    public ?array $userNames = null;
    public TransPermissionModel $exceptionMessage;
    public RedirectPermissionModel $redirect;

    public function __construct(?array $options)
    {
        if (empty($options)) {
            return;
        }

        $this->roles = !empty($options['roles']) ? $options['roles'] : null;
        $this->userNames = !empty($options['userNames']) ? $options['userNames'] : [];
        $this->exceptionMessage = new TransPermissionModel($options['exceptionMessage'] ?? null);
        $this->redirect = new RedirectPermissionModel($options['redirect'] ?? null);
    }
}
