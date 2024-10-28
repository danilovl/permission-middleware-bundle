<?php declare(strict_types=1);

namespace Danilovl\PermissionMiddlewareBundle\EventListener;

use Danilovl\PermissionMiddlewareBundle\Attribute\PermissionMiddleware;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ResponseListener implements EventSubscriberInterface
{
    protected bool $checkPermissionMethod = true;

    protected ResponseEvent $responseEvent;

    public function __construct(
        protected readonly ContainerInterface $container,
        protected readonly string $environment
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse'
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $this->responseEvent = $event;

        /** @var string|array $controller */
        $controller = $event->getRequest()->attributes->get('_controller', '');

        if (is_string($controller)) {
            $controller = explode('::', $controller);
        }

        if (!is_array($controller) || count($controller) !== 2) {
            return;
        }

        try {
            new ReflectionClass($controller[0] ?? '');
        } catch (ReflectionException) {
            return;
        }

        $this->checkPermissionClass($controller);
        if ($this->checkPermissionMethod) {
            $this->checkPermissionMethod($controller);
        }
    }

    protected function checkPermissionClass(array $controllers): void
    {
        [$controller] = $controllers;

        $attributes = (new ReflectionClass($controller))->getAttributes(PermissionMiddleware::class);
        foreach ($attributes as $attribute) {
            /** @var PermissionMiddleware $attributeInstance */
            $attributeInstance = $attribute->newInstance();
            if (!$attributeInstance->afterResponse) {
                continue;
            }

            $environments = $attributeInstance->environment;
            if ($environments !== null && !in_array($this->environment, $environments)) {
                continue;
            }

            $this->checkPermissionMethod = !($this->checkPermissionMethod === false) && $this->checkPermissions($attribute->newInstance());
        }
    }

    protected function checkPermissionMethod(array $controllers): void
    {
        [$controller, $methodName] = $controllers;

        $attributes = (new ReflectionClass($controller))->getMethod($methodName)->getAttributes(PermissionMiddleware::class);
        foreach ($attributes as $attribute) {
            /** @var PermissionMiddleware $attributeInstance */
            $attributeInstance = $attribute->newInstance();
            if (!$attributeInstance->afterResponse) {
                continue;
            }

            $environments = $attributeInstance->environment;
            if ($environments !== null && !in_array($this->environment, $environments)) {
                continue;
            }

            $this->checkPermissions($attributeInstance);
        }
    }

    protected function checkPermissions(PermissionMiddleware $permissionMiddleware): bool
    {
        foreach (['class', 'service'] as $method) {
            $permission = $permissionMiddleware->{$method};
            if ($permission === null) {
                continue;
            }

            $checkMethod = sprintf('check%s', ucfirst($method));
            $access = $this->{$checkMethod}($permissionMiddleware);
            if ($access === false) {
                return false;
            }
        }

        return true;
    }

    protected function checkClass(PermissionMiddleware $permissionMiddleware): bool
    {
        $classMiddleware = $permissionMiddleware->class;
        if ($classMiddleware === null) {
            return false;
        }

        /** @var string $className */
        $className = $classMiddleware->name;
        /** @var string $classMethod */
        $classMethod = $classMiddleware->method;

        /** @var callable $callable */
        $callable = [$className, $classMethod];
        /** @var bool $result */
        $result = call_user_func_array($callable, [$this->responseEvent]);

        return $result;
    }

    protected function checkService(PermissionMiddleware $permissionMiddleware): bool
    {
        $serviceMiddleware = $permissionMiddleware->service;
        if ($serviceMiddleware === null) {
            return false;
        }

        /** @var string $serviceName */
        $serviceName = $serviceMiddleware->name;
        /** @var string $serviceMethod */
        $serviceMethod = $serviceMiddleware->method;

        $service = $this->container->get($serviceName);

        /** @var callable $callable */
        $callable = [$service, $serviceMethod];
        /** @var bool $result */
        $result = call_user_func_array($callable, [$this->responseEvent]);

        return $result;
    }
}
