<?php declare(strict_types = 1);

namespace WhiteDigital\DocumentGeneratorBundle\GeneratorContext\Traits;

use InvalidArgumentException;
use WhiteDigital\DocumentGeneratorBundle\Contracts\GeneratorContext;

trait MultiLayoutTwigToPdf
{
    protected array $layouts = [];

    public function getLayouts(): array
    {
        return $this->layouts;
    }

    public function setLayouts(array $layouts): static
    {
        foreach ($layouts as $layout) {
            if (!$layout instanceof GeneratorContext) {
                throw new InvalidArgumentException(sprintf('All layouts must be instances of %s', GeneratorContext::class));
            }
        }

        $this->layouts = $layouts;

        return $this;
    }
}
