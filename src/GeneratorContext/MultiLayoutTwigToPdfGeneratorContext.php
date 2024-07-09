<?php declare(strict_types=1);

namespace WhiteDigital\DocumentGeneratorBundle\GeneratorContext;

use InvalidArgumentException;
use WhiteDigital\DocumentGeneratorBundle\Contracts\GeneratorContext;

/**
 * This class is used to set generator context for the TwigToPdfGenerator, in case it needs to generate multiple pdf
 * page layouts and merge them into a single pdf file
 */
readonly class MultiLayoutTwigToPdfGeneratorContext implements GeneratorContext
{
    /**
     * @param TwigToPdfGeneratorContext[] $layouts
     */
    public function __construct(private array $layouts)
    {
        foreach ($layouts as $layout) {
            if (!$layout instanceof TwigToPdfGeneratorContext) {
                throw new InvalidArgumentException(
                    sprintf('All layouts must be instances of %s', TwigToPdfGeneratorContext::class)
                );
            }
        }
    }

    /**
     * @return TwigToPdfGeneratorContext[]
     */
    public function getLayouts(): array
    {
        return $this->layouts;
    }
}
