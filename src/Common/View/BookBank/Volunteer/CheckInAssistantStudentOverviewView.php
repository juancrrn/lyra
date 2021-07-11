<?php

namespace Juancrrn\Lyra\Common\View\BookBank\Volunteer;

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\CommonUtils;
use Juancrrn\Lyra\Common\View\ViewModel;
use Juancrrn\Lyra\Domain\BookBank\Donation\DonationRepository;
use Juancrrn\Lyra\Domain\BookBank\Lot\Lot;
use Juancrrn\Lyra\Domain\BookBank\Lot\LotRepository;
use Juancrrn\Lyra\Domain\BookBank\Request\Request;
use Juancrrn\Lyra\Domain\BookBank\Request\RequestRepository;
use Juancrrn\Lyra\Domain\DomainUtils;
use Juancrrn\Lyra\Domain\User\User;
use Juancrrn\Lyra\Domain\User\UserRepository;

/**
 * Vista de resumen de banco de libros de un estudiante
 *
 * @package lyra
 * 
 * @author juancrrn
 *
 * @version 0.0.1
 */

class CheckInAssistantStudentOverviewView extends ViewModel
{
    private const VIEW_RESOURCE_FILE    = 'views/bookbank/volunteer/view_check_in_assistant_student_overview';
    public  const VIEW_NAME             = 'Asistente de recepción';
    public  const VIEW_ID               = 'bookbank-volunteering-check-in-assistant-student-overview';
    public  const VIEW_ROUTE_BASE       = '/bookbank/check-in/students/';
    public  const VIEW_ROUTE            = self::VIEW_ROUTE_BASE . '([0-9]+)/overview/';

    private $student;

    private $returnsContent;
    private $returnsCount;
    private $pickupsContent;
    private $pickupsCount;

    public function __construct(int $studentId)
    {
        $app = App::getSingleton();

        $sessionManager = $app->getSessionManagerInstance();

        $sessionManager->requirePermissionGroups([ User::NPG_BOOKBANK_MANAGER ]);

        $userRepository = new UserRepository($app->getDbConn());

        if (! $userRepository->findById($studentId)) {
            $app->getViewManagerInstance()->addErrorMessage('El parámetro de identificador de usuario es inválido.', '');
        }

        $this->student = $userRepository->retrieveById($studentId, true);

        if (! $this->student->hasPermission(User::NPG_STUDENT)) {
            $app->getViewManagerInstance()->addErrorMessage('El parámetro de identificador de usuario es inválido.', '');
        }

        $this->name = self::VIEW_NAME;
        $this->id = self::VIEW_ID;
    }

    public function processContent(): void
    {
        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        $this->initializeReturnsContent();
        $this->initializePickupsContent();

        if ($this->returnsCount != 0) {
            $requestsText = 'No es posible crear solicitudes si existen devoluciones pendientes.';
            $requestsBtnUrl = '#';
            $requestsBtnDisabledClass = 'disabled';
            $requestsBtnDisabledAttributes = 'aria-disabled="true" tabindex="-1"';
        } else {
            $requestsText = 'Es posible crear solicitudes.';
            $requestsBtnUrl = $app->getUrl() . CheckInAssistantRequestLiteView::VIEW_ROUTE_BASE . $this->student->getId() . '/requests/create/';
            $requestsBtnDisabledClass = '';
            $requestsBtnDisabledAttributes = '';
        }

        $filling = [
            'app-name' => $app->getName(),
            'view-name' => $this->getName(),
            'back-to-search-url' => $app->getUrl() . CheckInAssistantStudentSearchView::VIEW_ROUTE,
            'student-card-html' => $this->generateStudentCardPart(),

            'returns-count' => $this->returnsCount,
            'returns-accordion-id' => self::VIEW_ID . '-returns-accordion',
            // Mostrar todas las devoluciones (solicitudes procesadas y con paquete en estado picked-up) y enlace a formulario "lite" para transformar a estado siguiente (solicitud procesada y paquete returned)
            'returns-content' => $this->getReturnsContent(),

            'pickups-count' => $this->pickupsCount,
            'pickups-accordion-id' => self::VIEW_ID . '-pickups-accordion',
            // Mostrar todas las recogidas (solicitudes procesadas y con paquete en estado ready) y enlace a formulario "lite" para transformar (y modificar contenido) a estados siguientes (solicitud procesada y paquete a estados picked-up o rejected)
            'pickups-content' => $this->getPickupsContent(),

            'donations-btn-url' => $app->getUrl() . CheckInAssistantDonationLiteView::VIEW_ROUTE_BASE . $this->student->getId() . '/donations/create/',

            'requests-text' => $requestsText,
            'requests-btn-url' => $requestsBtnUrl,
            'requests-btn-disabled-class' => $requestsBtnDisabledClass,
            'requests-btn-disabled-attributes' => $requestsBtnDisabledAttributes,

            //'request-count' => 0,//$requestIdsCount,
            //'request-and-lot-create-url' => '',//$app->getUrl() . RequestAndLotCreateView::VIEW_ROUTE_BASE . $this->student->getId() . '/requests/create/',
            //'request-lot-list-human' => $this->generateRequestListItemsHtml(),
            //'donation-count' => 0,//$donationIdsCount,
            //'donation-create-url' => '',//$app->getUrl() . DonationCreateView::VIEW_ROUTE_BASE . $this->student->getId() . '/donations/create/',
            //'donation-list-human' => $this->generateDonationListItemsHtml()
        ];

        $viewManager->renderTemplate(self::VIEW_RESOURCE_FILE, $filling);
    }

