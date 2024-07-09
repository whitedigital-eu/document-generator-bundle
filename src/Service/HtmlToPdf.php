<?php declare(strict_types=1);

namespace WhiteDigital\DocumentGeneratorBundle\Service;

use Gotenberg\Exceptions\GotenbergApiErrored;
use Gotenberg\Exceptions\NoOutputFileInResponse;
use Gotenberg\Gotenberg;
use Gotenberg\Modules\ChromiumPdf;
use Gotenberg\Stream;

use function sys_get_temp_dir;

use const DIRECTORY_SEPARATOR;

readonly class HtmlToPdf
{
    public function __construct(private GotenbergModuleFactory $gotenbergModuleFactory)
    {
    }

    /**
     * @throws NoOutputFileInResponse
     * @throws GotenbergApiErrored
     */
    public function htmlToPdf(
        string       $html,
        ?string      $headerHtml = null,
        ?string      $footerHtml = null,
        bool         $saveAsFile = true,
        ?ChromiumPdf $pdfConfiguration = null
    ): string
    {
        if (!$pdfConfiguration) {
            $pdfConfiguration = $this->gotenbergModuleFactory->createPdf();
        }

        if (null !== $headerHtml) {
            $pdfConfiguration->header(Stream::string('header.html', $headerHtml));
        }

        if (null !== $footerHtml) {
            $pdfConfiguration->footer(Stream::string('footer.html', $footerHtml));
        }

        $request = $pdfConfiguration->html(Stream::string('index.html', $html));

        if ($saveAsFile) {
            $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR;

            return $dir . Gotenberg::save($request, $dir);
        }

        return Gotenberg::send($request)->getBody()->getContents();
    }

    /**
     * @throws NoOutputFileInResponse
     * @throws GotenbergApiErrored
     */
    public function mergePdfs(array $pdfDataStrings, bool $save = true): string
    {
        $request = $this->gotenbergModuleFactory
            ->createPdfEngines()
            ->merge(
                ...array_map(
                    static fn(string $pdf, int $idx) => Stream::string("pdf-$idx.pdf", $pdf),
                    $pdfDataStrings,
                    array_keys($pdfDataStrings)
                )
            );

        if ($save) {
            $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR;

            return $dir . Gotenberg::save($request, $dir);
        }

        return Gotenberg::send($request)->getBody()->getContents();
    }
}
