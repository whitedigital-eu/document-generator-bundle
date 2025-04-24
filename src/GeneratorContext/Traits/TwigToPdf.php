<?php declare(strict_types = 1);

namespace WhiteDigital\DocumentGeneratorBundle\GeneratorContext\Traits;

use Gotenberg\Modules\ChromiumPdf;

trait TwigToPdf
{
    protected string $template;
    protected ?string $headerTemplate = null;
    protected ?string $footerTemplate = null;
    protected ?ChromiumPdf $pdfConfiguration = null;

    public function getHeaderTemplate(): ?string
    {
        return $this->headerTemplate;
    }

    public function setHeaderTemplate(?string $headerTemplate): static
    {
        $this->headerTemplate = $headerTemplate;

        return $this;
    }

    public function getFooterTemplate(): ?string
    {
        return $this->footerTemplate;
    }

    public function setFooterTemplate(?string $footerTemplate): static
    {
        $this->footerTemplate = $footerTemplate;

        return $this;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function setTemplate(string $template): static
    {
        $this->template = $template;

        return $this;
    }

    public function getPdfConfiguration(): ChromiumPdf
    {
        return $this->pdfConfiguration;
    }

    public function setPdfConfiguration(ChromiumPdf $pdfConfiguration): static
    {
        $this->pdfConfiguration = $pdfConfiguration;

        return $this;
    }
}
