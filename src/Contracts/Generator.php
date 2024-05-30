<?php declare(strict_types = 1);

namespace WhiteDigital\DocumentGeneratorBundle\Contracts;

interface Generator
{
    public function generate(): string;

    public function setTemplate(string $template): self;

    public function setData(array $data): self;

    /**
     * Allows to set generator-specific context.
     */
    public function setGeneratorContext(?GeneratorContext $context): self;
}
