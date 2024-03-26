<?php declare(strict_types = 1);

namespace WhiteDigital\DocumentGeneratorBundle\Generator;

use Exception;
use RuntimeException;
use Twig\Environment;
use WhiteDigital\DocumentGeneratorBundle\Contracts\Generator;
use WhiteDigital\DocumentGeneratorBundle\Service\HtmlToPdf;

class TwigToPdfGenerator implements Generator
{
    protected ?string $template = null;
    protected array $data = [];

    public function __construct(
        private readonly Environment $twig,
        private readonly HtmlToPdf $pdf,
    ) {
    }

    public function generate(): string
    {
        try {
            return $this->pdf->htmlToPdf($this->twig->render($this->template, $this->data));
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
}