    private function generateStudentCardPart(): string
    {
        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        if ($this->student->getRepresentativeId() == null) {
            $userRepresentativeHuman = '(No definido)';
        } else {
            $userRepository = new UserRepository($app->getDbConn());
            $representative = $userRepository
                ->retrieveById($this->student->getRepresentativeId());
            $userRepresentativeHuman = $representative->getFullName();
        }

        $studentCardFilling = [
            'accordion-id' => $this->student->getId(),
            'user-profile-picture' => $app->getUrl() . '/img/default-user-image.png',
            'user-id' => $this->student->getId(),
            'user-full-name' => $this->student->getFullName(),
            'user-gov-id' => $this->student->getGovId(true),
            'user-email-address' => $this->student->getEmailAddress(),
            'user-phone-number' => $this->student->getPhoneNumber(),
            'user-representative-name-human' => $userRepresentativeHuman,
            'user-status-human' => User::statusToHuman(
                $this->student->getStatus()
            )->getTitle()
        ];

        return $viewManager->fillTemplate(
            'views/bookbank/common/part_student_profile_card',
            $studentCardFilling
        );
    }

    private function initializeReturnsContent(): void
    {
        $this->returnsContent = '';

        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        $requestRepository = new RequestRepository($app->getDbConn());

        $requestIds = $requestRepository->findReturnsByStudentId($this->student->getId());
        
        $this->returnsCount = count($requestIds);

        if (empty($requestIds)) {
            $this->returnsContent .= $viewManager->fillTemplate(
                'views/bookbank/common/part_card_empty',
                []
            );
        } else {
            foreach ($requestIds as $requestId) {
                $request = $requestRepository->retrieveById($requestId, true);

                $specification = 
                    $request->getSpecification() == '' ? '(Vacía)' :
                    $request->getSpecification();

                //if ($request->getStatus() == Request::STATUS_PROCESSED) {
                    $lotRepository = new LotRepository($app->getDbConn());

                    $lot = $lotRepository->retrieveById($lotRepository->findByRequestId($requestId), true);

                    $lotContentListHuman = '';

                    if (empty($lot->getContents())) {
                        $lotContentListHuman = $viewManager->fillTemplate(
                            'views/bookbank/common/part_subject_list_empty', []
                        );
                    } else {
                        foreach ($lot->getContents() as $subject) {
                            $bookImageUrl = $subject->getBookImageUrl() ??
                                $app->getUrl() . '/img/graphic-default-book-image.svg';

                            $bookName = $subject->getBookName() ??
                                'Sin libro o libro no definido';
                                
                            $lotContentListHuman .= $viewManager->fillTemplate(
                                'views/bookbank/common/part_subject_list_item',
                                [
                                    'book-image-url' => $bookImageUrl,
                                    'title-human' =>
                                        $subject->getName() . ' de ' .
                                        DomainUtils::educationLevelToHuman($subject->getEducationLevel())->getTitle(),
                                    'book-isbn' => $subject->getBookIsbn(),
                                    'book-name' => $bookName
                                ]
                            );
                        }
                    }

                    $this->returnsContent .= $viewManager->fillTemplate(
                        'views/bookbank/volunteer/view_check_in_assistant_student_overview_part_request_with_lot_editable_item',
                        [
                            'heading-id' => 'header-request-' . $requestId,
                            'body-id' => 'body-request-' . $requestId,
                            'id-badge' => $viewManager->fillTemplate(
                                'views/bookbank/common/part_id_badge_request',
                                [ 'id' => $requestId ]
                            ),
                            'title-human' => 'Solicitud de ' . DomainUtils::educationLevelToHuman($request->getEducationLevel())->getTitle(),
                            'status-human' => Request::statusToHuman($request->getStatus())->getTitle(),
                            'lot-badge' => '<span class="badge rounded-pill bg-warning"><i class="material-icons-outlined">report_problem</i> Paquete pendiente de devolución</span>',
                            'creation-date-human' => strftime(
                                CommonUtils::HUMAN_DATETIME_FORMAT_STRF,
                                $request->getCreationDate()->getTimestamp()
                            ),
                            'specification' => $specification,
                            'lot-id-badge' => $viewManager->fillTemplate(
                                'views/bookbank/common/part_id_badge_lot',
                                [ 'id' => $lot->getId() ]
                            ),
                            'lot-title-human' =>
                                Lot::statusToHuman($lot->getStatus())->getTitle(),
                            'lot-creation-date-human' => strftime(
                                CommonUtils::HUMAN_DATETIME_FORMAT_STRF,
                                $lot->getCreationDate()->getTimestamp()
                            ),
                            'lot-content-list-human' => $lotContentListHuman,
                            'edit-url' => $app->getUrl() . CheckInAssistantReturnLiteView::VIEW_ROUTE_BASE . $this->student->getId() . '/requests/' . $requestId . '/return/'
                        ]
                    );
                //}
            }
        }
    }

