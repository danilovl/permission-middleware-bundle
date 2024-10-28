<?php declare(strict_types=1);

namespace Danilovl\PermissionMiddlewareBundle\Model;

use Danilovl\PermissionMiddlewareBundle\Attribute\RequireModelOption;
use Danilovl\PermissionMiddlewareBundle\Interfaces\CheckInterface;

#[RequireModelOption(['name'])]
class ClassPermissionModel extends ServicePermissionModel implements CheckInterface {}
