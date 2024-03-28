<?php declare(strict_types=1);

namespace WhiteDigital\DocumentGeneratorBundle\DataProvider;

use ApiPlatform\Exception\ResourceClassNotFoundException;
use ApiPlatform\State\ProviderInterface;
use ReflectionException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use WhiteDigital\DocumentGeneratorBundle\Api\Resource\DocumentResource;
use WhiteDigital\EntityResourceMapper\DataProvider\AbstractDataProvider;
use WhiteDigital\EntityResourceMapper\Entity\BaseEntity;
use WhiteDigital\StorageItemResource\Api\Resource\StorageItemResource;

class DocumentDataProvider extends AbstractDataProvider
{
    /**
     * @throws ReflectionException
     * @throws ExceptionInterface
     * @throws ResourceClassNotFoundException
     */
    protected function createResource(BaseEntity $entity, array $context): DocumentResource
    {
        return DocumentResource::create($entity, $context);
    }
}
