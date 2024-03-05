<?php declare(strict_types=1);

namespace Danilovl\PermissionMiddlewareBundle\Interfaces;

interface CheckInterface
{
    public function checkOptions(array $options): void;
}
