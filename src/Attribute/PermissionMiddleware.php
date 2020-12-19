<?php declare(strict_types=1);

namespace Danilovl\PermissionMiddlewareBundle\Attribute;

use Danilovl\PermissionMiddlewareBundle\Model\{
	UserPermissionModel,
	DatePermissionModel,
	RedirectPermissionModel
};

#[\Attribute]
class PermissionMiddleware
{
	public UserPermissionModel $user;
	public DatePermissionModel $date;
	public RedirectPermissionModel $redirect;

	public function __construct(array $options)
	{
		$this->user = new UserPermissionModel($options['user'] ?? null);
		$this->date = new DatePermissionModel($options['date'] ?? null);
		$this->redirect = new RedirectPermissionModel($options['redirect'] ?? null);
	}
}
