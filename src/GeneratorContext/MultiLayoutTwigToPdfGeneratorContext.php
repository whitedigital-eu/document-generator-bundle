<?php declare(strict_types=1);

namespace WhiteDigital\DocumentGeneratorBundle\GeneratorContext;

use InvalidArgumentException;
use WhiteDigital\DocumentGeneratorBundle\Contracts\GeneratorContext;

/**
 * This class is used to add additional context to the MultiLayoutTwigToPdfGenerator, such as header and footer templates.
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
                    sprintf(
                        'All layouts must be instances of %s',
                        TwigToPdfGeneratorContext::class)
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
