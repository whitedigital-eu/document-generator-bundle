<?php declare(strict_types = 1);

namespace WhiteDigital\DocumentGeneratorBundle\Service;

use Gotenberg\Exceptions\GotenbergApiErrored;
use Gotenberg\Exceptions\NoOutputFileInResponse;
use Gotenberg\Gotenberg;
use Gotenberg\Stream;

use function sys_get_temp_dir;

use const DIRECTORY_SEPARATOR;

class HtmlToPdf
{
    public function __construct(private string $pdfHost, ?int $pdfPort = null)
    {
        if ($pdfPort) {
            $this->pdfHost .= ':' . $pdfPort;
        }
    }

    /**
     * @throws NoOutputFileInResponse
     * @throws GotenbergApiErrored
     */
    public function htmlToPdf(string $html, ?string $header = null, ?string $footer = null, bool $save = true): string
    {
        $request = Gotenberg::chromium($this->pdfHost)->pdf()->margins(0, 0, 0, 0);

        if (null !== $header) {
            $request->header(Stream::string('header.html', $header));
        }

        if (null !== $footer) {
            $request->footer(Stream::string('footer.html', $footer));
        }

        $request = $request->html(Stream::string('index.html', $html));

        if ($save) {
            $dir = sys_get_temp_dir();
            if (is_dir('/dev/shm')) {
                $dir = '/dev/shm';
            }
            $dir .= DIRECTORY_SEPARATOR;

            return $dir . Gotenberg::save($request, $dir);
        }

        return Gotenberg::send($request)->getBody()->getContents();
    }
}
