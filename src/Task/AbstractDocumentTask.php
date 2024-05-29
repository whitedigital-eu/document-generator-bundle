<?php declare(strict_types = 1);

namespace WhiteDigital\DocumentGeneratorBundle\Task;

use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Vich\UploaderBundle\FileAbstraction\ReplacingFile;
use WhiteDigital\DocumentGeneratorBundle\Contracts\Generator;
use WhiteDigital\DocumentGeneratorBundle\Contracts\Task;
use WhiteDigital\DocumentGeneratorBundle\Contracts\Transformer;
use WhiteDigital\DocumentGeneratorBundle\Entity\Document;
use WhiteDigital\StorageItemResource\Entity\StorageItem;

use function array_diff;
use function array_diff_key;
use function array_flip;
use function array_key_exists;
use function array_keys;
use function count;
use function get_debug_type;
use function implode;
use function is_array;
use function ltrim;
use function preg_match;
use function preg_match_all;
use function preg_replace;
use function sprintf;

abstract class AbstractDocumentTask implements Task
{
    protected ?string $name = null;
    protected ?string $templatePath = null;
    protected ?string $type = null;
    protected ?string $inputType = null;
    protected mixed $input = null;

    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly Generator $generator,
        protected readonly Transformer $transformer,
    ) {
    }

    final public function generate(mixed $input): Document
    {
        $this->input = $input;
        if (get_debug_type($input) !== $this->getInputType()) {
            throw new InvalidArgumentException(sprintf('Incompatible input type. Expected: "%s", got: "%s"', $this->getInputType(), get_debug_type($input)));
        }

        $data = $this->getTransformer()->getTransformedFields($input);
        $this->validate($data);

        $result = $this->getGenerator()
            ->setData($data)
            ->setTemplate($this->getTemplatePath())
            ->setGeneratorContext($this->getGeneratorContext())
            ->generate();

        $storageItem = (new StorageItem())->setFile(new ReplacingFile($result));
        $this->em->persist($storageItem);

        $sourceDump = self::makeOneDimension($this->getTransformerFields(), onlyLast: true);
        $dataDump = self::makeOneDimension($data, onlyLast: true);
        $extraKeys = array_diff(array_keys($sourceDump), array_keys($dataDump));
        $sourceDump = array_diff_key($sourceDump, array_flip($extraKeys));

        $document = (new Document())
            ->setType($this->getType())
            ->setSourceData(self::unpack($sourceDump))
            ->setTemplateData($data)
            ->setFile($storageItem)
            ->setTemplatePath($this->getTemplatePath());

        $this->em->persist($document);
        $this->em->flush();

        return $document;
    }

    public function getGenerator(): Generator
    {
        return $this->generator;
    }

    public function getGeneratorContext(): mixed
    {
        return null;
    }

    public function getTransformer(): Transformer
    {
        return $this->transformer;
    }

    public function getInput(): mixed
    {
        return $this->input;
    }

    protected function validate(array $data): void
    {
        $count = 0;
        $invalid = [];

        $dataDump = self::makeOneDimension($data, onlyLast: true);
        $fullDump = self::makeOneDimension($this->getTransformerFields(), onlyLast: true);
        $requiredCount = count($fullDump);

        foreach ($dataDump as $key => $value) {
            $check = $key;
            if (preg_match('/\.\d+\./', $check) && preg_match('/[0-9]/', $check) > 0) {
                preg_match_all('/\d+/', $check, $matches);
                $check = preg_replace("/\d/", '0', $check);
                if ('0' !== $matches[0][0] && isset($fullDump[$check])) {
                    $requiredCount++;
                }
            }
            if (!isset($fullDump[$check])) {
                $invalid[] = $check;
                continue;
            }

            if (get_debug_type($value) !== $fullDump[$check]) {
                throw new InvalidArgumentException(sprintf('Incompatible input type. Expected "%s", got "%s"', $fullDump[$check], get_debug_type($value)));
            }

            $count += (int) isset($fullDump[$check]);
        }

        if ([] !== $invalid) {
            throw new InvalidArgumentException(sprintf('Invalid mapping found: "%s"', implode(', ', $invalid)));
        }

        foreach ($fullDump as $key => $value) {
            $check = $key;
            if (preg_match('/\.\d+\./', $check) && preg_match('/[0-9]/', $check) > 0) {
                preg_match_all('/\d+/', $check, $matches);
                $check = preg_replace("/\d/", '0', $check);
                if (!array_key_exists($check, $dataDump)) {
                    $requiredCount--;
                }
            }
        }

        if ($requiredCount !== $count) {
            throw new InvalidArgumentException(sprintf('Missing required fields: "%s"', implode(', ', array_diff(array_keys($fullDump), array_keys($dataDump)))));
        }
    }

    /*
     * unpack one-dimensional array into multi-dimensional array
     * ['one.two.three' => 'four'] becomes ['one' => ['two' => ['three' => 'four']]]
     */
    private static function unpack(array $oneDimension): array
    {
        $multiDimension = [];

        foreach ($oneDimension as $key => $value) {
            $path = explode('.', $key);
            $temp = &$multiDimension;
            foreach ($path as $segment) {
                if (!isset($temp[$segment])) {
                    $temp[$segment] = [];
                }
                $temp = &$temp[$segment];
            }
            $temp = $value;
        }

        return $multiDimension;
    }

    /*
     * Merge multi-dimensional array into one-dimensional array
     * ['one' => ['two' => ['three' => 'four']]] becomes ['one.two.three' => 'four']
     */
    private static function makeOneDimension(array $array, string $base = '', string $separator = '.', bool $onlyLast = false, int $depth = 0, int $maxDepth = PHP_INT_MAX, array $result = []): array
    {
        if ($depth <= $maxDepth) {
            foreach ($array as $key => $value) {
                $key = ltrim($base . '.' . $key, '.');

                if (self::isAssociative($value)) {
                    $result = self::makeOneDimension($value, $key, $separator, $onlyLast, $depth + 1, $maxDepth, $result);

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
        if (!is_array($array) || [] === $array) {
            return false;
        }

        return true;
    }
}
