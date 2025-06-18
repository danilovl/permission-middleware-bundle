<?php declare(strict_types=1);

namespace Danilovl\PermissionMiddlewareBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public const string ALIAS = 'danilovl_permission_middleware';

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder(self::ALIAS);
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('kernel_controller_priority')
                    ->defaultNull()
                    ->info('Priority for the controller listener')
                ->end()
                ->scalarNode('kernel_response_priority')
                    ->defaultNull()
                    ->info('Priority for the response listener')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
