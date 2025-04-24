<?php declare(strict_types = 1);

namespace WhiteDigital\DocumentGeneratorBundle\GeneratorContext\Traits;

trait LocaleAware
{
    private ?string $locale = null;

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): static
    {
        $this->locale = $locale;

        return $this;
    }
}
