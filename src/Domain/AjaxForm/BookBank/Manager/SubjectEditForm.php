<?php

namespace Juancrrn\Lyra\Domain\AjaxForm\BookBank\Manager;

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\Http;
use Juancrrn\Lyra\Domain\AjaxForm\AjaxFormModel;
use Juancrrn\Lyra\Domain\BookBank\Subject\Subject;
use Juancrrn\Lyra\Domain\BookBank\Subject\SubjectRepository;
use Juancrrn\Lyra\Domain\User\User;

/**
 * Bookbank manager subject edit AJAX form
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class SubjectEditForm extends AjaxFormModel
{

    private const FORM_ID = 'bookbank-manager-subject-edit-'; // Prefix
    private const FORM_NAME = 'Editar asignatura';
    private const TARGET_CLASS_NAME = 'Subject';
    public  const SUBMIT_URL = '/bookbank/manage/subjects/edit/';
    private const EXPECTED_SUBMIT_METHOD = Http::METHOD_PATCH;
    private const ON_SUCCESS_EVENT_NAME = 'subject.edited';
    private const ON_SUCCESS_EVENT_TARGET = '#asignacion-ps-list'; // TODO set accordion item

    /**
     * Constructs the form object
     */
    public function __construct(string $educationLevel)
    {
        $app = App::getSingleton();

        $sessionManager = $app->getSessionManagerInstance();

        $sessionManager->requirePermissionGroups([ User::NPG_BOOKBANK_MANAGER ]);

        parent::__construct(
            self::FORM_ID . $educationLevel,
            self::FORM_NAME,
            self::TARGET_CLASS_NAME,
            $app->getUrl() . self::SUBMIT_URL,
            self::EXPECTED_SUBMIT_METHOD
        );

        $this->setOnSuccess(
            self::ON_SUCCESS_EVENT_NAME,
            self::ON_SUCCESS_EVENT_TARGET
        );
    }

    protected function getDefaultData(array $requestData) : array
    {
        $app = App::getSingleton();

        if (! isset($requestData['uniqueId'])){
            $responseData = array(
                'status' => 'error',
                'error' => 400, // Bad request
                'messages' => array(
                    'Falta el parámetro uniqueId.'
                )
            );
        }

        $uniqueId = $requestData['uniqueId'];

        $subjectRepo = new SubjectRepository($app->getDbConn());

        // Comprobar que el uniqueId es válido.
        if (! $subjectRepo->findById($uniqueId)) {
            $responseData = array(
                'status' => 'error',
                'error' => 404, // Not found.
                'messages' => array(
                    'La asignatura especificada no existe.'
                )
            );

            return $responseData;
        }

        $asignacion = $subjectRepo->retrieveById($uniqueId);

        /*// Formalización HATEOAS de profesor.
        $profesorLink = FormularioAjax::generateHateoasSelectLink(
            'profesor',
            'single',
            Usuario::dbGetByRol(2)
        );
        
        // Formalización HATEOAS de asignatura.
        $asignaturaLink = FormularioAjax::generateHateoasSelectLink(
            'asignatura',
            'single',
            Asignatura::dbGetAll()
        );
        
        // Formalización HATEOAS de grupo.
        $grupoLink = FormularioAjax::generateHateoasSelectLink(
            'grupo',
            'single',
            Grupo::dbGetAll()
        );
        
        // Formalización HATEOAS de foro.
        $foroLink = FormularioAjax::generateHateoasSelectLink(
            'foroPrincipal',
            'single',
            Foro::dbGetAll()
        );*/

        // Map data to match placeholder inputs' names
        $responseData = [
            'status' => 'ok',
            'links' => [],
            self::TARGET_CLASS_NAME => $asignacion
        ];

        return $responseData;
    }

    public function generateFormInputs(): string
    {
        return App::getSingleton()->getViewManagerInstance()->fillTemplate(
            'ajax-forms/bookbank/manager/inputs-subject-edit-form',
            []
        );
    }

    public function processSubmit(array $data = array()): void
    {

        $uniqueId = $data['uniqueId'] ?? null;

        // Mapear los datos para que coincidan con los nombres de los inputs.
        if (! $uniqueId) {
            $errors[] = 'Falta el parámetro uniqueId.';

            $this->respondJsonBadRequest($errors);
        }
        
        $app = App::getSingleton();

        $subjectRepo = new SubjectRepository($app->getDbConn());
        
        if (! $subjectRepo->findById($uniqueId)) {
            $errors[] = 'La asignatura especificada no existe.';

            $this->respondJsonNotFound($errors);
        }
        
        $name = $data['name'] ?? null;
        $bookName = $data['bookName'] ?? null;
        $bookIsbn = $data['bookIsbn'] ?? null;
        $bookImageUrl = $data['bookImageUrl'] ?? null;
        
        if (empty($name)) {
            $errors[] = 'El campo de nombre no puede estar vacío.';
        }
        
        if (empty($bookName)) { // TODO A lo mejor si pueden estar vacíos
            $errors[] = 'El campo de nombre del libro no puede estar vacío.';
        }
        
        if (empty($bookIsbn)) { // TODO A lo mejor si pueden estar vacíos
            $errors[] = 'El campo de ISBN del libro no puede estar vacío.';
        }
        
        if (empty($bookImageUrl)) { // TODO A lo mejor si pueden estar vacíos
            $errors[] = 'El campo de URL de imagen del libro no puede estar vacío.';
        }

        if (! empty($errors)) {
            $this->respondJsonBadRequest($errors);
        } else {
            $subjectRepo->updateNameAndBookAttributes(
                $uniqueId,
                $name,
                $bookName,
                $bookIsbn,
                $bookImageUrl
            );

            $responseData = array(
                'status' => 'ok',
                'messages' => array('Asignatura actualizada correctamente.'),
                self::TARGET_CLASS_NAME => $subjectRepo->retrieveById($uniqueId)
            );
                
            $this->respondJsonOk($responseData);
        }
    }
}

?>