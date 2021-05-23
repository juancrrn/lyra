<?php

namespace Juancrrn\Lyra\Common\View\BookBank\Manager;

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\CommonUtils;
use Juancrrn\Lyra\Common\View\ViewModel;
use Juancrrn\Lyra\Domain\BookBank\Donation\DonationRepository;
use Juancrrn\Lyra\Domain\BookBank\Lot\Lot;
use Juancrrn\Lyra\Domain\BookBank\Lot\LotRepository;
use Juancrrn\Lyra\Domain\BookBank\Request\Request;
use Juancrrn\Lyra\Domain\BookBank\Request\RequestRepository;
use Juancrrn\Lyra\Domain\DomainUtils;
use Juancrrn\Lyra\Domain\StaticForm\BookBank\Manager\StudentSearchForm;
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

class StudentOverviewView extends ViewModel
{
    private const VIEW_RESOURCE_FILE    = 'views/bookbank/manager/view_student_overview';
    public  const VIEW_NAME             = 'Gestión de estudiante en banco de libros';
    public  const VIEW_ID               = 'bookbank-manage-student-overview';
    public  const VIEW_ROUTE_BASIC      = '/bookbank/manage/students/';
    public  const VIEW_ROUTE            = self::VIEW_ROUTE_BASIC . '([0-9]+)/overview/';

    private $student;

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
        $studentCardHtml = $this->generateStudentCardPart();

        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        $requestRepository = new RequestRepository($app->getDbConn());

        $requestIds = $requestRepository->findByStudentId($this->student->getId());
        $requestIdsCount = count($requestIds);

        $requestLotListItemsHtml = '';

