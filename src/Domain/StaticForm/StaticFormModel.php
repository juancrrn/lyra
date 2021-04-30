<?php

/**
 * Modelo base para de formularios HTML normales
 * 
 * Los formularios deben extender esta clase, que además ofrece funcionalidad
 * de gestión.
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

namespace Juancrrn\Lyra\Domain\StaticForm;

use Juancrrn\Lyra\Common\App;

abstract class StaticFormModel
{

    /**
     * @var string $id          Cadena utilizada como valor del atributo "id" de
     *                          la etiqueta <form> asociada al formulario y como 
     *                          parámetro a comprobar para verificar que el
     *                          usuario ha enviado el formulario.
     */
    private $id;

    /**
     * @var string $actionUrl   URL asociada al atributo "action" de la etiqueta 
     *                          <form> del formulario y que procesará el envío
     *                          del formulario.
     */
    private $actionUrl;

    /**
     * @var string $html        Almacena el HTML para generar el formulario. Que
     *                          el HTML no se imprima directamente y se pueda
     *                          procesar el formulario al inicio del script de
     *                          vista, nos permite adelantar eventos y, por
     *                          ejemplo, usar el mecanismo de mensajes de
     *                          ViewManager.
     */
    private $html = '';

    /**
     * @var string CSRF_PREFIX           CSRF prefix for $_SESSION storing.
     * @var string CSRF_TOKEN_FIELD      CSRF token field name.
     */
    private const CSRF_PREFIX = 'csrf';
    private const CSRF_TOKEN_FIELD = 'csrf-token';

    /**
     * Crea un nuevo formulario.
     * 
     * @param string $id
     *
     * @param array $opciones (Ver más arriba.)
     */
    public function __construct(string $id, ?array $options = array())
    {
        $this->id = $id;
        
        if (isset($options['action'])) {
            $options['action'] = App::getSingleton()->getUrl() . $options['action'];

            $defaultOptions = array();
        } else {
            $defaultOptions = array('action' => null);
        }

        $options = array_merge($defaultOptions, $options);

        $this->actionUrl   = $options['action'];
        
        if (! $this->actionUrl) {
            $this->actionUrl = htmlentities($_SERVER['PHP_SELF']);
        }
    }
  
    /**
     * Procesa el envío de un formulario.
     */
    public function handle()
    {   
        if ($this->isSent($_POST)) {
            $submittedCsrfToken = $_POST[self::CSRF_TOKEN_FIELD] ?? null;

            if ($this->CsrfValidateToken($submittedCsrfToken)) {
                $this->process($_POST);
            } else {
                App::getSingleton()
                    ->getViewManagerInstance()
                    ->addErrorMessage('Hubo un fallo en una verificación de seguridad. Por favor, vuelve a intentarlo.');
            }
        }  
    }
  
    /**
     * Verifica si el usuario ha enviado el formulario y para ello comprueba si 
     * existe el parámetro $id en $params.
     *
     * @param array $params Array que contiene los datos recibidos en el envío 
     *                      formulario.
     *
     * @return bool         Devuelve true si $id existe como clave en 
     *                      $params.
     */
    private function isSent(& $params)
    {
        return isset($params["action"]) && $params["action"] == $this->id;
    } 

    /**
     * Genera el HTML necesario para presentar los campos del formulario.
     *
     * @param array $preloadedData   Datos iniciales para los campos del 
     *                               formulario (normalmente $_POST).
     * 
     * @return string                HTML asociado a los campos del formulario.
     */
    protected function generateFields(array & $preloadedData = array()): string
    {
        return '';
    }

    /**
     * Procesa los datos del formulario.
     * 
     * Durante el procesamiento del formulario pueden producirse errores, que 
     * serán gestionados por el mecanismo de mensajes definido en ViewManager.
     * 
     * En tal caso, es la propia función la que vuelve a generar el formulario
     * con los datos iniciales.
     *
     * @param array $postedData Datos enviado por el usuario (normalmente $_POST).
     */
    protected function process(array & $postedData): void
    {
        return;
    }

    /**
     * Función que genera el HTML necesario para el formulario.
     *
     * @param string? $preloadedData Array con los valores por defecto de los campos 
     *                       del formulario.
     *
     * @return string        HTML asociado al formulario.
     */
    public function initialize(array & $preloadedData = array()): void
    {
        $campos = $this->generateFields($preloadedData);

        $actionUrl = $this->actionUrl;
        $id = $this->id;
        $csrfTokenFieldName = self::CSRF_TOKEN_FIELD;
        $csrfToken = $this->CsrfGenerateToken();

        $this->html = <<< HTML
        <form method="post" action="$actionUrl" id="$id" class="default-form">
            <input type="hidden" name="$csrfTokenFieldName" value="$csrfToken">
            <input type="hidden" name="action" value="$id" />
            $campos
        </form>
        HTML;
    }

    /**
     * Imprime el HTML del formulario.
     */
    public function render(): void
    {
        echo $this->html;
    }

    /**
     * Devuelve el HTML del formulario.
     */
    public function getHtml(): string
    {
        return $this->html;
    }

    /**
     * Generates a CSRF token and stores it in $_SESSION
     * 
     * @return string Generated token
     */
    private function CsrfGenerateToken(): string
    {
        $token = hash('sha512', mt_rand(0, mt_getrandmax()));

        $_SESSION[self::CSRF_PREFIX . '_' . $this->id] = $token;

        return $token;
    }

    /**
     * Validates a CSRF token
     * 
     * @param null|string $token Token to be validated
     * 
     * @return bool True if valid, else false
     */
    private function CsrfValidateToken(null|string $token): bool
    {
        if (! $token) return false;

        if (isset($_SESSION[self::CSRF_PREFIX . '_' . $this->id])
            && $_SESSION[self::CSRF_PREFIX . '_' . $this->id] === $token) {
            
            unset($_SESSION[self::CSRF_PREFIX . '_' . $this->id]);

            return true;
        }

        return false;
    }
}

?>