<?php declare(strict_types = 1);

namespace WhiteDigital\DocumentGeneratorBundle\GeneratorContext;

use WhiteDigital\DocumentGeneratorBundle\Contracts\GeneratorContext;

class DocumentGeneratorContext implements GeneratorContext
{
    use Traits\TwigToPdf;
}
