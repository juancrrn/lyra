<?php 

namespace Juancrrn\Lyra\Common\View;

use InvalidArgumentException;
use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\TemplateUtils;
use Juancrrn\Lyra\Common\View\Common\FooterPartView;
use Juancrrn\Lyra\Common\View\Common\HeaderPartView;
use RuntimeException;

/**
 * Métodos relacionados con las vistas y la generación de contenido visible 
 * para el usuario en el navegador.
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class ViewManager
{

    /**
     * @var string $currentPageName     Título de la página actual.
     */
    private $currentPageName;

    /**
     * @var string $currentPageId       Id de la página actual.
     */
    private $currentPageId;

    /**
     * @var string $viewResourcesPath   Directorio donde se encuentran los
     *                                  ficheros comunes.
     */
    private $viewResourcesPath;

    /**
     * Subdirectorio para los elementos plantilla.
     */
    private const TEMPLATE_ELEMENT_SUBDIRECTORY = 'html_templates';

    /**
     * Valor de $_SESSION donde se almacenan los mensajes.
     */
    private const SESSION_MESSAGES = 'lyra_session_messages';

    /**
     * Array de elementos plantilla (<template>).
     */
    private $templateElements;

    /**
     * Constructor.
     * 
     * @param string $viewResourcesPath
     */
    public function __construct(string $viewResourcesPath)
    {
        $this->viewResourcesPath = $viewResourcesPath;

        $this->templateElements = array();
    }

    /*
     * 
     * Página actual
     * 
     */

    /**
     * Establece el nombre y el id de la página actual.
     * 
     * @param string $nombre Nombre de la página actual.
     * @param string $id Identificador de la página actual.
     */
    private function setCurrentPage(string $name, string $id): void
    {
        $this->currentPageName = $name;
        $this->currentPageId = $id;
    }

    /**
     * Devuelve el nombre de la página actual.
     * 
     * @return string Nombre de la página actual.
     */
    public function getCurrentPageName(): string
    {
        return $this->currentPageName;
    }

    /**
     * Devuelve el id de la página actual.
     * 
     * @return string Id de la página actual.
     */
    public function getCurrentPageId(): string
    {
        return $this->currentPageId;
    }

    /*
     * 
     * Cabecera y pie de página
     * 
     */

    /**
     * Genera la cabecera HTML y la incluye.
     */
    private function injectHeader(): void
    {
        (new HeaderPartView())->processContent();
    }

    /**
     * Genera el pie HTML y lo incluye.
     */
    private function injectFooter(): void
    {
        (new FooterPartView())->processContent();
    }

    /*
     * 
     * Mensajes para el usuario
     * 
     */

    /**
     * Añade un mensaje de error a la cola de mensajes para el usuario.
     * 
     * @param string $message Mensaje a mostrar al usuario.
     * @param string $header_location Opcional para redirigir al mismo tiempo 
     * que se encola el mensaje.
     */
    public function addErrorMessage(string $mensaje, string $header_location = null): void
    {
        $_SESSION[self::SESSION_MESSAGES][] = array(
            "tipo" => "error",
            "contenido" => $mensaje
        );

        if ($header_location !== null) {
            header("Location: " . App::getSingleton()->getUrl() . $header_location);
            die();
        }
    }
    
    /**
     * Añade un mensaje de éxito a la cola de mensajes para el usuario.
     * 
     * @param string $message Mensaje a mostrar al usuario.
     * @param string $header_location Opcional para redirigir al mismo tiempo 
     * que se encola el mensaje.
     */
    public function addSuccessMessage(string $mensaje, string $header_location = null): void
    {
        $_SESSION[self::SESSION_MESSAGES][] = array(
            "tipo" => "exito",
            "contenido" => $mensaje
        );

        if ($header_location !== null) {
            header("Location: " . App::getSingleton()->getUrl() . $header_location);
            die();
        }
    }

    /**
     * Comprueba si hay algún mensaje de error en la cola de mensajes.
     */
    public function anyErrorMessages(): bool
    {
        if (! empty($_SESSION[self::SESSION_MESSAGES])) {
            foreach ($_SESSION[self::SESSION_MESSAGES] as $mensaje) {
                if ($mensaje["tipo"] == "error") {
                    return true;
                }
            }
        }

        return false;
    }

    /*
     * 
     * Mensajes para el usuario (parte visible: toasts)
     * 
     */

    /**
     * Genera un elemento Bootstrap toast para mostrar un mensaje.
     */
    public function generateToast(string $tipo, string $contenido): string
    {
        $app = App::getSingleton();

        return $this->generateViewTemplateRender(
            self::TEMPLATE_ELEMENT_SUBDIRECTORY . DIRECTORY_SEPARATOR . 'template_toast',
            array(
                'autohide'  => $app->isDevMode() ? 'false' : 'true',
                'type'      => $tipo,
                'app-name'  => $app->getName(),
                'content'   => $contenido
            )
        );
    }

    /**
     * Imprime todos los mensajes de la cola de mensajes.
     */
    private function printToasts(): void
    {
        echo '<div id="toasts-container" aria-live="polite" aria-atomic="true">';

        if (! empty($_SESSION[self::SESSION_MESSAGES])) {
            foreach ($_SESSION[self::SESSION_MESSAGES] as $clave => $mensaje) {
                echo $this->generateToast($mensaje['tipo'], $mensaje['contenido']);

                // Eliminar mensaje de la cola tras mostrarlo.
                unset($_SESSION[self::SESSION_MESSAGES][$clave]);
            }
        }

        echo '</div>';
    }

    /**
     * Registra la plantilla de los toasts para el navegador e imprime las
     * que haya disponibles.
     */
    private function addToastTemplateAndPrint(): void
    {
        $this->addTemplateElement(
            'toast',
            'template_toast',
            array(
                'autohide'  => '',
                'type'      => '',
                'app-name'  => '',
                'content'   => ''
            )
        );

        $this->printToasts();
    }

    /*
     * 
     * Elementos plantilla de HTML (<template>)
     * 
     */

    /**
     * Añade un elemento plantilla de HTML (<template>) para que luego sea
     * añadido al código HTML y pueda ser clonado por un script en el navegador.
     */
    public function addTemplateElement(
        string $htmlId,
		string $fileName,
		array $filling
    ): void
    {
        $this->templateElements[] = array(
            'html_id' => $htmlId,
            'file_name' => $fileName,
            'filling' => $filling
        );
    }

    /**
     * Comprueba si hay algún elemento template guardado para inyectar.
     * 
     * @return bool
     */
    public function anyTemplateElements(): bool
    {
        if (! empty($this->templateElements)) 
            return true;

        return false;
    }

    /**
     * Genera un elemento <template> para luego insertarlo en el HTML.
     */
    private function generateTemplateElementRender(
        string $htmlId,
		string $fileName,
		array $filling
    ): string
    {
        $filledTemplate = $this->generateViewTemplateRender(
            self::TEMPLATE_ELEMENT_SUBDIRECTORY . DIRECTORY_SEPARATOR . $fileName,
            $filling
        );

        return <<< HTML
        <template id="$htmlId">
            $filledTemplate
        </template>
        HTML;
    }

    /**
     * Imprime todos los elementos <template> precargados.
     */
    private function printTemplateElements(): void
    {
        if ($this->anyTemplateElements()) {
            foreach ($this->templateElements as $element) {
                echo $this->generateTemplateElementRender(
                    $element['html_id'],
                    $element['file_name'],
                    $element['filling']
                );
            }
        }
    }

    /*
     * 
     * Renderizado de la vista
     * 
     */

    /**
     * Dibuja una vista completa y detiene la ejecución del script.
     */
    public function render(ViewModel $vista): void
    {
        $this->setCurrentPage($vista->getName(), $vista->getId());
        
        $this->injectHeader();

        $this->addToastTemplateAndPrint();

        echo <<< HTML
        <section id="main-container" class="container my-4 px-4">
            <article>
        HTML;

        $vista->processContent();

        echo <<< HTML
            </article>
        </section>
        HTML;

        $this->printTemplateElements();

        $this->injectFooter();

        //die(); // TODO No necesario
    }

    /**
     * Imprime una plantilla rellenada.
     * 
     * Ver ViewManager::generateTemplateRender().
     */
    public function renderTemplate(
		string $fileName,
		array $filling
	): void
	{
        echo $this->generateViewTemplateRender(
            $fileName,
            $filling
        );
	}

    /*
     * 
     * Elementos de menú
     * 
     */

    /**
     * Genera un item de una lista no ordenada (<li> de una <ul>) para el menú 
     * principal lateral.
     * 
     * Por defecto añade la ruta de la URL principal al principio del enlace.
     * 
     * @param string $url
     * @param string $titulo
     * @param string $paginaId Identificador de la página de destino, para saber
     *                         si es la actual.
     */
    public function generateMainMenuLink(string $viewClass): string
    {
        if (! class_exists($viewClass)) {
            throw new InvalidArgumentException('Specified view class ($viewClass = ' . $viewClass . ') does not exist.');
        }

        if (
            ! defined($viewClass . '::VIEW_ID') ||
            ! defined($viewClass . '::VIEW_NAME') ||
            ! defined($viewClass . '::VIEW_ROUTE')
        ) {
            throw new InvalidArgumentException('Specified view class ($viewClass = ' . $viewClass . ') must have VIEW_ID, VIEW_NAME and VIEW_ROUTE constants defined and public.');
        }

        $viewId = $viewClass::VIEW_ID;
        $viewName = $viewClass::VIEW_NAME;
        $viewRoute = $viewClass::VIEW_ROUTE;

        $appUrl = App::getSingleton()->getUrl();

        $activeClass = $this->getCurrentPageId() === $viewId ? 'active' : '';

        $classAttr = 'class="nav-link ' . $activeClass . '"';
        $hrefAttr = 'href="' . $appUrl . $viewRoute . '"';

        $a = <<< HTML
        <li class="nav-item"><a $classAttr $hrefAttr>$viewName</a></li>
        HTML;

        return $a;
    }

    /**
     * Genera un item de una lista no ordenada (<li> de una <ul>) para el menú 
     * de sesión de usuario.
     * 
     * Por defecto añade la ruta de la URL principal al principio del enlace.
     * 
     * @param string $content
     * @param string|null $paginaId Identificador de la página de destino, para 
     *                              saber si es la actual.
     */
    public function generateUserMenuItem(string $content): string
    {
        return <<< HTML
        <span class="nav-item">$content</span>
        HTML;
    }

    /*
     *
     * Auxiliares
     * 
     */

    /**
     * @param string $fileName
     * @param array $filling
     * 
     * @return string
     */
    public function generateViewTemplateRender(
		string $fileName,
		array $filling
    ): string
    {
        return TemplateUtils::generateTemplateRender(
            $fileName,
            $filling,
            $this->viewResourcesPath
        );
    }
}