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
        ?string      $header = null,
        ?string      $footer = null,
        bool         $save = true,
        ?ChromiumPdf $pdfConfiguration = null
    ): string
    {
        if (!$pdfConfiguration) {
            $pdfConfiguration = $this->gotenbergModuleFactory->createChromium()->pdf()->margins(0, 0, 0, 0);
        }

        if (null !== $header) {
            $pdfConfiguration->header(Stream::string('header.html', $header));
        }

        if (null !== $footer) {
            $pdfConfiguration->footer(Stream::string('footer.html', $footer));
        }

        $request = $pdfConfiguration->html(Stream::string('index.html', $html));

        if ($save) {
            $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR;

            return $dir . Gotenberg::save($request, $dir);
        }

        return Gotenberg::send($request)->getBody()->getContents();
    }

    /**
     * @throws NoOutputFileInResponse
     * @throws GotenbergApiErrored
     */
    public function mergePdf(array $pdfs, bool $save = true): string
    {

        $request = $this->gotenbergModuleFactory
            ->createPdfEngines()
            ->merge(
                ...array_map(
                    static fn(string $pdf, int $idx) => Stream::string("pdf-{$idx}.pdf", $pdf), $pdfs, array_keys($pdfs)
                )
            );

        if ($save) {
            $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR;

            return $dir . Gotenberg::save($request, $dir);
        }

        return Gotenberg::send($request)->getBody()->getContents();
    }
}
