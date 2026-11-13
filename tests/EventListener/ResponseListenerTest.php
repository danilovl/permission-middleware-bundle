<?php declare(strict_types=1);

namespace Danilovl\PermissionMiddlewareBundle\Tests\EventListener;

use Danilovl\PermissionMiddlewareBundle\EventListener\ResponseListener;
use Danilovl\PermissionMiddlewareBundle\Tests\Fixtures\{
    Checker,
    ServiceChecker,
    MethodController,
    InvokableController,
    ClassDeniedController
};
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\{HttpKernelInterface, KernelEvents};

class ResponseListenerTest extends TestCase
{
    private ServiceChecker $serviceChecker;

    protected function setUp(): void
    {
        Checker::reset();

        $this->serviceChecker = new ServiceChecker;
    }

    protected function tearDown(): void
    {
        ResponseListener::$priority = 0;
    }

    public function testSubscribedEventsUsePriority(): void
    {
        $expected = [KernelEvents::RESPONSE => [['onKernelResponse', 0]]];
        $this->assertSame($expected, ResponseListener::getSubscribedEvents());

        ResponseListener::$priority = -10;

        $expected = [KernelEvents::RESPONSE => [['onKernelResponse', -10]]];
        $this->assertSame($expected, ResponseListener::getSubscribedEvents());
    }

    #[DataProvider('provideIgnoredControllerCases')]
    public function testIgnoredController(mixed $controller): void
    {
        $event = $this->createEvent($controller);
        $listener = $this->createListener();

        $listener->onKernelResponse($event);

        $this->assertSame([], Checker::$calls);
    }

    #[DataProvider('provideInvokableControllerCases')]
    public function testInvokableController(mixed $controller): void
    {
        $event = $this->createEvent($controller);
        $listener = $this->createListener();

        $listener->onKernelResponse($event);

        $this->assertSame(['record'], Checker::names());
        $this->assertSame(ResponseEvent::class, Checker::$calls[0][1]);
    }

    public function testOnlyAfterResponseAttributesAreChecked(): void
    {
        $event = $this->createEvent(MethodController::class . '::mixed');
        $listener = $this->createListener();

        $listener->onKernelResponse($event);

        $this->assertSame(['record'], Checker::names());
    }

    public function testClassAndServiceChecks(): void
    {
        $event = $this->createEvent([MethodController::class, 'afterResponseBoth']);
        $listener = $this->createListener();

        $listener->onKernelResponse($event);

        $this->assertSame(['record'], Checker::names());
        $this->assertSame(['allow'], $this->serviceChecker->calls);
    }

    public function testAttributeForOtherEnvironmentIsSkipped(): void
    {
        $event = $this->createEvent(MethodController::class . '::afterResponseProdOnly');
        $listener = $this->createListener();

        $listener->onKernelResponse($event);

        $this->assertSame([], Checker::$calls);
    }

    public function testAttributeForCurrentEnvironmentIsChecked(): void
    {
        $event = $this->createEvent(MethodController::class . '::afterResponseProdOnly');
        $listener = $this->createListener('prod');

        $listener->onKernelResponse($event);

        $this->assertSame(['record'], Checker::names());
    }

    public function testClassDeniedSkipsMethodCheck(): void
    {
        $event = $this->createEvent(ClassDeniedController::class . '::index');
        $listener = $this->createListener();

        $listener->onKernelResponse($event);

        $this->assertSame(['deny'], Checker::names());
    }

    public function testMethodCheckFlagIsResetBetweenEvents(): void
    {
        $listener = $this->createListener();

        $deniedEvent = $this->createEvent(ClassDeniedController::class . '::index');
        $listener->onKernelResponse($deniedEvent);

        $invokableEvent = $this->createEvent(InvokableController::class);
        $listener->onKernelResponse($invokableEvent);

        $this->assertSame(['deny', 'record'], Checker::names());
    }

    private function createListener(string $environment = 'test'): ResponseListener
    {
        $container = new Container;
        $container->set('app.checker', $this->serviceChecker);

        return new ResponseListener($container, $environment);
    }

    private function createEvent(mixed $controller): ResponseEvent
    {
        $kernel = $this->createStub(HttpKernelInterface::class);

        $request = new Request;
        $request->attributes->set('_controller', $controller);

        return new ResponseEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, new Response);
    }

    public static function provideIgnoredControllerCases(): Generator
    {
        yield 'empty' => [''];
        yield 'unknown class' => ['App\\Missing\\Controller::index'];
        yield 'unknown invokable class' => ['App\\Missing\\Controller'];
        yield 'non invokable class' => [MethodController::class];
        yield 'function name' => ['strlen'];
        yield 'closure' => [static function (): void {}];
        yield 'method without attributes' => [MethodController::class . '::free'];
    }

    public static function provideInvokableControllerCases(): Generator
    {
        yield 'class string' => [InvokableController::class];
        yield 'class string with __invoke' => [InvokableController::class . '::__invoke'];
        yield 'object' => [new InvokableController];
        yield 'array' => [[new InvokableController, '__invoke']];
    }
}
