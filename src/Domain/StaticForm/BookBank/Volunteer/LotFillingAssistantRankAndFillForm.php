<?php

namespace Juancrrn\Lyra\Domain\StaticForm\BookBank\Volunteer;

use DateTime;
use Exception;
use Juancrrn\Lyra\Common\Api\BookBank\Common\SubjectSearchApi;
use Juancrrn\Lyra\Common\Api\BookBank\Volunteer\LotLocatorApi;
use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\Http;
use Juancrrn\Lyra\Common\TemplateUtils;
use Juancrrn\Lyra\Common\View\BookBank\Manager\StudentOverviewView;
use Juancrrn\Lyra\Common\View\BookBank\Volunteer\CheckInAssistantStudentOverviewView;
use Juancrrn\Lyra\Common\View\BookBank\Volunteer\LotFillingAssistantHomeView;
use Juancrrn\Lyra\Domain\BookBank\Donation\Donation;
use Juancrrn\Lyra\Domain\BookBank\Donation\DonationRepository;
use Juancrrn\Lyra\Domain\BookBank\Lot\Lot;
use Juancrrn\Lyra\Domain\BookBank\Lot\LotRepository;
use Juancrrn\Lyra\Domain\BookBank\Request\Request;
use Juancrrn\Lyra\Domain\BookBank\Request\RequestRepository;
use Juancrrn\Lyra\Domain\BookBank\Subject\SubjectRepository;
use Juancrrn\Lyra\Domain\DomainUtils;
use Juancrrn\Lyra\Domain\Email\EmailUtils;
use Juancrrn\Lyra\Domain\StaticForm\StaticFormModel;
use Juancrrn\Lyra\Domain\User\User;
use Juancrrn\Lyra\Domain\User\UserRepository;