    private function initializePickupsContent(): void
    {
        $this->pickupsContent = '';

        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        $requestRepository = new RequestRepository($app->getDbConn());

        $requestIds = $requestRepository->findPickupsByStudentId($this->student->getId());
        
        $this->pickupsCount = count($requestIds);

        if (empty($requestIds)) {
            $this->pickupsContent .= $viewManager->fillTemplate(
                'views/bookbank/common/part_card_empty',
                []
            );
        } else {
            foreach ($requestIds as $requestId) {
                $request = $requestRepository->retrieveById($requestId, true);

                $specification = 
                    $request->getSpecification() == '' ? '(Vacía)' :
                    $request->getSpecification();

                //if ($request->getStatus() == Request::STATUS_PROCESSED) {
                    $lotRepository = new LotRepository($app->getDbConn());

                    $lot = $lotRepository->retrieveById($lotRepository->findByRequestId($requestId), true);

                    $lotContentListHuman = '';

                    if (empty($lot->getContents())) {
                        $lotContentListHuman = $viewManager->fillTemplate(
                            'views/bookbank/common/part_subject_list_empty', []
                        );
                    } else {
                        foreach ($lot->getContents() as $subject) {
                            $bookImageUrl = $subject->getBookImageUrl() ??
                                $app->getUrl() . '/img/graphic-default-book-image.svg';

                            $bookName = $subject->getBookName() ??
                                'Sin libro o libro no definido';
                                
                            $lotContentListHuman .= $viewManager->fillTemplate(
                                'views/bookbank/common/part_subject_list_item',
                                [
                                    'book-image-url' => $bookImageUrl,
                                    'title-human' =>
                                        $subject->getName() . ' de ' .
                                        DomainUtils::educationLevelToHuman($subject->getEducationLevel())->getTitle(),
                                    'book-isbn' => $subject->getBookIsbn(),
                                    'book-name' => $bookName
                                ]
                            );
                        }
                    }

                    $this->pickupsContent .= $viewManager->fillTemplate(
                        'views/bookbank/volunteer/view_check_in_assistant_student_overview_part_request_with_lot_editable_item',
                        [
                            'heading-id' => 'header-request-' . $requestId,
                            'body-id' => 'body-request-' . $requestId,
                            'id-badge' => $viewManager->fillTemplate(
                                'views/bookbank/common/part_id_badge_request',
                                [ 'id' => $requestId ]
                            ),
                            'title-human' => 'Solicitud de ' . DomainUtils::educationLevelToHuman($request->getEducationLevel())->getTitle(),
                            'status-human' => Request::statusToHuman($request->getStatus())->getTitle(),
                            'lot-badge' => '<span class="badge rounded-pill bg-success"><i class="material-icons-outlined">done</i> Paquete listo para recoger</span>',
                            'creation-date-human' => strftime(
                                CommonUtils::HUMAN_DATETIME_FORMAT_STRF,
                                $request->getCreationDate()->getTimestamp()
                            ),
                            'specification' => $specification,
                            'lot-id-badge' => $viewManager->fillTemplate(
                                'views/bookbank/common/part_id_badge_lot',
                                [ 'id' => $lot->getId() ]
                            ),
                            'lot-title-human' =>
                                Lot::statusToHuman($lot->getStatus())->getTitle(),
                            'lot-creation-date-human' => strftime(
                                CommonUtils::HUMAN_DATETIME_FORMAT_STRF,
                                $lot->getCreationDate()->getTimestamp()
                            ),
                            'lot-content-list-human' => $lotContentListHuman,
                            'edit-url' => $app->getUrl() . CheckInAssistantPickupLiteView::VIEW_ROUTE_BASE . $this->student->getId() . '/requests/' . $requestId . '/pickup/'
                        ]
                    );
                //}
            }
        }
    }

    private function getReturnsContent(): string
    {
        return $this->returnsContent;
    }

    private function getPickupsContent(): string
    {
        return $this->pickupsContent;
    }
}