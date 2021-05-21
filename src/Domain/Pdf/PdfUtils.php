<?php

namespace Juancrrn\Lyra\Domain\Pdf;

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\TemplateUtils;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\HTMLParserMode;

/**
 * Utilidades de PDF
 *
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class PdfUtils
{

    /**
     * Ruta relativa del directorio de plantillas HTML de los PDF.
     */
    private const PDF_RESOURCES_PATH = 'resources/pdf';

	/**
	 * Initializes the mPDF configuration with the application instance's 
	 * settings.
	 * 
	 * @return Mpdf
	 */
	public static function initialize(): Mpdf
    {
        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];
        
        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new Mpdf([
            'fontDir' => array_merge($fontDirs, [
                App::getSingleton()->getRoot() . self::PDF_RESOURCES_PATH . '/fonts'
            ]),
            'fontdata' => $fontData + [
                'montserrat' => [
                    'R' => 'Montserrat-Regular.ttf',
                    'I' => 'Montserrat-Italic.ttf',
                    'B' => 'Montserrat-Bold.ttf',
                    'BI' => 'Montserrat-BoldItalic.ttf',
                ]
            ],
            'default_font' => 'montserrat',
            'margin-top' => 30
        ]);

        return $mpdf;
    }

    private static function generateGenericPdf(): Mpdf
    {
        $app = App::getSingleton();

        $mpdf = self::initialize();
        
        $mpdf->SetHTMLHeader(self::generatePdfTemplateRender(
            'common/part_header',
            array(
                'app-name' => $app->getName(),
                'app-url' => $app->getUrl()
            )
        ));
        
        $mpdf->SetHTMLFooter(self::generatePdfTemplateRender(
            'common/part_footer',
            array(
                'app-name' => $app->getName(),
                'app-url' => $app->getUrl()
            )
        ));
        
        $pdfCss = file_get_contents(realpath(App::getSingleton()->getRoot() . self::PDF_RESOURCES_PATH . '/common/part_styles.css'));

        $mpdf->WriteHTML($pdfCss, HTMLParserMode::HEADER_CSS);
        
        $mpdf->AddPage(mgt: 30, mgb: 20, mgl: 10, mgr: 10);

        return $mpdf;
    }

    public static function renderUserRegistrationRequestPdf(): void
    {
        $app = App::getSingleton();

        $mpdf = self::generateGenericPdf();

        $mpdf->SetTitle('Solicitud de registro de usuario - ' . $app->getName());

        // TODO content
        $mpdf->WriteHTML(self::generatePdfTemplateRender(
            'auth/doc_registration_request',
            array(
                'app-name' => $app->getName(),
                'app-url' => $app->getUrl()
            )
        ));

        $mpdf->Output('lyra-solicitud-registro-usuario.pdf', Destination::INLINE);
    }

	/**
	 * Renderiza el contenido de un documento a partir de una plantilla y un
	 * relleno.
	 * 
	 * @param string $fileName
	 * @param string $filling
	 * 
	 * @return string
	 */
	private static function generatePdfTemplateRender(
		string $fileName,
		array $filling
	): string
	{
		return TemplateUtils::fillTemplate(
			$fileName,
			$filling,
			realpath(App::getSingleton()->getRoot() . self::PDF_RESOURCES_PATH)
		);
	}
}