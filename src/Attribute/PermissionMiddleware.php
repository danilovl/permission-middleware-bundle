<?php declare(strict_types=1);

namespace Danilovl\PermissionMiddlewareBundle\Attribute;

use Danilovl\PermissionMiddlewareBundle\Model\{
    UserPermissionModel,
    DatePermissionModel,
    ClassPermissionModel,
    ServicePermissionModel,
    RedirectPermissionModel
};

#[\Attribute]
class PermissionMiddleware
{
    public ?UserPermissionModel $user;
    public ?DatePermissionModel $date;
    public ?RedirectPermissionModel $redirect;
    public ?ClassPermissionModel $class;
    public ?ServicePermissionModel $service;

    public function __construct(array $options)
    {
        $this->user = !empty($options['user']) ? new UserPermissionModel($options['user']) : null;
        $this->date = !empty($options['date']) ? new DatePermissionModel($options['date']) : null;
        $this->redirect = !empty($options['redirect']) ? new RedirectPermissionModel($options['redirect']) : null;
        $this->class = !empty($options['class']) ? new ClassPermissionModel($options['class']) : null;
        $this->service = !empty($options['service']) ? new ServicePermissionModel($options['service']) : null;
    }
}
