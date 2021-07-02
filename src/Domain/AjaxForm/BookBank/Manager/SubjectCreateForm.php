<?php

namespace Juancrrn\Lyra\Domain\AjaxForm\BookBank\Manager;

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\Http;
use Juancrrn\Lyra\Domain\AjaxForm\AjaxFormModel;
use Juancrrn\Lyra\Domain\BookBank\Subject\Subject;
use Juancrrn\Lyra\Domain\BookBank\Subject\SubjectRepository;
use Juancrrn\Lyra\Domain\DomainUtils;
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

class SubjectCreateForm extends AjaxFormModel
{

    private const FORM_ID = 'bookbank-manager-subject-create-'; // Prefix
    private const FORM_NAME = 'Crear asignatura';
    private const TARGET_CLASS_NAME = 'Subject';
    public  const SUBMIT_URL = '/bookbank/manage/subjects/create/';
    private const EXPECTED_SUBMIT_METHOD = Http::METHOD_POST;
    private const ON_SUCCESS_EVENT_NAME = 'subject.created';
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

        // Map data to match placeholder inputs' names
        $responseData = [
            'status' => 'ok',
            self::TARGET_CLASS_NAME => [
                //'educationLevelHumanDescription' => DomainUtils::educationLevelToHuman() //TODO
            ]
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