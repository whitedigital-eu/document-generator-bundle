<?php

namespace WhiteDigital\DocumentGeneratorBundle\Service;

use Gotenberg\Gotenberg;
use Gotenberg\Modules\Chromium;
use Gotenberg\Modules\LibreOffice;
use Gotenberg\Modules\PdfEngines;

/**
 * This class is a wrapper class around helper to create different gotenberg modules with correct host and port from
 * application configuration
 * @see Gotenberg
 */
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
}