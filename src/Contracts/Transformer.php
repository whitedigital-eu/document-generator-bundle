<?php declare(strict_types = 1);

namespace WhiteDigital\DocumentGeneratorBundle\Contracts;

interface Transformer
{
    public function getTransformedFields(mixed $input): array;
}
