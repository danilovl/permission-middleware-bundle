<?php declare(strict_types=1);

namespace Danilovl\PermissionMiddlewareBundle\EventListener;

use Danilovl\PermissionMiddlewareBundle\Model\{
	FlashPermissionModel,
	TransPermissionModel,
	RedirectPermissionModel
};
use Danilovl\PermissionMiddlewareBundle\Annotation\PermissionMiddleware;
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
	private Reader $reader;
	private Security $security;
	private RouterInterface $router;
	private TranslatorInterface $translator;
	private SessionInterface $session;
	private ControllerEvent $controllerEvent;

	public function __construct(
		Security $security,
		RouterInterface $router,
		TranslatorInterface $translator,
		SessionInterface $session,
		Reader $reader
	) {
		$this->security = $security;
		$this->router = $router;
		$this->translator = $translator;
		$this->session = $session;
		$this->reader = $reader;
	}

	public function onKernelController(ControllerEvent $event)
	{
		$controllers = $event->getController();
		if (!is_array($controllers)) {
			return;
		}

		$this->controllerEvent = $event;

		$this->checkPermissionClass($controllers);
		$this->checkPermissionMethod($controllers);
	}

	private function checkPermissionClass(array $controllers): void
	{
		[$controller] = $controllers;

		/** @var PermissionMiddleware $classPermissionMiddleware */
		$classPermissionMiddleware = $this->reader->getClassAnnotation(
			new ReflectionClass($controller),
			PermissionMiddleware::class
		);

		$this->checkPermissions($classPermissionMiddleware);
	}

	private function checkPermissionMethod(array $controllers): void
	{
		[$controller, $methodName] = $controllers;

		/** @var PermissionMiddleware $methodPermissionMiddleware */
		$methodPermissionMiddleware = $this->reader->getMethodAnnotation(
			(new ReflectionObject($controller))->getMethod($methodName),
			PermissionMiddleware::class
		);

		$this->checkPermissions($methodPermissionMiddleware);
	}

	private function checkPermissions(?PermissionMiddleware $permissionMiddleware): void
	{
		if ($permissionMiddleware === null) {
			return;
		}

		$access = $this->checkRedirect($permissionMiddleware);
		if ($access === false) {
			return;
		}

		foreach (['user', 'date'] as $method) {
			$checkMethod = sprintf('check%s', strtoupper($method));
			$access = $this->{$checkMethod}($permissionMiddleware);
			if ($access === false) {
				$this->createResponse(
					$permissionMiddleware->{$method}->redirect,
					$permissionMiddleware->{$method}->exceptionMessage
				);
				break;
			}
		}
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
			return true;
		}

		$this->setControllerRedirectResponse($permissionMiddleware->redirect);

		return false;
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

		return $this->translator->trans(
			...$transPermissionModel->getArguments()
		);
	}
}