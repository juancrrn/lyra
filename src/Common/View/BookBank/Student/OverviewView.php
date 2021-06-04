<?php

namespace Juancrrn\Lyra\Common\View\BookBank\Student;

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

/**
 * Vista de resumen de banco de libros de un estudiante
 *
 * @package lyra
 * 
 * @author juancrrn
 *
 * @version 0.0.1
 */

class OverviewView extends ViewModel
{
    private const VIEW_RESOURCE_FILE    = 'views/bookbank/student/view_overview';
    public  const VIEW_NAME             = 'Mi banco de libros';
    public  const VIEW_ID               = 'bookbank-student-overview';
    public  const VIEW_ROUTE            = '/bookbank/student/overview/';

    public function __construct()
    {
        $sessionManager = App::getSingleton()->getSessionManagerInstance();

        $sessionManager->requirePermissionGroups([ User::NPG_STUDENT ]);

        $this->name = self::VIEW_NAME;
        $this->id = self::VIEW_ID;
    }

    public function processContent(): void
    {
        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        $loggedInUser = $app->getSessionManagerInstance()->getLoggedInUser();

        $requestRepository = new RequestRepository($app->getDbConn());

        $requestIds = $requestRepository->findByStudentId($loggedInUser->getId());
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

                    if ($lot->getStatus() == Lot::STATUS_READY) {
                        $lotBadge = '<span class="badge rounded-pill bg-success"><i class="material-icons">done</i> Paquete listo para recoger</span>';
                    } elseif ($lot->getStatus() == Lot::STATUS_PICKED_UP) {
                        $lotBadge = '<span class="badge rounded-pill bg-warning"><i class="material-icons">report_problem</i> Paquete pendiente de devolución</span>';
                    } else {
                        $lotBadge = '';
                    }

                    $requestLotListItemsHtml .= $viewManager->fillTemplate(
                        'views/bookbank/student/view_overview_part_request_with_lot_item',
                        [
                            'heading-id' => 'header-request-' . $requestId,
                            'body-id' => 'body-request-' . $requestId,
                            'id' => $requestId,
                            'title-human' => 'Solicitud de ' . DomainUtils::educationLevelToHuman($request->getEducationLevel())->getTitle(),
                            'status-human' => Request::statusToHuman($request->getStatus())->getTitle(),
                            'lot-badge' => $lotBadge,
                            'creation-date-human' => strftime(
                                CommonUtils::HUMAN_DATETIME_FORMAT_STRF,
                                $request->getCreationDate()->getTimestamp()
                            ),
                            'specification' => $specification,
                            'lot-id' => $lot->getId(),
                            'lot-title-human' =>
                                Lot::statusToHuman($lot->getStatus())->getTitle(),
                            'lot-creation-date-human' => strftime(
                                CommonUtils::HUMAN_DATETIME_FORMAT_STRF,
                                $lot->getCreationDate()->getTimestamp()
                            ),
                            'lot-content-list-human' => $lotContentListHuman
                        ]
                    );

                    /* Fin proceso paquete asociado */
                } else {
                    $requestLotListItemsHtml .= $viewManager->fillTemplate(
                        'views/bookbank/student/view_overview_part_request_item',
                        [
                            'heading-id' => 'header-request-' . $requestId,
                            'body-id' => 'body-request-' . $requestId,
                            'id' => $requestId,
                            'title-human' => 'Solicitud de ' . DomainUtils::educationLevelToHuman($request->getEducationLevel())->getTitle(),
                            'status-human' => Request::statusToHuman($request->getStatus())->getTitle(),
                            'lot-badge' => '',
                            'creation-date-human' => strftime(
                                CommonUtils::HUMAN_DATETIME_FORMAT_STRF,
                                $request->getCreationDate()->getTimestamp()
                            ),
                            'specification' => $specification
                        ]
                    );
                }
            }
        }

        $donationRepository = new DonationRepository($app->getDbConn());

        $donationIds = $donationRepository->findByStudentId($loggedInUser->getId());
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

                $donationListItemsHtml .= $viewManager->fillTemplate(
                    'views/bookbank/student/view_overview_part_donation_item',
                    [
                        'heading-id' => 'header-donation-' . $donationId,
                        'body-id' => 'body-donation-' . $donationId,
                        'id' => $donationId,
                        'title-human' => 'Donación de ' . DomainUtils::educationLevelToHuman($donation->getEducationLevel())->getTitle(),
                        'creation-date-human' => strftime(CommonUtils::HUMAN_DATETIME_FORMAT_STRF, $donation->getCreationDate()->getTimestamp()),
                        'content-list-human' => $donationContentListHuman
                    ]
                );
            }
        }

        $filling = [
            'app-name' => $app->getName(),
            'view-name' => $this->getName(),
            'request-count' => $requestIdsCount,
            'request-lot-list-human' => $requestLotListItemsHtml,
            'donation-count' => $donationIdsCount,
            'donation-list-human' => $donationListItemsHtml
        ];

        $viewManager->renderTemplate(self::VIEW_RESOURCE_FILE, $filling);
    }
}