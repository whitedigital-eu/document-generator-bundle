<?php declare(strict_types = 1);

namespace WhiteDigital\DocumentGeneratorBundle\Generator;

use Exception;
use InvalidArgumentException;
use RuntimeException;
use Twig\Environment;
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
                return $this->pdf->htmlToPdf(
                    $this->twig->render($this->generatorContext->getTemplate(), $this->data),
                    $this->generatorContext->getHeaderTemplate()
                        ? $this->twig->render($this->generatorContext->getHeaderTemplate(), $this->data) : null,
                    $this->generatorContext->getFooterTemplate()
                        ? $this->twig->render($this->generatorContext->getFooterTemplate(), $this->data) : null,
                    true,
                    $this->generatorContext->getPdfConfiguration()
                );
            } elseif ($this->generatorContext instanceof MultiLayoutTwigToPdfGeneratorContext) {
                $data = [];
                foreach ($this->generatorContext->getLayouts() as $layout) {
                    $data[] = $this->pdf->htmlToPdf(
                        $this->twig->render($layout->getTemplate(), $this->data),
                        $layout->getHeaderTemplate()
                            ? $this->twig->render($layout->getHeaderTemplate(), $this->data) : null,
                        $layout->getFooterTemplate()
                            ? $this->twig->render($layout->getFooterTemplate(), $this->data) : null,
                        false,
                        $layout->getPdfConfiguration()
                    );
                }
                if ($data) {
                    return $this->pdf->mergePdf(...$data);
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
}
