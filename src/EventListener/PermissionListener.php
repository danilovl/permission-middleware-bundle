<?php declare(strict_types=1);

namespace Danilovl\PermissionMiddlewareBundle\EventListener;

use Danilovl\PermissionMiddlewareBundle\Attribute\PermissionMiddleware;
use Danilovl\PermissionMiddlewareBundle\Model\{
    FlashPermissionModel,
    TransPermissionModel,
    RedirectPermissionModel
};
use DateTime;
use ReflectionClass;
use ReflectionObject;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\{
    RequestStack,
    RedirectResponse,
    Session\Session
};
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PermissionListener
{
    protected bool $checkPermissionMethod = true;
    protected ControllerEvent $controllerEvent;

    public function __construct(
        protected readonly Security $security,
        protected readonly RouterInterface $router,
        protected readonly TranslatorInterface $translator,
        protected readonly RequestStack $requestStack,
        protected readonly ContainerInterface $container
    ) {}

    public function onKernelController(ControllerEvent $event): void
    {
        $controller = $event->getController();
        if (!is_array($controller)) {
            return;
        }

        $this->controllerEvent = $event;

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
            $this->checkPermissionMethod = !($this->checkPermissionMethod === false) && $this->checkPermissions($attribute->newInstance());
        }
    }

    protected function checkPermissionMethod(array $controllers): void
    {
        [$controller, $methodName] = $controllers;

        $attributes = (new ReflectionObject($controller))->getMethod($methodName)->getAttributes(PermissionMiddleware::class);
        foreach ($attributes as $attribute) {
            $this->checkPermissions($attribute->newInstance());
        }
    }

    protected function checkPermissions(PermissionMiddleware $permissionMiddleware): bool
    {
        $isRedirect = $this->checkRedirect($permissionMiddleware);
        if ($isRedirect === true) {
            return false;
        }

        foreach (['user', 'date', 'class', 'service'] as $method) {
            $permission = $permissionMiddleware->{$method};
            if ($permission === null) {
                continue;
            }

            $checkMethod = sprintf('check%s', ucfirst($method));
            $access = $this->{$checkMethod}($permissionMiddleware);
            if ($access === false) {
                $this->createResponse(
                    $permission->redirect,
                    $permission->exceptionMessage
                );

                return false;
            }
        }

        return true;
    }

    protected function createResponse(
        ?RedirectPermissionModel $redirect,
        ?TransPermissionModel $trans
    ): void {
        if ($redirect !== null) {
            $this->addFlashBag($redirect->flash);
            $this->setControllerRedirectResponse($redirect);

            return;
        }

        $message = $trans ? $this->getExceptionMessage($trans) : '';

        throw new AccessDeniedHttpException($message);
    }

    protected function checkUser(PermissionMiddleware $permissionMiddleware): bool
    {
        $user = $this->security->getUser();
        if ($user === null || $permissionMiddleware->user === null) {
            return false;
        }

        $roles = $permissionMiddleware->user->roles;
        if ($roles !== null) {
            $isGranted = false;
            foreach ($roles as $role) {
                if ($this->security->isGranted($role, $user)) {
                    $isGranted = true;
                    break;
                }
            }

            if (!$isGranted) {
                return false;
            }
        }

        $userNames = $permissionMiddleware->user->userNames;
        if ($userNames !== null && !in_array($user->getUserIdentifier(), $userNames, true)) {
            return false;
        }

        return true;
    }

    protected function checkDate(PermissionMiddleware $permissionMiddleware): bool
    {
        if ($permissionMiddleware->date === null) {
            return false;
        }

        $dateFrom = $permissionMiddleware->date->from;
        if ($dateFrom !== null) {
            if ((new DateTime)->getTimestamp() < $dateFrom->getTimestamp()) {
                return false;
            }
        }

        $dateTo = $permissionMiddleware->date->to;
        if ($dateTo !== null) {
            if ((new DateTime)->getTimestamp() > $dateTo->getTimestamp()) {
                return false;
            }
        }

        return true;
    }

    protected function checkRedirect(PermissionMiddleware $permissionMiddleware): bool
    {
        if ($permissionMiddleware->redirect === null) {
            return false;
        }

        $this->setControllerRedirectResponse($permissionMiddleware->redirect);

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
        $result = call_user_func_array($callable, [$this->controllerEvent]);

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
        $result = call_user_func_array($callable, [$this->controllerEvent]);

        return $result;
    }

    protected function addFlashBag(?FlashPermissionModel $flashPermissionModel): void
    {
        if ($flashPermissionModel === null) {
            return;
        }

        $type = $flashPermissionModel->type;

        $transArguments = $flashPermissionModel->trans->getArguments();

        /** @var Session $session */
        $session = $this->requestStack->getSession();

        $session->getFlashBag()->add(
            $type,
            $this->translator->trans(...$transArguments)
        );
    }

    protected function setControllerRedirectResponse(RedirectPermissionModel $redirectPermissionModel): void
    {
        $this->addFlashBag($redirectPermissionModel->flash);

        $this->controllerEvent->setController(function () use ($redirectPermissionModel): RedirectResponse {
            /** @var string $route */
            $route = $redirectPermissionModel->route;
            $url = $this->router->generate(
                $route,
                $redirectPermissionModel->parameters
            );

            return new RedirectResponse($url);
        });
    }

    protected function getExceptionMessage(TransPermissionModel $transPermissionModel): string
    {
        $transArguments = $transPermissionModel->getArguments();

        return $this->translator->trans(...$transArguments);
    }
}