/**
 * Check-in assistant donation lite form
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class LotFillingAssistantRankAndFillForm extends StaticFormModel
{

    private const FORM_ID = 'form-bookbank-volunteer-lot-filling-assistant-rank-and-fill';
    private const FORM_FIELDS_NAME_PREFIX = self::FORM_ID . '-';

    private $request;

    private $student;

    public function __construct(string $action)
    {
        parent::__construct(self::FORM_ID, [ 'action' => $action ]);

        $app = App::getSingleton();

        $this->rankAndPickTopRequest();

        if ($this->request == null) {
            $app->getViewManagerInstance()->addWarningMessage(
                'No existen solicitudes pendientes para el curso escolar actual.',
                LotFillingAssistantHomeView::VIEW_ROUTE
            );
        }
        
        $userRepo = new UserRepository($app->getDbConn());

        $this->student = $userRepo->retrieveById($this->request->getStudentId());
    }
    
    protected function generateFields(array & $preloadedData = []): string
    {
        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        // Add content list item template to HTML (available to AJAX)

        $viewManager->addTemplateElement(
            'bookbank-common-subject-list-editable-item',
            'bookbank/common/template_subject_list_editable_item',
            [
                'book-image-url' => '',
                'title-human' => '',
                'book-isbn' => '',
                'book-name' => '',
                'id' => '',
                'checkbox-name' => self::FORM_FIELDS_NAME_PREFIX . 'lot-contents'
            ]
        );
        
        $viewManager->addTemplateElement(
            'bookbank-common-subject-list-empty-item',
            'bookbank/common/template_subject_list_empty_item',
            []
        );

        // Add search list item template to HTML (available to AJAX)

        $viewManager->addTemplateElement(
            'bookbank-common-subject-search-item',
            'bookbank/common/template_subject_search_item',
            [
                'book-image-url' => '',
                'title-human' => '',
                'book-isbn' => '',
                'book-name' => '',
                'id' => ''
            ]
        );
        
        $viewManager->addTemplateElement(
            'bookbank-common-subject-search-empty-item',
            'bookbank/common/template_subject_search_empty_item',
            []
        );

        $educationLevelSelectOptionsHtml = TemplateUtils::generateSelectOptions(
            [
                $this->request->getEducationLevel() => DomainUtils::getEducationLevelsForSelectOptions()[$this->request->getEducationLevel()]
            ],
            $this->request->getEducationLevel()
        );

        // TODO null request

        return $viewManager->fillTemplate(
            'forms/bookbank/volunteer/inputs_lot_filling_assistant_rank_and_fill_form',
            [
                'request-id' => $this->request->getId(),
                'lot-locator-url' => $app->getUrl() . LotLocatorApi::API_ROUTE_BASE . $this->request->getId() . '/locator/',
                'student-card' => $this->student->generateCard(),
                'prefix' => self::FORM_FIELDS_NAME_PREFIX,
                'education-level-select-options' => $educationLevelSelectOptionsHtml,
                'specification' => $this->request->getSpecification(),
                'query-url' => $app->getUrl() . SubjectSearchApi::API_ROUTE,
                'checkbox-name' => self::FORM_FIELDS_NAME_PREFIX . 'lot-contents',
                'contents' => $viewManager->fillTemplate(
                    'html_templates/bookbank/common/template_subject_list_empty_item',
                    []
                )
            ]
        );
    }
    
    protected function process(array & $postedData): void
    {
        $app = App::getSingleton();
        
        $viewManager = $app->getViewManagerInstance();

        // Check request

        $requestId = $postedData[self::FORM_FIELDS_NAME_PREFIX . 'request-id'] ?? null;

        $requestRepo = new RequestRepository($app->getDbConn());

        if (! $requestId || ! $requestRepo->findById($requestId)) {
            $viewManager->addErrorMessage('Hubo un error al procesar el formulario. Por favor, vuelve a intentarlo en unos minutos. (1)');

            return;
        }

        $request = $requestRepo->retrieveById($requestId);

        // Check contents

        $lotContents = $postedData[self::FORM_FIELDS_NAME_PREFIX . 'lot-contents'] ?? null;

        $requestRejectedStock = false;

        if (is_array($lotContents)) {
            $subjectRepository = new SubjectRepository($app->getDbConn());

            foreach ($lotContents as $subjectId) {
                if (! $subjectRepository->findById($subjectId)) {
                    $viewManager->addErrorMessage('Hubo un error al procesar el formulario. Por favor, vuelve a intentarlo en unos minutos. (2)');
                } else {
                    $newSubject = $subjectRepository->retrieveById($subjectId);

                    if ($newSubject->getEducationLevel() != $request->getEducationLevel()) {
                        $lotContents = array_diff($lotContents, [ $subjectId ]);

                        $viewManager->addWarningMessage('Se ignoró un contenido cuyo nivel educativo no coincidía con el de la solicitud.');
                    }
                }
            }

            if (empty($lotContents)) {
                $viewManager->addErrorMessage('Hubo un error al procesar el formulario. Por favor, vuelve a intentarlo en unos minutos. (3)');
            }
        } else {
            $requestRejectedStock = true;
        }

        if (! $viewManager->anyErrorMessages()) {
            if ($requestRejectedStock) {
                $requestRepo->updateStatusById($requestId, Request::STATUS_REJECTED_STOCK);

                $viewManager->addSuccessMessage('La solicitud se marcó como desestimada por falta de ejemplares correctamente.');
            } else {
                // Create associated lot with contents

                $lotRepo = new LotRepository($app->getDbConn());

                $lot = new Lot(
                    null,
                    $requestId,
                    Lot::STATUS_READY,
                    new DateTime,
                    $app->getSessionManagerInstance()->getLoggedInUser()->getId(),
                    null,
                    null,
                    null
                );

                $lotRepo->insert($lot);

                $lotRepo->insertContentsById($lot->getId(), $lotContents);

                // Update request status

                $requestRepo->updateStatusById($requestId, Request::STATUS_PROCESSED);

                $viewManager->addSuccessMessage('La solicitud se marcó como procesada correctamente y se creó un paquete preparado asociado.');
            }

            // Notify user

            $userRepo = new UserRepository($app->getDbConn());

            $studentId = $request->getStudentId();

            EmailUtils::sendLotFillingAssistantRequestUpdatedMessage(
                $userRepo->retrieveById($studentId),
                'https://soporte.iax.es',
                'soporte.iax.es'
            );

            $viewManager->addSuccessMessage('Se envió un aviso al estudiante sobre el cambio en el estado de su solicitud.');
        }
    }

    private function rankAndPickTopRequest(): void
    {
        $app = App::getSingleton();

        // 1. Select all pending requests for this year
        
        $requestRepo = new RequestRepository($app->getDbConn());

        $requests = $requestRepo->retrieveByIds($requestRepo->findPending());

        // 2. Retrieve this and past years donation, calculate rank and associate with request

        $donationRepo = new DonationRepository($app->getDbConn());

        $data = [];

        foreach ($requests as $request) {
            $rank =
                $donationRepo->countContentsByStudentIdThisYear($request->getStudentId()) * 1 +
                $donationRepo->countContentsByStudentIdPastYears($request->getStudentId()) * 0.5;

            $data[] = [
                'request' => $request,
                'rank' => $rank,
                'timestamp' => $request->getCreationDate()->getTimestamp()
            ];
        }

        // 3. Sort by rank and creation date

        usort($data, function($b, $a) {
            if ($a['rank'] > $b['rank']) {
                return 1;
            } elseif ($a['rank'] < $b['rank']) {
                return - 1;
            } else {
                if ($a['timestamp'] > $b['timestamp']) {
                    return 1;
                } elseif ($a['timestamp'] < $b['timestamp']) {
                    return - 1;
                } else {
                    return 0;
                }
            }
        });

        // 4. Return top request

        if (! empty($data)) {
            $this->request = $data[0]['request'];
        } else {
            $this->request = null;
        }
    }
}