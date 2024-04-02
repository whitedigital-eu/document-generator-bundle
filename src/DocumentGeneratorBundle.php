<?php declare(strict_types = 1);

namespace WhiteDigital\DocumentGeneratorBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use WhiteDigital\EntityResourceMapper\DependencyInjection\Traits\DefineApiPlatformMappings;
use WhiteDigital\EntityResourceMapper\DependencyInjection\Traits\DefineOrmMappings;

use function array_key_exists;
use function array_merge_recursive;

class DocumentGeneratorBundle extends AbstractBundle
{
    use DefineApiPlatformMappings;
    use DefineOrmMappings;

    private const MAPPINGS = [
        'type' => 'attribute',
        'dir' => __DIR__ . '/Entity',
        'alias' => 'DocumentGeneratorBundle',
        'prefix' => 'WhiteDigital\DocumentGeneratorBundle\Entity',
        'is_bundle' => false,
        'mapping' => true,
    ];

    private const API_RESOURCE_PATH = '%kernel.project_dir%/vendor/whitedigital-eu/document-generator-bundle/src/Api/Resource';

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.php');
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $extensionConfig = self::getConfig('document_generator', $builder);
        $auditExtensionConfig = self::getConfig('audit', $builder);
        $manager = $extensionConfig['entity_manager'] ?? 'default';

        $this->addDoctrineConfig($container, $manager, 'DocumentGenerator', self::MAPPINGS);

        if ([] !== $auditExtensionConfig) {
            $mappings = $this->getOrmMappings($builder, $auditExtensionConfig['default_entity_manager'] ?? 'default');
            $this->addDoctrineConfig($container, $auditExtensionConfig['audit_entity_manager'] ?? 'audit', 'DocumentGenerator', self::MAPPINGS, $mappings);
        }

        $this->configureApiPlatformExtension($container, $extensionConfig);
    }

    public static function getConfig(string $package, ContainerBuilder $builder): array
    {
        return array_merge_recursive(...$builder->getExtensionConfig($package));
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition
            ->rootNode()
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('entity_manager')->defaultValue('default')->end()
                ->scalarNode('custom_api_resource_path')->defaultNull()->end()
            ->end();
    }

    private function configureApiPlatformExtension(ContainerConfigurator $container, array $extensionConfig): void
    {
        if (!array_key_exists('custom_api_resource_path', $extensionConfig)) {
            $this->addApiPlatformPaths($container, [self::API_RESOURCE_PATH]);
        } elseif (!empty($extensionConfig['custom_api_resource_path'])) {
            $this->addApiPlatformPaths($container, [$extensionConfig['custom_api_resource_path']]);
        }
    }
}