        if (empty($requestIds)) {
            $requestLotListItemsHtml .= $viewManager->fillTemplate(
                'views/bookbank/common/part_card_empty',
                []
            );
        } else {
            foreach ($requestIds as $requestId) {
                $request = $requestRepository->retrieveById($requestId, true);

                $specification = 
                    $request->getSpecification() == '' ? '(Vacía)' :
                    $request->getSpecification();

                if ($request->getStatus() == Request::STATUS_PROCESSED) {
                    /* Inicio proceso paquete asociado */

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
                                    'item-book-image-url' => $bookImageUrl,
                                    'item-title-human' =>
                                        $subject->getName() . ' de ' .
                                        DomainUtils::educationLevelToHuman($subject->getEducationLevel())->getTitle(),
                                    'item-book-isbn' => $subject->getBookIsbn(),
                                    'item-book-name' => $bookName
                                ]
                            );
                        }
                    }

                    if ($lot->getStatus() == Lot::STATUS_READY) {
                        $lotBadge = '<span class="badge rounded-pill bg-success"><i class="material-icons">done</i> Paquete listo para recoger</span>';
                    } elseif ($lot->getStatus() == Lot::STATUS_PICKED_UP) {
                        $lotBadge = '<span class="badge rounded-pill bg-warning"><i class="material-icons">report_problem</i> Paquete pendiente de devolución</span>';
                    } else {
                        $lotBadge = '';
                    }

                    $requestLotListItemsHtml .= $viewManager->fillTemplate(
                        'views/bookbank/manager/view_overview_part_request_with_lot_editable_item',
                        [
                            'item-heading-id' => 'item-header-request-' . $requestId,
                            'item-body-id' => 'item-body-request-' . $requestId,
                            'item-id' => $requestId,
                            'item-title-human' => 'Solicitud de ' . DomainUtils::educationLevelToHuman($request->getEducationLevel())->getTitle(),
                            'item-status-human' => Request::statusToHuman($request->getStatus())->getTitle(),
                            'item-lot-badge' => $lotBadge,
                            'item-creation-date-human' => strftime(
                                CommonUtils::HUMAN_DATETIME_FORMAT_STRF,
                                $request->getCreationDate()->getTimestamp()
                            ),
                            'item-specification' => $specification,
                            'item-lot-id' => $lot->getId(),
                            'item-lot-title-human' =>
                                Lot::statusToHuman($lot->getStatus())->getTitle() . ' de ' .
                                DomainUtils::educationLevelToHuman($lot->getEducationLevel())->getTitle(),
                            'item-lot-creation-date-human' => strftime(
                                CommonUtils::HUMAN_DATETIME_FORMAT_STRF,
                                $lot->getCreationDate()->getTimestamp()
                            ),
                            'item-lot-content-list-human' => $lotContentListHuman,
                            'item-edit-url' => $app->getUrl() . RequestEditView::VIEW_ROUTE_BASE . $requestId . '/edit/'
                        ]
                    );

                    /* Fin proceso paquete asociado */
                } else {
                    $requestLotListItemsHtml .= $viewManager->fillTemplate(
                        'views/bookbank/manager/view_overview_part_request_editable_item',
                        [
                            'item-heading-id' => 'item-header-request-' . $requestId,
                            'item-body-id' => 'item-body-request-' . $requestId,
                            'item-id' => $requestId,
                            'item-title-human' => 'Solicitud de ' . DomainUtils::educationLevelToHuman($request->getEducationLevel())->getTitle(),
                            'item-status-human' => Request::statusToHuman($request->getStatus())->getTitle(),
                            'item-lot-badge' => '',
                            'item-creation-date-human' => strftime(
                                CommonUtils::HUMAN_DATETIME_FORMAT_STRF,
                                $request->getCreationDate()->getTimestamp()
                            ),
                            'item-specification' => $specification,
                            'item-edit-url' => $app->getUrl() . RequestEditView::VIEW_ROUTE_BASE . $requestId . '/edit/'
                        ]
                    );
                }
            }
        }

        $donationRepository = new DonationRepository($app->getDbConn());

        $donationIds = $donationRepository->findByStudentId($this->student->getId());
        $donationIdsCount = count($donationIds);

        $donationListItemsHtml = '';

        if (empty($donationIds)) {
            $donationListItemsHtml .= $viewManager->fillTemplate(
                'views/bookbank/common/part_card_empty',
                []
            );
        } else {
            foreach ($donationIds as $donationId) {
                $donation = $donationRepository->retrieveById($donationId, true);

                $donationContentListHuman = '';

                if (empty($donation->getContents())) {
                    $donationContentListHuman = $viewManager->fillTemplate(
                        'views/bookbank/common/part_subject_list_empty', []
                    );
                } else {
                    foreach ($donation->getContents() as $subject) {
                        $bookImageUrl = $subject->getBookImageUrl() ??
                            $app->getUrl() . '/img/graphic-default-book-image.svg';

                        $bookName = $subject->getBookName() ??
                            'Sin libro o libro no definido';
                            
                        $donationContentListHuman .= $viewManager->fillTemplate(
                            'views/bookbank/common/part_subject_list_item',
                            [
                                'item-book-image-url' => $bookImageUrl,
                                'item-title-human' =>
                                    $subject->getName() . ' de ' .
                                    DomainUtils::educationLevelToHuman($subject->getEducationLevel())->getTitle(),
                                'item-book-isbn' => $subject->getBookIsbn(),
                                'item-book-name' => $bookName
                            ]
                        );
                    }
                }

                $donationListItemsHtml .= $viewManager->fillTemplate(
                    'views/bookbank/manager/view_overview_part_donation_editable_item',
                    [
                        'item-heading-id' => 'item-header-donation-' . $donationId,
                        'item-body-id' => 'item-body-donation-' . $donationId,
                        'item-id' => $donationId,
                        'item-title-human' => 'Donación de ' . DomainUtils::educationLevelToHuman($donation->getEducationLevel())->getTitle(),
                        'item-creation-date-human' => strftime(CommonUtils::HUMAN_DATETIME_FORMAT_STRF, $donation->getCreationDate()->getTimestamp()),
                        'item-content-list-human' => $donationContentListHuman,
                        'item-edit-url' => $app->getUrl() . DonationEditView::VIEW_ROUTE_BASE . $donationId . '/edit/'
                    ]
                );
            }
        }

        $filling = [
            'app-name' => $app->getName(),
            'view-name' => $this->getName(),
            'student-card-html' => $studentCardHtml,
            'request-count' => $requestIdsCount,
            'request-lot-list-human' => $requestLotListItemsHtml,
            'donation-count' => $donationIdsCount,
            'donation-list-human' => $donationListItemsHtml
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
}