<?php declare(strict_types = 1);

namespace WhiteDigital\DocumentGeneratorBundle\Generator;

use Exception;
use Gotenberg\Exceptions\GotenbergApiErrored;
use Gotenberg\Exceptions\NoOutputFileInResponse;
use InvalidArgumentException;
use RuntimeException;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use WhiteDigital\DocumentGeneratorBundle\Contracts\Generator;
use WhiteDigital\DocumentGeneratorBundle\Contracts\GeneratorContext;
use WhiteDigital\DocumentGeneratorBundle\GeneratorContext\MultiLayoutTwigToPdfGeneratorContext;
use WhiteDigital\DocumentGeneratorBundle\GeneratorContext\TwigToPdfGeneratorContext;
use WhiteDigital\DocumentGeneratorBundle\Service\HtmlToPdf;

class TwigToPdfGenerator implements Generator
{
    protected array $data = [];
    protected TwigToPdfGeneratorContext|MultiLayoutTwigToPdfGeneratorContext|null $generatorContext = null;

    public function __construct(private readonly Environment $twig, private readonly HtmlToPdf $pdf)
    {
    }

    public function generate(): string
    {
        try {
            if ($this->generatorContext instanceof TwigToPdfGeneratorContext) {
                return $this->generatePdfWithContext($this->generatorContext);
            } elseif ($this->generatorContext instanceof MultiLayoutTwigToPdfGeneratorContext) {
                $data = [];
                foreach ($this->generatorContext->getLayouts() as $layout) {
                    $data[] = $this->generatePdfWithContext($layout, false);
                }
                if ($data) {
                    return $this->pdf->mergePdfs($data);
                }
            }
            throw new InvalidArgumentException('Invalid generator context');

        } catch (Exception $exception) {
            throw new RuntimeException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    public function setData(array $data): Generator
    {
        $this->data = $data;

        return $this;
    }

    public function setGeneratorContext(?GeneratorContext $context): Generator
    {
        if (!$context instanceof TwigToPdfGeneratorContext && !$context instanceof MultiLayoutTwigToPdfGeneratorContext) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid context type. Expected: "%s" or "%s", got: "%s"',
                    TwigToPdfGeneratorContext::class,
                    MultiLayoutTwigToPdfGeneratorContext::class,
                    get_debug_type($context)
                )
            );
        }
        $this->generatorContext = $context;

        return $this;
    }

    /**
     * @throws NoOutputFileInResponse
     * @throws RuntimeError
     * @throws LoaderError
     * @throws SyntaxError
     * @throws GotenbergApiErrored
     */
    protected function generatePdfWithContext(TwigToPdfGeneratorContext $context, bool $saveAsFile = true): string
    {
        return $this->pdf->htmlToPdf(
            html: $this->twig->render($context->getTemplate(), $this->data),
            headerHtml: $context->getHeaderTemplate()
                ? $this->twig->render($context->getHeaderTemplate(), $this->data) : null,
            footerHtml: $context->getFooterTemplate()
                ? $this->twig->render($context->getFooterTemplate(), $this->data) : null,
            saveAsFile: $saveAsFile,
            pdfConfiguration: $context->getPdfConfiguration()
        );
    }
}
