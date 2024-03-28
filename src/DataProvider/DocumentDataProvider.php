<?php declare(strict_types = 1);

namespace WhiteDigital\DocumentGeneratorBundle\DataProvider;

use ApiPlatform\Exception\ResourceClassNotFoundException;
use ApiPlatform\Metadata\Operation;
use ReflectionException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Contracts\Service\Attribute\Required;
use WhiteDigital\DocumentGeneratorBundle\Api\Resource\DocumentResource;
use WhiteDigital\DocumentGeneratorBundle\Task\DocumentTask;
use WhiteDigital\EntityResourceMapper\DataProvider\AbstractDataProvider;
use WhiteDigital\EntityResourceMapper\Entity\BaseEntity;

class DocumentDataProvider extends AbstractDataProvider
{
    private DocumentTask $task;

    #[Required]
    public function setTask(DocumentTask $task): void
    {
        $this->task = $task;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ('generate' === $operation->getName()) {
            return $this->receipt($operation, $uriVariables, $context);
        }

        return parent::provide($operation, $uriVariables, $context);
    }

    /**
     * @throws ReflectionException
     * @throws ExceptionInterface
     * @throws ResourceClassNotFoundException
     */
    protected function createResource(BaseEntity $entity, array $context): DocumentResource
    {
        return DocumentResource::create($entity, $context);
    }

    private function receipt(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $entity = $this->entityManager->find($this->getEntityClass($operation), $uriVariables['id']);

        $document = $this->task->generate($entity);

        return DocumentResource::create($document, $context);
    }
}
