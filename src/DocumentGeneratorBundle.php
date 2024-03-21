<?php declare(strict_types = 1);

namespace WhiteDigital\DocumentGeneratorBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use WhiteDigital\EntityResourceMapper\DependencyInjection\Traits\DefineOrmMappings;

use function array_merge_recursive;

class DocumentGeneratorBundle extends AbstractBundle
{
    use DefineOrmMappings;

    private const MAPPINGS = [
        'type' => 'attribute',
        'dir' => __DIR__ . '/Entity',
        'alias' => 'DocumentGeneratorBundle',
        'prefix' => 'WhiteDigital\DocumentGeneratorBundle\Entity',
        'is_bundle' => false,
        'mapping' => true,
    ];

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
    }

    public static function getConfig(string $package, ContainerBuilder $builder): array
    {
        return array_merge_recursive(...$builder->getExtensionConfig($package));
    }
}
