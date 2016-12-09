<?php


namespace Drupal\service_provider;


use Drupal\druplash\DrupalContainerAdapter;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class DrupalServiceProviderCompilerPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        // Let's override the acclimated container with a Drupal specific one.
        $definition = new Definition(DrupalContainerAdapter::class);
        $definition->addArgument(new Reference("service_container"));
        $container->setDefinition('interop_service_provider_acclimated_container', $definition);

        // Let's override the registry definition
        if (file_exists(__DIR__.'/../../../../service-providers.php')) {
            $file = require __DIR__.'/../../../../service-providers.php';
            $puli = $file['puli'] ?? true;
            $serviceProviders = $file['service-providers'] ?? [];
        } else {
            $puli = true;
            $serviceProviders = [];
        }

        $definition = new Definition(DrupalServiceProviderRegistry::class);
        $definition->addArgument($serviceProviders);
        if ($puli) {
            $definition->addArgument(new Reference('puli_discovery'));
        }
        $container->setDefinition('service_provider_registry_0', $definition);

        // Let's provide a service for PSR-3 logger
        $container->setAlias('psr\\log\\loggerinterface', 'logger.channel.default');
    }
}