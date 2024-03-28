<?php declare(strict_types = 1);

namespace WhiteDigital\DocumentGeneratorBundle\Task;

use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Vich\UploaderBundle\FileAbstraction\ReplacingFile;
use WhiteDigital\DocumentGeneratorBundle\Contracts\Generator;
use WhiteDigital\DocumentGeneratorBundle\Contracts\Task;
use WhiteDigital\DocumentGeneratorBundle\Contracts\Transformer;
use WhiteDigital\DocumentGeneratorBundle\Entity\Document;
use WhiteDigital\EntityResourceMapper\EntityResourceMapperBundle;
use WhiteDigital\StorageItemResource\Entity\StorageItem;

use function array_merge;
use function count;
use function ctype_digit;
use function dump;
use function get_debug_type;
use function implode;
use function is_array;
use function ltrim;
use function preg_match_all;
use function str_contains;

abstract class AbstractDocumentTask implements Task
{
    protected ?string $name = null;
    protected ?string $templatePath = null;
    protected ?string $type = null;
    protected ?string $inputType = null;

    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly Generator $generator,
        protected readonly Transformer $transformer,
    ) {
    }

    final public function generate(mixed $input): Document
    {
        if(get_debug_type($input) !== $this->getInputType()) {
            throw new InvalidArgumentException();
        }

        $data = $this->getTransformer()->getTransformedFields($input);
        $this->validate($data);

        $result = $this->getGenerator()
            ->setData($data)
            ->setTemplate($this->getTemplatePath())
            ->generate();

        $storageItem = (new StorageItem())
            ->setFile(new ReplacingFile($result));
        $this->em->persist($storageItem);

        $document = (new Document())
            ->setType($this->getType())
            ->setSourceData(array_merge($this->getRequiredFields(), $this->getOptionalFields()))
            ->setTemplateData($data)
            ->setFile($storageItem);

        $this->em->persist($document);
        $this->em->flush();

        return $document;
    }

    public function getOptionalFields(): array
    {
        return [];
    }

    public function getGenerator(): Generator
    {
        return $this->generator;
    }

    public function getTransformer(): Transformer
    {
        return $this->transformer;
    }

    protected function validate(array $data): void
    {
        $count = 0;
        $invalid = [];
        $fullData = array_merge($this->getRequiredFields(), $this->getOptionalFields());

        $dataDump = self::makeOneDimension($data, onlyLast: true);
        $fullDump = self::makeOneDimension($fullData, onlyLast: true);
        $requiredDump = self::makeOneDimension($this->getRequiredFields(), onlyLast: true);
        $requiredCount = count($requiredDump);

        foreach ($dataDump as $key => $value) {
            $check = $key;
            if(preg_match('/[0-9]/', $check) > 0){
                preg_match_all('/\d+/', $check, $matches);
                $check = preg_replace("/\d/", '0', $check);
                if('0' !== $matches[0][0]){
                    $requiredCount++;
                }
            }
            if (!isset($fullDump[$check])) {
                $invalid[] = $check;
                continue;
            }

            if (get_debug_type($value) !== $fullDump[$check]) {
                throw new InvalidArgumentException();
            }
            $count += (int) isset($requiredDump[$check]);
        }

        if ($requiredCount !== $count) {
            throw new InvalidArgumentException();
        }

        if ([] !== $invalid) {
            throw new InvalidArgumentException(implode(', ', $invalid));
        }
    }

    public static function makeOneDimension(array $array, string $base = '', string $separator = '.', bool $onlyLast = false, int $depth = 0, int $maxDepth = PHP_INT_MAX, array $result = []): array
    {
        if ($depth <= $maxDepth) {
            foreach ($array as $key => $value) {
                $key = ltrim(string: $base . '.' . $key, characters: '.');

                if (self::isAssociative(array: $value)) {
                    $result = self::makeOneDimension(array: $value, base: $key, separator: $separator, onlyLast: $onlyLast, depth: $depth + 1, maxDepth: $maxDepth, result: $result);

                    if ($onlyLast) {
                        continue;
                    }
                }

                $result[$key] = $value;
            }
        }

        return $result;
    }

    private static function isAssociative(mixed $array): bool
    {
        if (!is_array(value: $array) || [] === $array) {
            return false;
        }

        return true;
    }
}
