<?php declare(strict_types=1);

namespace Danilovl\PermissionMiddlewareBundle\Model;

use Danilovl\PermissionMiddlewareBundle\Interfaces\CheckInterface;

class UserPermissionModel implements CheckInterface
{
    public ?array $roles = null;
    public ?array $userNames = null;
    public TransPermissionModel $exceptionMessage;
    public RedirectPermissionModel $redirect;
    public bool $accessDeniedHttpException = true;

    public function __construct(?array $options)
    {
        if (empty($options)) {
            return;
        }

        $this->roles = !empty($options['roles']) ? $options['roles'] : null;
        $this->userNames = !empty($options['userNames']) ? $options['userNames'] : [];
        $this->exceptionMessage = new TransPermissionModel($options['exceptionMessage'] ?? null);
        $this->redirect = new RedirectPermissionModel($options['redirect'] ?? null);
        $this->accessDeniedHttpException = (bool) ($options['accessDeniedHttpException'] ?? true);
    }

    public function canCheck(): bool
    {
        return $this->roles !== null || $this->userNames !== null;
    }
}
