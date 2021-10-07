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
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class PermissionListener
{
    protected bool $checkPermissionMethod = true;
    protected ControllerEvent $controllerEvent;

    public function __construct(
        protected Security $security,
        protected RouterInterface $router,
        protected TranslatorInterface $translator,
        protected SessionInterface $session,
        protected ContainerInterface $container
    ) {
    }

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
            if ($permissionMiddleware->{$method} === null) {
                continue;
            }

            $checkMethod = sprintf('check%s', strtoupper($method));
            $access = $this->{$checkMethod}($permissionMiddleware);
            if ($access === false) {
                if ($permissionMiddleware->{$method}->accessDeniedHttpException) {
                    $this->createResponse(
                        $permissionMiddleware->{$method}->redirect,
                        $permissionMiddleware->{$method}->exceptionMessage
                    );
                }

                return false;
            }
        }

        return true;
    }

    protected function createResponse(
        RedirectPermissionModel $redirect,
        TransPermissionModel $trans
    ): void {
        if ($redirect->route !== null) {
            $this->addFlashBag($redirect->flash);
            $this->setControllerRedirectResponse($redirect);

            return;
        }

        throw new AccessDeniedHttpException($this->getExceptionMessage($trans));
    }

    protected function checkUser(PermissionMiddleware $permissionMiddleware): bool
    {
        $user = $this->security->getUser();
        if ($user === null) {
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
        if ($userNames !== null && !in_array($user->getUsername(), $userNames, true)) {
            return false;
        }

        return true;
    }

    protected function checkDate(PermissionMiddleware $permissionMiddleware): bool
    {
        $dateFrom = $permissionMiddleware->date->from;
        if ($dateFrom !== null) {
            if (new DateTime < $dateFrom) {
                return false;
            }
        }

        $dateTo = $permissionMiddleware->date->to;
        if ($dateTo !== null) {
            if (new DateTime > $dateTo) {
                return false;
            }
        }

        return true;
    }

    protected function checkRedirect(PermissionMiddleware $permissionMiddleware): bool
    {
        if ($permissionMiddleware->redirect === null || !$permissionMiddleware->redirect->canCheck()) {
            return false;
        }

        $this->setControllerRedirectResponse($permissionMiddleware->redirect);

        return true;
    }

    protected function checkClass(PermissionMiddleware $permissionMiddleware): bool
    {
        $classMiddleware = $permissionMiddleware->class;
        if (!$classMiddleware->canCheck()) {
            return false;
        }

        return call_user_func_array([$classMiddleware->name, $classMiddleware->method], [$this->controllerEvent]);
    }

    protected function checkService(PermissionMiddleware $permissionMiddleware): mixed
    {
        $serviceMiddleware = $permissionMiddleware->service;
        if (!$serviceMiddleware->canCheck()) {
            return false;
        }

        $service = $this->container->get($serviceMiddleware->name);

        return call_user_func_array([$service, $serviceMiddleware->method], [$this->controllerEvent]);
    }

    protected function addFlashBag(FlashPermissionModel $flashPermissionModel): void
    {
        if (!$flashPermissionModel->canCheck()) {
            return;
        }

        $trans = $flashPermissionModel->trans->getArguments();
        $this->session->getFlashBag()->add(
            $flashPermissionModel->type,
            $this->translator->trans(...$trans)
        );
    }

    protected function setControllerRedirectResponse(RedirectPermissionModel $redirectPermissionModel): void
    {
        $this->addFlashBag($redirectPermissionModel->flash);

        $this->controllerEvent->setController(function () use ($redirectPermissionModel): RedirectResponse {
            $url = $this->router->generate(
                $redirectPermissionModel->route,
                $redirectPermissionModel->parameters
            );

            return new RedirectResponse($url);
        });
    }

    protected function getExceptionMessage(TransPermissionModel $transPermissionModel): string
    {
        if (!$transPermissionModel->canCheck()) {
            return '';
        }

        return $this->translator->trans(...$transPermissionModel->getArguments());
    }
}
