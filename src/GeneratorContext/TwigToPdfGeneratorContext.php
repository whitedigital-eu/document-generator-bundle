<?php declare(strict_types = 1);

namespace WhiteDigital\DocumentGeneratorBundle\GeneratorContext;

use WhiteDigital\DocumentGeneratorBundle\Contracts\GeneratorContext;

/**
 * This class is used to add additional context to the TwigToPdfGenerator, such as header and footer templates.
 */
readonly class TwigToPdfGeneratorContext implements GeneratorContext
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
