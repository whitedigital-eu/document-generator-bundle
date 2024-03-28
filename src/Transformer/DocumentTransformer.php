<?php declare(strict_types = 1);

namespace WhiteDigital\DocumentGeneratorBundle\Transformer;

use WhiteDigital\DocumentGeneratorBundle\Contracts\Transformer;

class DocumentTransformer implements Transformer
{
    public function getTransformedFields(mixed $input): array
    {
        return $input->getTemplateData();
    }
}
