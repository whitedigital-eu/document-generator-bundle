<?php declare(strict_types = 1);

namespace WhiteDigital\DocumentGeneratorBundle\Task;

use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Vich\UploaderBundle\FileAbstraction\ReplacingFile;
use WhiteDigital\DocumentGeneratorBundle\Contracts\Generator;
use WhiteDigital\DocumentGeneratorBundle\Contracts\Task;
use WhiteDigital\DocumentGeneratorBundle\Contracts\Transformer;
use WhiteDigital\DocumentGeneratorBundle\Entity\Document;
use WhiteDigital\EntityResourceMapper\EntityResourceMapperBundle;
use WhiteDigital\StorageItemResource\Entity\StorageItem;

use function array_merge;
use function count;
use function get_debug_type;
use function implode;

abstract class AbstractDocumentTask implements Task
{
    protected ?string $name = null;
    protected ?string $templatePath = null;
    protected ?string $type = null;

    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly Generator $generator,
        protected readonly Transformer $transformer,
    ) {
    }

    final public function generate(): Document
    {
        $data = $this->getTransformer()->getTransformedFields($fields = array_merge($this->getRequiredFields(), $this->getOptionalFields()));
        $this->validate($data);

        $result = $this->getGenerator()
            ->setData($data)
            ->setTemplate($this->getTemplatePath())
            ->generate();

        $storageItem = (new StorageItem())
            ->setFile(new ReplacingFile($result));
        $this->em->persist($storageItem);

        $document = (new Document())
            ->setType($this->getType())
            ->setSourceData($fields)
            ->setTemplateData($data)
            ->setFile($storageItem);

        $this->em->persist($document);
        // $this->em->flush();

        return $document;
    }

    public function getOptionalFields(): array
    {
        return [];
    }

    public function getGenerator(): Generator
    {
        return $this->generator;
    }

    public function getTransformer(): Transformer
    {
        return $this->transformer;
    }

    protected function validate(array $data): void
    {
        $count = 0;
        $invalid = [];
        $fullData = array_merge($this->getRequiredFields(), $this->getOptionalFields());

        $dataDump = EntityResourceMapperBundle::makeOneDimension($data, onlyLast: true);
        $fullDump = EntityResourceMapperBundle::makeOneDimension($fullData, onlyLast: true);
        $requiredDump = EntityResourceMapperBundle::makeOneDimension($this->getRequiredFields(), onlyLast: true);

        foreach ($dataDump as $key => $value) {
            if (!isset($fullDump[$key])) {
                $invalid[] = $key;
                continue;
            }

            if (get_debug_type($value) !== $fullDump[$key]) {
                throw new InvalidArgumentException();
            }
            $count += (int) isset($requiredDump[$key]);
        }

        if (count($requiredDump) !== $count) {
            throw new InvalidArgumentException();
        }

        if ([] !== $invalid) {
            throw new InvalidArgumentException(implode(', ', $invalid));
        }
    }
}
