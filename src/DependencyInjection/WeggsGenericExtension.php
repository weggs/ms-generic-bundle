<?php
namespace Weggs\GenericBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * WeggsGenericExtension
 */
class WeggsGenericExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $config = $this->processConfiguration($this->getConfiguration([], $container), $configs);
        $this->addParam($config, $container, $this->getAlias());
    }

    public function getConfiguration(array $config, ContainerBuilder $container): ConfigurationInterface
    {
        return new Configuration();
    }

    public function getAlias(): string
    {
        return 'weggs_generic';
    }

    public function addParam(array $config, ContainerBuilder $container, string $prefix) {
        foreach ($config as $key => $value) {
            if (is_array($value)) {
                $this->addParam($value, $container, $prefix.'.'.$key);
                continue;
            }
            $container->setParameter($prefix . '.' . $key, $value);
        }
    }
}
