<?php declare(strict_types = 1);

namespace WhiteDigital\DocumentGeneratorBundle\Generator;

use Exception;
use RuntimeException;
use Symfony\Component\Translation\LocaleSwitcher;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use WhiteDigital\DocumentGeneratorBundle\Contracts\Generator;
use WhiteDigital\DocumentGeneratorBundle\Contracts\GeneratorContext;
use WhiteDigital\DocumentGeneratorBundle\GeneratorContext\LocaleAwareGeneratorContext;
use WhiteDigital\DocumentGeneratorBundle\GeneratorContext\TwigToPdfGeneratorContext;
use WhiteDigital\DocumentGeneratorBundle\Service\HtmlToPdf;

class TwigToPdfGenerator implements Generator
{
    protected ?string $template = null;
    protected ?string $headerTemplate = null;
    protected ?string $footerTemplate = null;
    protected ?string $locale = null;

    protected array $data = [];

    public function __construct(
        private readonly Environment $twig,
        private readonly HtmlToPdf $pdf,
        private readonly LocaleSwitcher $localeSwitcher,
    ) {
    }

    public function generate(): string
    {
        try {
            return $this->pdf->htmlToPdf(
                $this->render($this->template, $this->data),
                $this->headerTemplate ? $this->render($this->headerTemplate, $this->data) : null,
                $this->footerTemplate ? $this->render($this->footerTemplate, $this->data) : null,
            );
        } catch (Exception $exception) {
            throw new RuntimeException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    public function setTemplate(string $template): Generator
    {
        $this->template = $template;

        return $this;
    }

    public function setData(array $data): Generator
    {
        $this->data = $data;

        return $this;
    }

    public function setGeneratorContext(?GeneratorContext $context): Generator
    {
        if ($context instanceof TwigToPdfGeneratorContext) {
            $this->headerTemplate = $context->getHeaderTemplate();
            $this->footerTemplate = $context->getFooterTemplate();
        }

        if ($context instanceof LocaleAwareGeneratorContext) {
            $this->locale = $context->getLocale();
        }

        return $this;
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    private function render($name, array $context = []): string
    {
        if (null !== $this->locale) {
            $this->localeSwitcher->runWithLocale($this->locale, fn () => $this->twig->render($name, $context));
        }

        return $this->twig->render($name, $context);
    }
}
