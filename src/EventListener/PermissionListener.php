<?php declare(strict_types=1);

namespace Danilovl\PermissionMiddlewareBundle\EventListener;

use Danilovl\PermissionMiddlewareBundle\Attribute\PermissionMiddleware;
use Danilovl\PermissionMiddlewareBundle\Model\{
    FlashPermissionModel,
    TransPermissionModel,
    RedirectPermissionModel
};
use DateTime;
use Doctrine\Common\Annotations\Reader;
use ReflectionClass;
use ReflectionObject;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class PermissionListener
{
    private bool $checkPermissionMethod = true;
    private ControllerEvent $controllerEvent;

    public function __construct(
        private Security $security,
        private RouterInterface $router,
        private TranslatorInterface $translator,
        private SessionInterface $session,
        private Reader $reader
    ) {
    }

    public function onKernelController(ControllerEvent $event)
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

    private function checkPermissionClass(array $controllers): void
    {
        [$controller] = $controllers;

        $attributes = (new ReflectionClass($controller))->getAttributes(PermissionMiddleware::class);
        foreach ($attributes as $attribute) {
            $this->checkPermissionMethod = $this->checkPermissionMethod === false ? false : $this->checkPermissions($attribute->newInstance());
        }
    }

    private function checkPermissionMethod(array $controllers): void
    {
        [$controller, $methodName] = $controllers;

        $attributes = (new ReflectionObject($controller))->getMethod($methodName)->getAttributes(PermissionMiddleware::class);
        foreach ($attributes as $attribute) {
            $this->checkPermissions($attribute->newInstance());
        }
    }

    private function checkPermissions(PermissionMiddleware $permissionMiddleware): bool
    {
        $isRedirect = $this->checkRedirect($permissionMiddleware);
        if ($isRedirect === true) {
            return false;
        }

        foreach (['user', 'date'] as $method) {
            $checkMethod = sprintf('check%s', strtoupper($method));
            $access = $this->{$checkMethod}($permissionMiddleware);
            if ($access === false) {
                $this->createResponse(
                    $permissionMiddleware->{$method}->redirect,
                    $permissionMiddleware->{$method}->exceptionMessage
                );
                return false;
            }
        }

        return true;
    }

    private function createResponse(
        RedirectPermissionModel $redirect,
        TransPermissionModel $trans
    ) {
        if ($redirect->route !== null) {
            $this->addFlashBag($redirect->flash);
            $this->setControllerRedirectResponse($redirect);

            return;
        }

        throw new AccessDeniedHttpException($this->getExceptionMessage($trans));
    }

    private function checkUser(PermissionMiddleware $permissionMiddleware): bool
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
        if ($userNames !== null) {
            if ($user === null || !in_array($user->getUsername(), $userNames, true)) {
                return false;
            }
        }

        return true;
    }

    private function checkDate(PermissionMiddleware $permissionMiddleware): bool
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

    private function checkRedirect(PermissionMiddleware $permissionMiddleware): bool
    {
        if ($permissionMiddleware->redirect->route === null) {
            return false;
        }

        $this->setControllerRedirectResponse($permissionMiddleware->redirect);

        return true;
    }

    private function addFlashBag(FlashPermissionModel $flashPermissionModel): void
    {
        $type = $flashPermissionModel->type;
        if ($type === null) {
            return;
        }

        $trans = $flashPermissionModel->trans->getArguments();
        $this->session->getFlashBag()->add(
            $type,
            $this->translator->trans(...$trans)
        );
    }

    private function setControllerRedirectResponse(RedirectPermissionModel $redirectPermissionModel): void
    {
        $this->addFlashBag($redirectPermissionModel->flash);

        $this->controllerEvent->setController(function () use ($redirectPermissionModel) {
            $url = $this->router->generate(
                $redirectPermissionModel->route,
                $redirectPermissionModel->parameters
            );

            return new RedirectResponse($url);
        });
    }

    private function getExceptionMessage(TransPermissionModel $transPermissionModel): string
    {
        if ($transPermissionModel->message === null) {
            return '';
        }

        return $this->translator->trans(...$transPermissionModel->getArguments());
    }
}