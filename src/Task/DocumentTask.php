<?php declare(strict_types = 1);

namespace WhiteDigital\DocumentGeneratorBundle\Task;

use Doctrine\ORM\EntityManagerInterface;
use WhiteDigital\DocumentGeneratorBundle\Entity\Document;
use WhiteDigital\DocumentGeneratorBundle\Generator\TwigToPdfGenerator;
use WhiteDigital\DocumentGeneratorBundle\Transformer\DocumentTransformer;

class DocumentTask extends AbstractDocumentTask
{
    private ?Document $document = null;

    public function __construct(
        EntityManagerInterface $em,
        TwigToPdfGenerator $generator,
        DocumentTransformer $transformer,
    ) {
        parent::__construct($em, $generator, $transformer);
    }

    public function getTransformerFields(): array
    {
        return $this->getInput()->getSourceData();
    }

    public function getTemplatePath(): string
    {
        return $this->getInput()->getTemplatePath();
    }

    public function getType(): string
    {
        return $this->getInput()->getType();
    }

    public function getInputType(): string
    {
        return Document::class;
    }
}
