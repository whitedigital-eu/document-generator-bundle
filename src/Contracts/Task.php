<?php declare(strict_types = 1);

namespace WhiteDigital\DocumentGeneratorBundle\Contracts;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use WhiteDigital\DocumentGeneratorBundle\Entity\Document;

#[Autoconfigure(tags: ['document.task'], lazy: true)]
interface Task
{
    public function getGenerator(): Generator;

    public function getTransformer(): Transformer;

    public function getRequiredFields(): array;

    public function getOptionalFields(): array;

    public function generate(): Document;

    public function getTemplatePath(): string;

    public function getType(): string;
}
