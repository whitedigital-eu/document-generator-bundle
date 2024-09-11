<?php declare(strict_types = 1);

namespace WhiteDigital\DocumentGeneratorBundle\GeneratorContext;

use WhiteDigital\DocumentGeneratorBundle\Contracts\GeneratorContext;

readonly class LocaleAwareGeneratorContext implements GeneratorContext
{
    public function __construct(
        private ?string $locale = null,
    ) {
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }
}
