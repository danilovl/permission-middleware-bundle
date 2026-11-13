<?php declare(strict_types=1);

namespace Danilovl\PermissionMiddlewareBundle\Tests\EventListener;

use Danilovl\PermissionMiddlewareBundle\EventListener\ControllerListener;
use Danilovl\PermissionMiddlewareBundle\Tests\Fixtures\{
    Checker,
    MethodController,
    SpyTranslator,
    ServiceChecker,
    InvokableController,
    ClassDeniedController,
    InvokableDeniedController
};
use Generator;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\{
    Request,
    RequestStack,
    RedirectResponse
};
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\{HttpKernelInterface, KernelEvents};
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\User\InMemoryUser;

class ControllerListenerTest extends TestCase
{
    private SpyTranslator $translator;

    private ServiceChecker $serviceChecker;

    private Session $session;

    protected function setUp(): void
    {
        Checker::reset();

        $this->translator = new SpyTranslator;
        $this->serviceChecker = new ServiceChecker;
        $this->session = new Session(new MockArraySessionStorage);
    }

    protected function tearDown(): void
    {
        ControllerListener::$priority = 2_048;
    }

    public function testSubscribedEventsUsePriority(): void
    {
        $expected = [KernelEvents::CONTROLLER => [['onKernelController', 2_048]]];
        $this->assertSame($expected, ControllerListener::getSubscribedEvents());

        ControllerListener::$priority = 0;

        $expected = [KernelEvents::CONTROLLER => [['onKernelController', 0]]];
        $this->assertSame($expected, ControllerListener::getSubscribedEvents());
    }

    public function testClosureControllerIsIgnored(): void
    {
        $controller = static function (): void {};
        $event = $this->createEvent($controller);
        $listener = $this->createListener();

        $listener->onKernelController($event);

        $this->assertSame($controller, $event->getController());
        $this->assertSame([], Checker::$calls);
    }

    public function testControllerWithoutAttributesIsIgnored(): void
    {
        $controller = [new MethodController, 'free'];
        $event = $this->createEvent($controller);
        $listener = $this->createListener();

        $listener->onKernelController($event);

        $this->assertSame($controller, $event->getController());
        $this->assertSame([], Checker::$calls);
    }

    public function testInvokableControllerAttributesAreChecked(): void
    {
        $controller = new InvokableController;
        $event = $this->createEvent($controller);
        $listener = $this->createListener();

        $listener->onKernelController($event);

        $this->assertSame($controller, $event->getController());
        $this->assertSame(['allow', 'record'], Checker::names());
        $this->assertSame(ControllerEvent::class, Checker::$calls[0][1]);
    }

    public function testInvokableControllerDenied(): void
    {
        $controller = new InvokableDeniedController;
        $event = $this->createEvent($controller);
        $listener = $this->createListener();

        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessage('translated:invokable.denied');

        $listener->onKernelController($event);
    }

    public function testClassDeniedThrowsTranslatedMessageWithDomain(): void
    {
        $controller = [new MethodController, 'classDenied'];
        $event = $this->createEvent($controller);
        $listener = $this->createListener();

        try {
            $listener->onKernelController($event);
            $this->fail('AccessDeniedHttpException was not thrown.');
        } catch (AccessDeniedHttpException $exception) {
            $this->assertSame('translated:class.denied', $exception->getMessage());
        }

        $expected = [['class.denied', ['%p%' => 1], 'security', null]];
        $this->assertSame($expected, $this->translator->calls);
    }

    public function testDeniedWithoutMessageThrowsEmptyMessage(): void
    {
        $controller = [new MethodController, 'classDeniedWithoutMessage'];
        $event = $this->createEvent($controller);
        $listener = $this->createListener();

        try {
            $listener->onKernelController($event);
            $this->fail('AccessDeniedHttpException was not thrown.');
        } catch (AccessDeniedHttpException $exception) {
            $this->assertSame('', $exception->getMessage());
        }

        $this->assertSame([], $this->translator->calls);
    }

    #[DataProvider('provideDateCases')]
    public function testDate(string $method, bool $allowed): void
    {
        /** @var callable $controller */
        $controller = [new MethodController, $method];
        $event = $this->createEvent($controller);
        $listener = $this->createListener();

        if (!$allowed) {
            $this->expectException(AccessDeniedHttpException::class);
        }

        $listener->onKernelController($event);

        $this->assertSame($controller, $event->getController());
    }

    public function testRedirectReplacesControllerAndAddsFlashOnce(): void
    {
        $controller = [new MethodController, 'redirect'];
        $event = $this->createEvent($controller);
        $listener = $this->createListener();

        $listener->onKernelController($event);

        $redirectController = $event->getController();
        $response = $redirectController();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/login?next=redirect', $response->getTargetUrl());

        $expectedFlashes = ['info' => ['translated:flash.redirect']];
        $this->assertSame($expectedFlashes, $this->session->getFlashBag()->peekAll());
    }

    public function testUserDeniedWithRedirectAddsFlashOnce(): void
    {
        $controller = [new MethodController, 'userRedirect'];
        $event = $this->createEvent($controller);
        $security = $this->createSecurity();
        $listener = $this->createListener($security);

        $listener->onKernelController($event);

        $redirectController = $event->getController();
        $response = $redirectController();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/login', $response->getTargetUrl());

        $expectedFlashes = ['warning' => ['translated:flash.user']];
        $this->assertSame($expectedFlashes, $this->session->getFlashBag()->peekAll());
    }

