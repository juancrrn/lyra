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
 * Request and lot edition form
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class CheckInAssistantPickupLiteForm extends StaticFormModel
{

    private const FORM_ID = 'form-bookbank-volunteer-check-in-assistant-pickup-lite';
    private const FORM_FIELDS_NAME_PREFIX = self::FORM_ID . '-';

    private $request;

    private $lot;

    private $student;

    public function __construct(string $action, int $requestId)
    {
        parent::__construct(self::FORM_ID, [ 'action' => $action ]);

        $app = App::getSingleton();

        // Precondition: request exists and is processed and an associated lot
        // exists and is picked-up

        $requestRepo = new RequestRepository($app->getDbConn());

        $this->request = $requestRepo->retrieveById($requestId);

        $lotRepo = new LotRepository($app->getDbConn());

        $this->lot = $lotRepo->retrieveById($lotRepo->findByRequestId($requestId), true);

        $userRepo = new UserRepository($app->getDbConn());

        $this->student = $userRepo->retrieveById($this->request->getStudentId());
    }
    
    protected function generateFields(array & $preloadedData = []): string
    {
        return <<< HTML
        <div class="row">
            <div class="col"></div>
            <div class="col-md-6">
                <div class="rounded border border-warning p-3">Este formulario no está implementado aún.</div>
            </div>
            <div class="col"></div>
        </div>
        HTML;

        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        $statusSelectOptionsHtml = TemplateUtils::generateSelectOptions(
            [
                Request::STATUS_PROCESSED => Request::getStatusesForSelectOptions()[Request::STATUS_PROCESSED]
            ],
            Request::STATUS_PROCESSED
        );

        $educationLevelSelectOptionsHtml = TemplateUtils::generateSelectOptions(
            [
                $this->request->getEducationLevel() => DomainUtils::getEducationLevelsForSelectOptions()[$this->request->getEducationLevel()]
            ],
            $this->request->getEducationLevel()
        );

        $filling = [
            'education-level-select-options-html' => $educationLevelSelectOptionsHtml,
            'school-year-human' => DomainUtils::schoolYearToHuman($this->request->getSchoolYear()),
            'query-url' => $app->getUrl() . SubjectSearchApi::API_ROUTE,
            'creation-date-human' => strftime(
                CommonUtils::HUMAN_DATETIME_FORMAT_STRF,
                $this->request->getCreationDate()->getTimestamp()
            ),
            'form-fields-name-prefix' => self::FORM_FIELDS_NAME_PREFIX,
            'status-select-options-html' => $statusSelectOptionsHtml,
            'associated-lot-html' => $this->generateAssociatedLotFields()
        ];

        return $viewManager->fillTemplate(
            'forms/bookbank/volunteer/inputs_check_in_assistant_pickup_lite_form',
            $filling
        );
    }
    
    protected function process(array & $postedData): void
    {
        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        $lotRepo = new LotRepository($app->getDbConn());

        $lotRepo->updateStatus($this->lot->getId(), Lot::STATUS_RETURNED);

        $viewManager->addSuccessMessage(
            'El paquete fue devuelto correctamente.',
            CheckInAssistantStudentOverviewView::VIEW_ROUTE_BASE . $this->student->getId() . '/overview/'
        );
    }

    private function generateAssociatedLotFields(): string
    {
        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        $lotStatusSelectOptionsHtml = TemplateUtils::generateSelectOptions(
            [
                Lot::getStatusesForSelectOptions()[Lot::STATUS_RETURNED]
            ],
            Lot::STATUS_RETURNED
        );
        
        $initialContentsListHtml = '';

        if (empty($this->lot->getContents())) {
            $initialContentsListHtml = $viewManager->fillTemplate(
                'html_templates/bookbank/common/template_subject_list_empty_item', []
            );
        } else {
            foreach ($this->lot->getContents() as $subject) {
                $bookImageUrl = $subject->getBookImageUrl() ??
                    $app->getUrl() . '/img/graphic-default-book-image.svg';

                $bookName = $subject->getBookName() ??
                    'Sin libro o libro no definido';
                    
                $initialContentsListHtml .= $viewManager->fillTemplate(
                    'html_templates/bookbank/common/template_subject_list_item',
                    [
                        'book-image-url' => $bookImageUrl,
                        'title-human' =>
                            $subject->getName() . ' de ' .
                            DomainUtils::educationLevelToHuman($subject->getEducationLevel())->getTitle(),
                        'book-isbn' => $subject->getBookIsbn(),
                        'book-name' => $bookName,
                        'id' => $subject->getId(),
                        'checkbox-name' => self::FORM_FIELDS_NAME_PREFIX . 'lot-contents'
                    ]
                );
            }
        }

        $pickupDateHuman =
            $this->lot->getStatus() == Lot::STATUS_PICKED_UP ||
            $this->lot->getStatus() == Lot::STATUS_RETURNED ?
            strftime(
                CommonUtils::HUMAN_DATETIME_FORMAT_STRF,
                $this->lot->getPickupDate()->getTimestamp()
            ) :
            'No definida'
            ;

        $associatedLotHtml = $viewManager->fillTemplate(
            'forms/bookbank/volunteer/inputs_check_in_assistant_pickup_lite_form_part_associated_lot',
            [
                'id' => $this->lot->getId(),
                'status-human' => Lot::statusToHuman(Lot::STATUS_RETURNED)->getTitle(),
                'creation-date-human' => strftime(
                    CommonUtils::HUMAN_DATETIME_FORMAT_STRF,
                    $this->lot->getCreationDate()->getTimestamp()
                ),
                'status-select-options-html' => $lotStatusSelectOptionsHtml,
                'contents' => $initialContentsListHtml,
                'pickup-date-human' => $pickupDateHuman,
                'return-date-human' => 'Ahora mismo',
                'creation-date-human' => strftime(
                    CommonUtils::HUMAN_DATETIME_FORMAT_STRF,
                    $this->lot->getCreationDate()->getTimestamp()
                )
            ]
        );

        return $associatedLotHtml;
    }
}