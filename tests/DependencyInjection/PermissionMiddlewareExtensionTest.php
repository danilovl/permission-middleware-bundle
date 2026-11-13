<?php declare(strict_types=1);

namespace Danilovl\PermissionMiddlewareBundle\Tests\DependencyInjection;

use Danilovl\PermissionMiddlewareBundle\DependencyInjection\{
    Configuration,
    PermissionMiddlewareExtension
};
use Danilovl\PermissionMiddlewareBundle\EventListener\{
    ResponseListener,
    ControllerListener
};
use Danilovl\PermissionMiddlewareBundle\PermissionMiddlewareBundle;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\{
    Reference,
    ContainerBuilder,
    ContainerInterface
};
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PermissionMiddlewareExtensionTest extends TestCase
{
    protected function tearDown(): void
    {
        ControllerListener::$priority = 2_048;
        ResponseListener::$priority = 0;
    }

    public function testDefaultConfiguration(): void
    {
        $processor = new Processor;
        $config = $processor->processConfiguration(new Configuration, []);

        $expected = ['kernel_controller_priority' => null, 'kernel_response_priority' => null];
        $this->assertSame($expected, $config);
    }

    #[DataProvider('provideInvalidPriorityCases')]
    public function testInvalidPriority(mixed $value): void
    {
        $processor = new Processor;
        $configs = [['kernel_controller_priority' => $value]];

        $this->expectException(InvalidTypeException::class);

        $processor->processConfiguration(new Configuration, $configs);
    }

    public function testBundleExtensionAlias(): void
    {
        $bundle = new PermissionMiddlewareBundle;
        $extension = $bundle->getContainerExtension();

        $this->assertSame(Configuration::ALIAS, $extension->getAlias());
    }

    public function testLoadRegistersListeners(): void
    {
        $container = new ContainerBuilder;
        $extension = new PermissionMiddlewareExtension;

        $extension->load([], $container);

        $this->assertTrue($container->hasDefinition(ControllerListener::class));
        $this->assertTrue($container->hasDefinition(ResponseListener::class));

        $definition = $container->getDefinition(ControllerListener::class);
        $security = $definition->getArgument('$security');

        $this->assertInstanceOf(Reference::class, $security);
        $this->assertSame('security.helper', (string) $security);
        $this->assertSame(ContainerInterface::IGNORE_ON_INVALID_REFERENCE, $security->getInvalidBehavior());

        $this->assertSame(2_048, ControllerListener::$priority);
        $this->assertSame(0, ResponseListener::$priority);
    }

    public function testCompiledContainerWithoutSecurityBundle(): void
    {
        $container = new ContainerBuilder;
        $container->setParameter('kernel.environment', 'test');

        foreach ([RouterInterface::class, TranslatorInterface::class, RequestStack::class] as $id) {
            $container->register($id)->setSynthetic(true);
        }

        $extension = new PermissionMiddlewareExtension;
        $extension->load([], $container);
        $container->compile();

        $container->set(RouterInterface::class, $this->createStub(RouterInterface::class));
        $container->set(TranslatorInterface::class, $this->createStub(TranslatorInterface::class));
        $container->set(RequestStack::class, new RequestStack);

        $controllerListener = $container->get(ControllerListener::class);
        $responseListener = $container->get(ResponseListener::class);

        $this->assertInstanceOf(ControllerListener::class, $controllerListener);
        $this->assertInstanceOf(ResponseListener::class, $responseListener);

        $securityProperty = new ReflectionProperty(ControllerListener::class, 'security');
        $this->assertNull($securityProperty->getValue($controllerListener));
    }

    public function testLoadAppliesPriorities(): void
    {
        $extension = new PermissionMiddlewareExtension;
        $configs = [[
            'kernel_controller_priority' => 100,
            'kernel_response_priority' => -100
        ]];

        $extension->load($configs, new ContainerBuilder);

        $this->assertSame(100, ControllerListener::$priority);
        $this->assertSame(-100, ResponseListener::$priority);
    }

    public function testLoadAppliesZeroPriority(): void
    {
        ControllerListener::$priority = 5;
        ResponseListener::$priority = 5;

        $extension = new PermissionMiddlewareExtension;
        $configs = [[
            'kernel_controller_priority' => 0,
            'kernel_response_priority' => 0
        ]];

        $extension->load($configs, new ContainerBuilder);

        $this->assertSame(0, ControllerListener::$priority);
        $this->assertSame(0, ResponseListener::$priority);
    }

    public static function provideInvalidPriorityCases(): Generator
    {
        yield 'string' => ['10'];
        yield 'float' => [1.5];
        yield 'bool' => [true];
    }
}
