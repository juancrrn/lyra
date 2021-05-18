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
    private const VIEW_RESOURCE_FILE    = 'bookbank/student/view_overview';
    public  const VIEW_NAME             = 'Mi banco de libros';
    public  const VIEW_ID               = 'bookbank-student-overview';
    public  const VIEW_ROUTE            = '/bookbank/student/overview/';

    public function __construct()
    {
        $sessionManager = App::getSingleton()->getSessionManagerInstance();

        $sessionManager->requireLoggedIn();

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
            $requestLotListItemsHtml .= $viewManager->generateViewTemplateRender(
                'bookbank/student/view_overview_part_empty',
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

                    foreach ($lot->getContents() as $subject) {
                        $bookImageUrl = $subject->getBookImageUrl() ??
                            $app->getUrl() . '/img/graphic-default-book-image.svg';

                        $bookName = $subject->getBookName() ??
                            'Sin libro o libro no definido';
                            
                        $lotContentListHuman .= $viewManager->generateViewTemplateRender(
                            'bookbank/student/view_overview_part_subject_sub_item',
                            array(
                                'item-book-image-url' => $bookImageUrl,
                                'item-title-human' =>
                                    $subject->getName() . ' de ' .
                                    DomainUtils::educationLevelToHuman($subject->getEducationLevel())->getTitle(),
                                'item-book-isbn' => $subject->getBookIsbn(),
                                'item-book-name' => $bookName
                            )
                        );
                    }

                    if ($lot->getStatus() == Lot::STATUS_READY) {
                        $lotBadge = '<span class="badge rounded-pill bg-success"><i class="material-icons">done</i> Paquete listo para recoger</span>';
                    } elseif ($lot->getStatus() == Lot::STATUS_PICKED_UP) {
                        $lotBadge = '<span class="badge rounded-pill bg-warning"><i class="material-icons">report_problem</i> Paquete pendiente de devolución</span>';
                    } else {
                        $lotBadge = '';
                    }

                    $requestLotListItemsHtml .= $viewManager->generateViewTemplateRender(
                        'bookbank/student/view_overview_part_request_with_lot_item',
                        array(
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
                            'item-lot-content-list-human' => $lotContentListHuman
                        )
                    );

                    /* Fin proceso paquete asociado */
                } else {
                    $requestLotListItemsHtml .= $viewManager->generateViewTemplateRender(
                        'bookbank/student/view_overview_part_request_item',
                        array(
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
                            'item-specification' => $specification
                        )
                    );
                }
            }
        }

        $donationRepository = new DonationRepository($app->getDbConn());

        $donationIds = $donationRepository->findByStudentId($loggedInUser->getId());
        $donationIdsCount = count($donationIds);

        $donationListItemsHtml = '';

        if (empty($donationIds)) {
            $donationListItemsHtml .= $viewManager->generateViewTemplateRender(
                'bookbank/student/view_overview_part_empty',
                []
            );
        } else {
            foreach ($donationIds as $donationId) {
                $donation = $donationRepository->retrieveById($donationId, true);

                $donationContentListHuman = '';

                foreach ($donation->getContents() as $subject) {
                    $bookImageUrl = $subject->getBookImageUrl() ??
                        $app->getUrl() . '/img/graphic-default-book-image.svg';

                    $bookName = $subject->getBookName() ??
                        'Sin libro o libro no definido';
                        
                    $donationContentListHuman .= $viewManager->generateViewTemplateRender(
                        'bookbank/student/view_overview_part_subject_sub_item',
                        array(
                            'item-book-image-url' => $bookImageUrl,
                            'item-title-human' =>
                                $subject->getName() . ' de ' .
                                DomainUtils::educationLevelToHuman($subject->getEducationLevel())->getTitle(),
                            'item-book-isbn' => $subject->getBookIsbn(),
                            'item-book-name' => $bookName
                        )
                    );
                }

                $donationListItemsHtml .= $viewManager->generateViewTemplateRender(
                    'bookbank/student/view_overview_part_donation_item',
                    array(
                        'item-heading-id' => 'item-header-donation-' . $donationId,
                        'item-body-id' => 'item-body-donation-' . $donationId,
                        'item-id' => $donationId,
                        'item-title-human' => 'Donación de ' . DomainUtils::educationLevelToHuman($donation->getEducationLevel())->getTitle(),
                        'item-creation-date-human' => strftime(CommonUtils::HUMAN_DATETIME_FORMAT_STRF, $donation->getCreationDate()->getTimestamp()),
                        'item-content-list-human' => $donationContentListHuman
                    )
                );
            }
        }

        $filling = array(
            'app-name' => $app->getName(),
            'view-name' => $this->getName(),
            'request-count' => $requestIdsCount,
            'request-lot-list-human' => $requestLotListItemsHtml,
            'donation-count' => $donationIdsCount,
            'donation-list-human' => $donationListItemsHtml
        );

        $viewManager->renderTemplate(self::VIEW_RESOURCE_FILE, $filling);
    }
}