<?php declare(strict_types=1);

namespace Danilovl\PermissionMiddlewareBundle\Model;

use Danilovl\PermissionMiddlewareBundle\Interfaces\CheckInterface;

class ClassPermissionModel implements CheckInterface
{
    public ?string $name = null;
    public ?string $method = null;
    public TransPermissionModel $exceptionMessage;
    public RedirectPermissionModel $redirect;
    public bool $accessDeniedHttpException = true;

    public function __construct(?array $options)
    {
        if (empty($options)) {
            return;
        }

        $this->name = !empty($options['name']) ? $options['name'] : null;
        $this->method = !empty($options['method']) ? $options['method'] : null;
        $this->exceptionMessage = new TransPermissionModel($options['exceptionMessage'] ?? null);
        $this->redirect = new RedirectPermissionModel($options['redirect'] ?? null);
        $this->accessDeniedHttpException = (bool) ($options['accessDeniedHttpException'] ?? true);
    }

    public function canCheck(): bool
    {
        return $this->name !== null && $this->method !== null;
    }
}
