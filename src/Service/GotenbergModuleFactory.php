<?php declare(strict_types = 1);

namespace WhiteDigital\DocumentGeneratorBundle\Service;

use Gotenberg\Gotenberg;
use Gotenberg\Modules\Chromium;
use Gotenberg\Modules\ChromiumPdf;
use Gotenberg\Modules\LibreOffice;
use Gotenberg\Modules\PdfEngines;

class GotenbergModuleFactory
{
    public function __construct(private string $pdfHost, ?int $pdfPort = null)
    {
        if ($pdfPort) {
            $this->pdfHost .= ':' . $pdfPort;
        }
    }

    public function createChromium(): Chromium
    {
        return Gotenberg::chromium($this->pdfHost);
    }

    public function createLibreOffice(): LibreOffice
    {
        return Gotenberg::libreOffice($this->pdfHost);
    }

    public function createPdfEngines(): PdfEngines
    {
        return Gotenberg::pdfEngines($this->pdfHost);
    }

    public function createPdf(): ChromiumPdf
    {
        return $this->createChromium()->pdf()->preferCssPageSize();
    }
}
