<?php declare(strict_types = 1);

namespace WhiteDigital\DocumentGeneratorBundle\GeneratorContext;

readonly class TwigToPdfGeneratorContext
{
    public function __construct(private ?string $headerTemplate = null, private ?string $footerTemplate = null)
    {
    }

    public function getHeaderTemplate(): ?string
    {
        return $this->headerTemplate;
    }

    public function getFooterTemplate(): ?string
    {
        return $this->footerTemplate;
    }
}
