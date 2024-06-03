<?php declare(strict_types = 1);

namespace WhiteDigital\DocumentGeneratorBundle\Generator;

use Exception;
use RuntimeException;
use Twig\Environment;
use WhiteDigital\DocumentGeneratorBundle\Contracts\Generator;
use WhiteDigital\DocumentGeneratorBundle\Contracts\GeneratorContext;
use WhiteDigital\DocumentGeneratorBundle\GeneratorContext\TwigToPdfGeneratorContext;
use WhiteDigital\DocumentGeneratorBundle\Service\HtmlToPdf;

class TwigToPdfGenerator implements Generator
{
    protected ?string $template = null;
    protected ?string $headerTemplate = null;
    protected ?string $footerTemplate = null;
    protected array $data = [];

    public function __construct(
        private readonly Environment $twig,
        private readonly HtmlToPdf $pdf,
    ) {
    }

    public function generate(): string
    {
        try {
            return $this->pdf->htmlToPdf(
                $this->twig->render($this->template, $this->data),
                $this->headerTemplate ? $this->twig->render($this->headerTemplate, $this->data) : null,
                $this->footerTemplate ? $this->twig->render($this->footerTemplate, $this->data) : null,
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

        return $this;
    }
}
