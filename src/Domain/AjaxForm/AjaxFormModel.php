<?php

namespace Juancrrn\Lyra\Domain\AjaxForm;

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\Http;
use stdClass;

/**
 * Modelo base para de formularios AJAX
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

abstract class AjaxFormModel
{

    /**
     * @var string $id                  Identificador del formulario.
     * @var string FORM_ID_FIELD        Nombre del atributo HTML para $id.
     */
    private $id = null;
    private const FORM_ID_FIELD = 'form-id';

    /**
     * @var string $formName            Nombre del formulario (para la ventana
     *                                  modal).
     */
    private $formName = null;

    /**
     * @var string $targetObjectName        Nombre del objeto que modifica el
     *                                      formulario.
     * @var string TARGET_CLASS_NAME_FIELD  Nombre del atributo HTML para
     *                                      $targetObjectName.
     */
    private $targetObjectName = null;
    private const TARGET_CLASS_NAME_FIELD = 'ajax-target-object-name';

    /**
     * @var string $readOnly        Indica si el formulario está bloqueado y no
     *                              espera ningún envío.
     * @var string READ_ONLY_FIELD  Nombre del atributo HTML para $readOnly.
     */
    private $readOnly = false;
    private const READ_ONLY_FIELD = 'ajax-read-only';

    /**
     * @var string $submitUrl       URL de envío del formulario.
     * @var string SUBMIT_URL_FIELD Nombre del atributo HTML para $submitUrl.
     */
    private $submitUrl = null;
    private const SUBMIT_URL_FIELD = 'ajax-submit-url';

    /**
     * @var string $onSuccessEventName          Mombre del evento ejecutado
     *                                          cuando hay una respuesta AJAX
     *                                          satisfactoria.
     * @var string ON_SUCCESS_EVENT_NAME_FIELD  Nombre del atributo HTML para
     *                                          $onSuccessEventName.
     * @var string $onSuccessTarget             Identificador del elemento sobre
     *                                          el que se ejecutará el evento.
     * @var string ON_SUCCESS_TARGET_FIELD      Nombre del atributo HTML para
     *                                          $onSuccessTarget.
     */
    private $onSuccessEventName = null;
    private const ON_SUCCESS_EVENT_NAME_FIELD = 'ajax-on-success-event-name';
    private $onSuccessEventTarget = null;
    private const ON_SUCCESS_EVENT_TARGET_FIELD = 'ajax-on-success-event-target';
    private $expectedSubmitMethod = null;
    private const EXPECTED_SUBMIT_METHOD_FIELD = 'ajax-submit-method';

    /**
     * @var string CSRF_PREFIX           CSRF prefix for $_SESSION storing.
     * @var string CSRF_TOKEN_FIELD      CSRF token field name.
     */
    private const CSRF_PREFIX = 'csrf';
    private const CSRF_TOKEN_FIELD = 'csrf-token';

    /**
     * @var string JSON_ADMITTED_CONTENT_TYPE Contenido admitido de tipo JSON.
     */
    private const JSON_ADMITTED_CONTENT_TYPE = 'application/json; charset=utf-8';

    /**
     * Constructor estándar.
     */
    public function __construct(
        string $formId,
        string $formName,
        $targetObjectName,
        $submitUrl,
        $expectedSubmitMethod
    )
    {
        $this->id = $formId;
        $this->targetObjectName = $targetObjectName;
        $this->formName = $formName;
        $this->submitUrl = $submitUrl;
        
        // Comprueba si el método de envío es válido.
        if ($expectedSubmitMethod && ! in_array($expectedSubmitMethod, Http::METHODS)) {
            throw new \Exception("Unsupported submit method \"$expectedSubmitMethod\".");
        }

        $this->expectedSubmitMethod = $expectedSubmitMethod;
    }

    /*
     *
     * Getters y setters (solo los necesarios)
     * 
     */

    public function setOnSuccess(
        string $onSuccessEventName,
        string $onSuccessEventTarget
    ): void
    {
        $this->onSuccessEventName = $onSuccessEventName;
        $this->onSuccessEventTarget = $onSuccessEventTarget;
    }

    public function setReadOnlyTrue(): void
    {
        $this->readOnly = true;
    }

    public function isReadOnly(): bool
    {
        return $this->readOnly;
    }

    /**
     * Gestiona las peticiones HTTP (lo llama el controlador)
     */
    public function handle(): void
    {
        // Comprueba el tipo de contenido
        $contentType = $_SERVER['CONTENT_TYPE'] ?? null;

        if (mb_strtolower($contentType) !=
            mb_strtolower(self::JSON_ADMITTED_CONTENT_TYPE)) {
            $this->respondJsonError(400, // Bad request
                [
                    'Content type not supported'
                ]
            );
        }

        // Check request method
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        
        if ($httpMethod === 'GET') {
            // Check form submit is valid
            $submittedFormId = $_GET[self::FORM_ID_FIELD] ?? null;

            if ($submittedFormId == $this->id) {
                // Generate default form data.
                $this->processInitialData($_GET);
            }

        // Check form is not read-only and method is the one expected
        } elseif (! $this->isReadOnly()
            && $httpMethod === $this->expectedSubmitMethod) {

            // Get request data as associative array
            $dataInput = file_get_contents('php://input');
            $data = json_decode($dataInput, true);

            // Check form submit is valid
            $submittedFormId = $data[self::FORM_ID_FIELD] ?? null;

            if ($submittedFormId == $this->id) {
                $submittedCsrfToken = $data[self::CSRF_TOKEN_FIELD] ?? null;

                if ($this->CsrfValidateToken($submittedCsrfToken)) {
                    $this->processSubmit($data);
                } else {
                    $errorMessages = [
                        'La validación CSRF ha fallado. Por favor, vuelve a cargar el formulario.'
                    ];

                    $this->respondJsonError(400, $errorMessages);
                }
            }
        } else {
            $this->respondJsonError(
                400, // Bad request
                [
                    'Method not supported'
                ]
            );
        }

        // End script execution.
        die();
    }

    /**
     * Responds with an HTTP 4XX error and message and sends a new CSRF token.
     * 
     * @param int   $httpCode HTTP error code
     * @param array $messages Error messages
     */
    public function respondJsonError(int $httpErrorCode, array $messages): void
    {
        // Generate a new CSRF token.
        $newCsrfToken = $this->CsrfGenerateToken();

        $errorData = [
            'status' => 'error',
            self::FORM_ID_FIELD => $this->id,
            self::CSRF_TOKEN_FIELD => $newCsrfToken,
            'error' => $httpErrorCode,
            'messages' => $messages
        ];

        Http::respondJson($httpErrorCode, $errorData);
    }

    /**
     * Quick alias for JSON error response.
     * 
     * 400 - Bad Request
     * 
     * The server could not understand the request due to invalid syntax.
     */
    public function respondJsonBadRequest(array $messages): void
    {
        $this->respondJsonError(400, $messages);
    }

    /**
     * Quick alias for JSON error response.
     * 
     * 401 - Unauthorized
     * 
     * Although the HTTP standard specifies "unauthorized", semantically this
     * response means "unauthenticated". That is, the client must authenticate
     * itself to get the requested response.
     */
    public function respondJsonUnauthorized(array $messages): void
    {
        $this->respondJsonError(401, $messages);
    }

    /**
     * Quick alias for JSON error response.
     * 
     * 403 - Forbidden
     * 
     * The client does not have access rights to the content; that is, it is
     * unauthorized, so the server is refusing to give the requested resource.
     * Unlike 401, the client's identity is known to the server.
     */
    public function respondJsonForbidden(array $messages): void
    {
        $this->respondJsonError(403, $messages);
    }

    /**
     * Quick alias for JSON error response.
     * 
     * 404 - Not Found
     * 
     * The server can not find the requested resource. In the browser, this
     * means the URL is not recognized. In an API, this can also mean that the
     * endpoint is valid but the resource itself does not exist. Servers may
     * also send this response instead of 403 to hide the existence of a
     * resource from an unauthorized client. This response code is probably the
     * most famous one due to its frequent occurrence on the web.
     */
    public function respondJsonNotFound(array $messages): void
    {
        $this->respondJsonError(404, $messages);
    }

    /**
     * Quick alias for JSON error response.
     * 
     * 405 - Method Not Allowed
     * 
     * The request method is known by the server but is not supported by the
     * target resource. For example, an API may forbid DELETE-ing a resource.
     */
    public function respondJsonMethodNotAllowed(array $messages): void
    {
        $this->respondJsonError(405, $messages);
    }

    /**
     * Quick alias for JSON error response.
     * 
     * 406 - Not Acceptable
     * 
     * This response is sent when the web server, after performing server-driven
     * content negotiation, doesn't find any content that conforms to the
     * criteria given by the user agent.
     */
    public function respondJsonNotAcceptable(array $messages): void
    {
        $this->respondJsonError(406, $messages);
    }

    /**
     * Quick alias for JSON error response.
     * 
     * 409 - Conflict
     * 
     * This response is sent when a request conflicts with the current state of
     * the server.
     */
    public function respondJsonConflict(array $messages): void
    {
        $this->respondJsonError(409, $messages);
    }


    






    /**
     * Responds with an HTTP 200 OK and message
     * 
     * @param array $data Data to send
     */
    public function respondJsonOk(array $data): void
    {
        $okData = [
            'status' => 'ok',
        ];

        $responseData = array_merge($okData, $data);

        Http::respondJson(200, $responseData);
    }

    /**
     * Loads the default form data (i. e. for reading, updating and deleting) 
     * and returns it; should be overriden if necessary
     * 
     * Defauld data keys must be mapped to form input names in
     * generateFormInputs()
     *
     * @param array $requestData Data sent in the request; may contain a 
     * uniqueId
     *
     * @return array Set of default data for the form, as "key" => "value"; must
     * include a "status" field with either "ok" or "error"
     */
    protected function getDefaultData(array $requestData) : array
    {
        return [];
    }

    /**
     * Sends a JSON response generated with the default form data to fill the
     * placeholders
     *
     * @param array $requestData Data sent in the initial request (i. e. 
     * $uniqueId)
     */
    public function processInitialData(array $requestData): void
    {
        $defaultData = $this->getDefaultData($requestData);

        // Check that default data is OK
        if ($defaultData['status'] === 'error') {
            $this->respondJsonError(
                $defaultData['error'],
                $defaultData['messages']
            );
        } else {
            $csrfToken = $this->CsrfGenerateToken();

            $formHiddenData = [
                self::FORM_ID_FIELD => $this->id,
                self::CSRF_TOKEN_FIELD => $csrfToken
            ];

            $all = array_merge($formHiddenData, $defaultData);

            $this->respondJsonOk($all);
        }
    }

    /**
     * Processes a submitted form and sends a JSON response if necessary
     * 
     * @param array $data Data sent in form submission
     */
    abstract public function processSubmit(array $data = []): void;

    /**
     * Generates specific form inputs as placeholders for AJAX preloading
     * 
     * @return string HTML containing the inputs
     */
    abstract protected function generateFormInputs(): string;

    /**
     * Generates the default HTML Bootstrap modal
     *
     * @return string HTML containing the modal
     */
    public function generateModal(): string
    {
        return App::getSingleton()->getViewManagerInstance()->fillTemplate(
            'ajax-forms/common/modal-master-template',
            [
                'form-id' => $this->id,
                'form-id-field' => self::FORM_ID_FIELD,
                'form-name' => $this->formName,
                'csrf-token-field' => self::CSRF_TOKEN_FIELD,
                'target-object-name-data' =>
                    'data-' . self::TARGET_CLASS_NAME_FIELD .
                    '="' . $this->targetObjectName . '"',
                'read-only-data' =>
                    'data-' . self::READ_ONLY_FIELD .
                    '="' . ($this->isReadOnly() ? 'true' : 'false') . '"',
                'on-success-event-name-data' => 
                    $this->onSuccessEventName ?
                    'data-' . self::ON_SUCCESS_EVENT_NAME_FIELD .
                    '="' . $this->onSuccessEventName . '"' : '',
                'on-success-event-target-data' =>
                    $this->onSuccessEventTarget ?
                    'data-' . self::ON_SUCCESS_EVENT_TARGET_FIELD .
                    '="' . $this->onSuccessEventTarget . '"' : '',
                'submit-url-data' =>
                    'data-' . self::SUBMIT_URL_FIELD .
                    '="' . $this->submitUrl . '"',
                'expected-submit-method-data' =>
                    'data-' . self::EXPECTED_SUBMIT_METHOD_FIELD .
                    '="' . $this->expectedSubmitMethod . '"',
                'inputs' => $this->generateFormInputs(),
                'footer' =>
                    $this->isReadOnly() ? '' :
                    <<< HTML
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                    HTML,
            ]
        );
    }

    /**
     * Generates a CSRF token and stores it in $_SESSION
     * 
     * @return string Generated token
     */
    private function CsrfGenerateToken(): string
    {
        if (App::getSingleton()->isDevMode())
            return '';

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
        if (App::getSingleton()->isDevMode())
            return true;

        if (! $token) return false;

        if (isset($_SESSION[self::CSRF_PREFIX . '_' . $this->id])
            && $_SESSION[self::CSRF_PREFIX . '_' . $this->id] === $token) {
            
            unset($_SESSION[self::CSRF_PREFIX . '_' . $this->id]);

            return true;
        }

        return false;
    }

    /**
     * Generates a JSON link formalization based in HATEOAS link specification.
     * 
     * @param string $rel
     * @param string $selectType 'multi' for multiple select, 'single' for 
     *                           single select (interpreted in Bootstrap modal
     *                           handling).
     * @param mixed $data
     * 
     * @return stdClass Object ready for JSON serialization.
     */
    public static function generateHateoasSelectLink(string $rel, string $selectType, $data) : stdClass
    {
        $link = new stdClass();

        $link->rel = $rel;
        $link->selectType = $selectType;

        if (! is_array($data)) $data = [ $data ]; // Ensure it is an array

        $link->data = array_values($data); // Ensure array is unkeyed

        return $link;
    }

    /**
     * Generates an Bootstrap button to fire the modal.
     * 
     * @param string|null $content Content of the button.
     * @param int|null $uniqueId
     * @param bool $small
     */
    public function generateButton($content = null, $uniqueId = null, $small = false): string
    {
        $formId = $this->id;
        $buttonContent = $content ? $content : $this->formName;

        $uniqueIdData = $uniqueId ? 'data-ajax-unique-id="' . $uniqueId . '"' : '';

        $smallClass = $small ? 'btn-sm' : '';

        $button = <<< HTML
        <button class="btn-ajax-modal-fire btn $smallClass btn-primary mb-1 mx-1" data-ajax-form-id="$formId" $uniqueIdData>$buttonContent</button>
        HTML;

        return $button;
    }
}