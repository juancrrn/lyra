<?php

namespace Juancrrn\Lyra\Domain\StaticForm\BookBank\Volunteer;

use DateTime;
use Juancrrn\Lyra\Common\Api\BookBank\Common\SubjectSearchApi;
use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\CommonUtils;
use Juancrrn\Lyra\Common\TemplateUtils;
use Juancrrn\Lyra\Common\View\BookBank\Volunteer\CheckInAssistantStudentOverviewView;
use Juancrrn\Lyra\Domain\BookBank\Lot\Lot;
use Juancrrn\Lyra\Domain\BookBank\Lot\LotRepository;
use Juancrrn\Lyra\Domain\BookBank\Request\Request;
use Juancrrn\Lyra\Domain\BookBank\Request\RequestRepository;
use Juancrrn\Lyra\Domain\BookBank\Subject\SubjectRepository;
use Juancrrn\Lyra\Domain\DomainUtils;
use Juancrrn\Lyra\Domain\StaticForm\StaticFormModel;
use Juancrrn\Lyra\Domain\User\UserRepository;

/**
 * Check-in assistant request lite form
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class CheckInAssistantRequestLiteForm extends StaticFormModel
{

    private const FORM_ID = 'form-bookbank-volunteer-check-in-assistant-request-lite';
    private const FORM_FIELDS_NAME_PREFIX = self::FORM_ID . '-';

    private $student;

    public function __construct(string $action, int $studentId)
    {
        parent::__construct(self::FORM_ID, [ 'action' => $action ]);

        $app = App::getSingleton();
        $userRepository = new UserRepository($app->getDbConn());

        $this->student = $userRepository->retrieveById($studentId);
    }
    
    protected function generateFields(array & $preloadedData = []): string
    {
        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        $statusSelectOptionsHtml = TemplateUtils::generateSelectOptions(
            [
                Request::STATUS_PENDING => Request::getStatusesForSelectOptions()[Request::STATUS_PENDING]
            ],
            Request::STATUS_PENDING
        );

        $educationLevelSelectOptionsHtml = TemplateUtils::generateSelectOptions(
            DomainUtils::getEducationLevelsForSelectOptions()
        );

        $filling = [
            'education-level-select-options' => $educationLevelSelectOptionsHtml,
            'school-year-human' => DomainUtils::schoolYearToHuman($app->getSetting('school-year')),
            'query-url' => $app->getUrl() . SubjectSearchApi::API_ROUTE,
            'form-fields-name-prefix' => self::FORM_FIELDS_NAME_PREFIX,
            'status-select-options-html' => $statusSelectOptionsHtml,
        ];

        return $viewManager->fillTemplate(
            'forms/bookbank/volunteer/inputs_check_in_assistant_request_lite_form',
            $filling
        );
    }
    
    protected function process(array & $postedData): void
    {
        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        $newSpecification = $this->processNewSpecification($postedData[self::FORM_FIELDS_NAME_PREFIX . 'specification']);

        $newEducationLevel = $this->processNewEducationLevel($postedData[self::FORM_FIELDS_NAME_PREFIX . 'education-level']);

        if (! $viewManager->anyErrorMessages()) {
            $requestRepository = new RequestRepository($app->getDbConn());

            $request = new Request(
                null,
                $this->student->getId(),
                Request::STATUS_PENDING,
                new DateTime,
                $app->getSessionManagerInstance()->getLoggedInUser()->getId(),
                $newEducationLevel,
                $app->getSetting('school-year'),
                $newSpecification,
                false
            );

            $insertedId = $requestRepository->insert($request);

            $viewManager->addSuccessMessage('El identificador de la solicitud creada es <strong>' . $insertedId . '</strong>');

            $viewManager->addSuccessMessage(
                'La solicitud fue creada correctamente.',
                CheckInAssistantStudentOverviewView::VIEW_ROUTE_BASE . $this->student->getId() . '/overview/'
            );
        }
    }

    private function processNewSpecification($newSpecification = null): mixed
    {
        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        if (empty($newSpecification)) {
            $viewManager->addErrorMessage('El campo especificación no puede estar vacío.');
        }

        return $newSpecification;
    }
    
    private function processNewEducationLevel($newEducationLevel = null): mixed
    {
        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        if (! $newEducationLevel) {
            $viewManager->addErrorMessage('Hubo un error al procesar el nivel educativo.');
        } elseif (! DomainUtils::validEducationLevel($newEducationLevel)) {
            $viewManager->addErrorMessage('Hubo un error al procesar el nivel educativo.');
        }

        return $newEducationLevel;
    }
}