<?php declare(strict_types=1);

namespace Danilovl\PermissionMiddlewareBundle\Attribute;

use Attribute;
use Danilovl\PermissionMiddlewareBundle\Exception\InvalidArgumentException;
use Danilovl\PermissionMiddlewareBundle\Model\{
    UserPermissionModel,
    DatePermissionModel,
    ClassPermissionModel,
    ServicePermissionModel,
    RedirectPermissionModel
};

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_ALL)]
class PermissionMiddleware
{
    public readonly ?UserPermissionModel $user;
    public readonly ?DatePermissionModel $date;
    public readonly ?RedirectPermissionModel $redirect;
    public readonly ?ClassPermissionModel $class;
    public readonly ?ServicePermissionModel $service;

    public function __construct(
        array $user = null,
        array $date = null,
        array $redirect = null,
        array $class = null,
        array $service = null
    ) {
        $this->checkArguments(get_defined_vars());

        $this->user = $user !== null ? new UserPermissionModel($user) : null;
        $this->date = $date !== null ? new DatePermissionModel($date) : null;
        $this->redirect = $redirect !== null ? new RedirectPermissionModel($redirect) : null;
        $this->class = $class !== null ? new ClassPermissionModel($class) : null;
        $this->service = $service !== null ? new ServicePermissionModel($service) : null;
    }

    private function checkArguments(array $arguments): void
    {
        foreach ($arguments as $argumentName => $argumentValue) {
            if ($argumentValue !== null && empty($argumentValue)) {
                throw new InvalidArgumentException(sprintf('Argument "%s" is not null but empty.', $argumentName));
            }
        }
    }
}