    #[DataProvider('provideUserCases')]
    public function testUser(string $method, bool $loggedIn, bool $allowed): void
    {
        /** @var callable $controller */
        $controller = [new MethodController, $method];
        $event = $this->createEvent($controller);
        $security = $this->createSecurity($loggedIn);
        $listener = $this->createListener($security);

        if (!$allowed) {
            $this->expectException(AccessDeniedHttpException::class);
        }

        $listener->onKernelController($event);

        $this->assertSame($controller, $event->getController());
    }

    public function testUserMissingRoleTranslatesMessage(): void
    {
        $controller = [new MethodController, 'userMissingRole'];
        $event = $this->createEvent($controller);
        $security = $this->createSecurity();
        $listener = $this->createListener($security);

        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessage('translated:user.denied');

        $listener->onKernelController($event);
    }

    public function testUserWithoutSecurityBundleThrowsLogicException(): void
    {
        $controller = [new MethodController, 'userRoles'];
        $event = $this->createEvent($controller);
        $listener = $this->createListener();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('symfony/security-bundle');

        $listener->onKernelController($event);
    }

    public function testServiceAllowed(): void
    {
        $controller = [new MethodController, 'serviceAllowed'];
        $event = $this->createEvent($controller);
        $listener = $this->createListener();

        $listener->onKernelController($event);

        $this->assertSame(['allow'], $this->serviceChecker->calls);
    }

    public function testServiceDenied(): void
    {
        $controller = [new MethodController, 'serviceDenied'];
        $event = $this->createEvent($controller);
        $listener = $this->createListener();

        try {
            $listener->onKernelController($event);
            $this->fail('AccessDeniedHttpException was not thrown.');
        } catch (AccessDeniedHttpException) {
        }

        $this->assertSame(['deny'], $this->serviceChecker->calls);
    }

    public function testAttributeForOtherEnvironmentIsSkipped(): void
    {
        $controller = [new MethodController, 'prodOnly'];
        $event = $this->createEvent($controller);
        $listener = $this->createListener();

        $listener->onKernelController($event);

        $this->assertSame([], Checker::$calls);
    }

    public function testAttributeForCurrentEnvironmentIsChecked(): void
    {
        $controller = [new MethodController, 'testOnly'];
        $event = $this->createEvent($controller);
        $listener = $this->createListener();

        $this->expectException(AccessDeniedHttpException::class);

        $listener->onKernelController($event);
    }

    public function testAfterResponseAttributesAreSkipped(): void
    {
        $controller = [new MethodController, 'afterResponseDeny'];
        $event = $this->createEvent($controller);
        $listener = $this->createListener();

        $listener->onKernelController($event);

        $this->assertSame([], Checker::$calls);
    }

    public function testClassDeniedSkipsMethodCheck(): void
    {
        $controller = [new ClassDeniedController, 'index'];
        $event = $this->createEvent($controller);
        $listener = $this->createListener();

        $listener->onKernelController($event);

        $this->assertSame(['deny'], Checker::names());

        $redirectController = $event->getController();
        $response = $redirectController();

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testMethodCheckFlagIsResetBetweenEvents(): void
    {
        $listener = $this->createListener();

        $deniedController = [new ClassDeniedController, 'index'];
        $deniedEvent = $this->createEvent($deniedController);
        $listener->onKernelController($deniedEvent);

        $recordedController = [new MethodController, 'recorded'];
        $recordedEvent = $this->createEvent($recordedController);
        $listener->onKernelController($recordedEvent);

        $this->assertSame(['deny', 'record'], Checker::names());
    }

    private function createListener(?Security $security = null): ControllerListener
    {
        $router = $this->createStub(RouterInterface::class);
        $router->method('generate')->willReturnCallback(
            static fn (string $name, array $parameters = []): string => '/' . $name . ($parameters ? '?' . http_build_query($parameters) : '')
        );

        $request = new Request;
        $request->setSession($this->session);

        $requestStack = new RequestStack;
        $requestStack->push($request);

        $container = new Container;
        $container->set('app.checker', $this->serviceChecker);

        return new ControllerListener($security, $router, $this->translator, $requestStack, $container, 'test');
    }

    private function createSecurity(bool $loggedIn = true): Security
    {
        $user = new InMemoryUser('admin', null, ['ROLE_ADMIN']);

        $security = $this->createStub(Security::class);
        $security->method('getUser')->willReturn($loggedIn ? $user : null);
        $security->method('isGranted')->willReturnCallback(
            static fn (mixed $attribute): bool => in_array($attribute, $user->getRoles(), true)
        );

        return $security;
    }

    private function createEvent(callable $controller): ControllerEvent
    {
        $kernel = $this->createStub(HttpKernelInterface::class);

        return new ControllerEvent($kernel, $controller, new Request, HttpKernelInterface::MAIN_REQUEST);
    }

    public static function provideDateCases(): Generator
    {
        yield 'expired' => ['dateExpired', false];
        yield 'not started' => ['dateNotStarted', false];
        yield 'active' => ['dateActive', true];
    }

    public static function provideUserCases(): Generator
    {
        yield 'role granted' => ['userRoles', true, true];
        yield 'role missing' => ['userMissingRole', true, false];
        yield 'user name matches' => ['userNameAdmin', true, true];
        yield 'user name differs' => ['userNameOther', true, false];
        yield 'not logged in' => ['userRoles', false, false];
    }
}
