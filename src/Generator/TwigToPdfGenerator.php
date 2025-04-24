<?php declare(strict_types = 1);

namespace WhiteDigital\DocumentGeneratorBundle\Generator;

use Gotenberg\Exceptions\GotenbergApiErrored;
use Gotenberg\Exceptions\NoOutputFileInResponse;
use InvalidArgumentException;
use Symfony\Component\Translation\LocaleSwitcher;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use WhiteDigital\DocumentGeneratorBundle\Contracts\Generator;
use WhiteDigital\DocumentGeneratorBundle\Contracts\GeneratorContext;
use WhiteDigital\DocumentGeneratorBundle\GeneratorContext\Traits\LocaleAware;
use WhiteDigital\DocumentGeneratorBundle\GeneratorContext\Traits\MultiLayoutTwigToPdf;
use WhiteDigital\DocumentGeneratorBundle\GeneratorContext\Traits\TwigToPdf;
use WhiteDigital\DocumentGeneratorBundle\Service\HtmlToPdf;

use function class_uses;

class TwigToPdfGenerator implements Generator
{
    protected ?string $locale = null;
    protected ?GeneratorContext $context = null;

    protected array $data = [];

    public function __construct(
        private readonly Environment $twig,
        private readonly HtmlToPdf $pdf,
        private readonly LocaleSwitcher $localeSwitcher,
    ) {
    }

    /**
     * @throws NoOutputFileInResponse
     * @throws RuntimeError
     * @throws LoaderError
     * @throws SyntaxError
     * @throws GotenbergApiErrored
     */
    public function generate(): string
    {
        if ($this->usesTrait(MultiLayoutTwigToPdf::class)) {
            $data = [];
            foreach ($this->context->getLayouts() as $layout) {
                $data[] = $this->generatePdfWithContext($layout, false);
            }

            if ($data) {
                return $this->pdf->mergePdfs($data);
            }
        }

        if ($this->usesTrait(TwigToPdf::class)) {
            return $this->generatePdfWithContext($this->context);
        }

        throw new InvalidArgumentException('Invalid generator context');
    }

    public function setData(array $data): Generator
    {
        $this->data = $data;

        return $this;
    }

    public function setGeneratorContext(?GeneratorContext $context): Generator
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @throws NoOutputFileInResponse
     * @throws RuntimeError
     * @throws LoaderError
     * @throws SyntaxError
     * @throws GotenbergApiErrored
     */
    protected function generatePdfWithContext(GeneratorContext $context, bool $saveAsFile = true): string
    {
        return $this->pdf->htmlToPdf(
            html: $this->render($context->getTemplate(), $this->data),
            headerHtml: $context->getHeaderTemplate()
                ? $this->render($context->getHeaderTemplate(), $this->data) : null,
            footerHtml: $context->getFooterTemplate()
                ? $this->render($context->getFooterTemplate(), $this->data) : null,
            saveAsFile: $saveAsFile,
            pdfConfiguration: $context->getPdfConfiguration(),
        );
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    protected function render(string $name, array $context): string
    {
        if ($this->usesTrait(LocaleAware::class)) {
            return $this->localeSwitcher->runWithLocale($this->context->getLocale(), fn () => $this->twig->render($name, $context));
        }

        return $this->twig->render($name, $context);
    }

    private function classUsesRecursive(string $class): array
    {
        $results = [];

        do {
            foreach (class_uses($class) as $trait) {
                $results[$trait] = $trait;
                $results += $this->traitUsesRecursive($trait);
            }
        } while ($class = get_parent_class($class));

        return $results;
    }

    private function traitUsesRecursive(string $trait): array
    {
        $traits = class_uses($trait);
        foreach ($traits as $nestedTrait) {
            $traits += $this->traitUsesRecursive($nestedTrait);
        }

        return $traits;
    }

    private function usesTrait(string $trait): bool
    {
        return in_array($trait, $this->classUsesRecursive($this->context::class), true);
    }
}
