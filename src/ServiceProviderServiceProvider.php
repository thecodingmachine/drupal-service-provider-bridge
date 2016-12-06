<?php


namespace Drupal\service_provider;


use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface as SymfonyContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use TheCodingMachine\Interop\ServiceProviderBridgeBundle\Exception\InvalidArgumentException;
use TheCodingMachine\Interop\ServiceProviderBridgeBundle\RegistryProviderInterface;
use TheCodingMachine\Interop\ServiceProviderBridgeBundle\ServiceProviderCompilationPass;
use TheCodingMachine\ServiceProvider\Registry;

/**
 * The default service provider loaded by the service_provider module (hence the stupid name :) )
 */
class ServiceProviderServiceProvider implements ServiceProviderInterface, RegistryProviderInterface
{

    /**
     * Registers services to the container.
     *
     * @param ContainerBuilder $container
     *   The ContainerBuilder to register services to.
     */
    public function register(ContainerBuilder $container)
    {
        // Let's initialize the Symfony bridge service provider.
        $serviceProviderCompilationPass = new ServiceProviderCompilationPass(0, $this);
        $container->addCompilerPass($serviceProviderCompilationPass);

        // A last compiler pass that overrides some services created by the ServiceProviderCompilationPass
        $container->addCompilerPass(new DrupalServiceProviderCompilerPass());

    }

    /**
     * @param SymfonyContainerInterface $container
     * @return Registry
     * @throws InvalidArgumentException
     */
    public function getRegistry(SymfonyContainerInterface $container)
    {

        if (file_exists(__DIR__.'/../../../service-providers.php')) {
            $file = require __DIR__.'/../../../service-providers.php';
            $puli = $file['puli'] ?? true;
            $serviceProviders = $file['service-providers'] ?? [];
        } else {
            $puli = true;
            $serviceProviders = [];
        }

        if ($puli) {
            $factoryClass = PULI_FACTORY_CLASS;
            $factory = new $factoryClass();

            $repo = $factory->createRepository();
            $discovery = $factory->createDiscovery($repo);
        } else {
            $discovery = null;
        }

        // In parallel, let's merge the registry:
        $registry = new Registry($serviceProviders, $discovery);
        return $registry;
    }
}