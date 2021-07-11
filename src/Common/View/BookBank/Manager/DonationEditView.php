<?php

namespace Juancrrn\Lyra\Common\View\BookBank\Manager;

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\CommonUtils;
use Juancrrn\Lyra\Common\View\ViewModel;
use Juancrrn\Lyra\Domain\BookBank\Donation\DonationRepository;
use Juancrrn\Lyra\Domain\BookBank\Request\RequestRepository;
use Juancrrn\Lyra\Domain\DomainUtils;
use Juancrrn\Lyra\Domain\StaticForm\BookBank\Manager\DonationEditForm;
use Juancrrn\Lyra\Domain\User\User;
use Juancrrn\Lyra\Domain\User\UserRepository;

/**
 * Vista de edición de donación
 *
 * @package lyra
 * 
 * @author juancrrn
 *
 * @version 0.0.1
 */

class DonationEditView extends ViewModel
{
    private const VIEW_RESOURCE_FILE    = 'views/bookbank/manager/view_donation_edit';
    public  const VIEW_NAME             = 'Editar donación';
    public  const VIEW_ID               = 'bookbank-manager-donation-edit';
    public  const VIEW_ROUTE_BASE       = '/bookbank/manage/donations/';
    public  const VIEW_ROUTE            = self::VIEW_ROUTE_BASE . '([0-9]+)/edit/';

    private $form;
    private $donation;

    public function __construct(int $itemId)
    {
        $app = App::getSingleton();
        
        $sessionManager = $app->getSessionManagerInstance();

        $sessionManager->requirePermissionGroups([ User::NPG_BOOKBANK_MANAGER ]);

        $donationRepository = new DonationRepository($app->getDbConn());

        if (! $donationRepository->findById($itemId)) {
            $app->getViewManagerInstance()->addErrorMessage('El parámetro de identificador de donación es inválido.', '');
        }

        $this->donation = $donationRepository->retrieveById($itemId, true);

        $userRepository = new UserRepository($app->getDbConn());

        $this->form = new DonationEditForm(self::VIEW_ROUTE_BASE . $itemId . '/edit/', $itemId); 

        $this->form->handle();

        // Retrieve again, just in case the form was submitted
        $this->donation = $donationRepository->retrieveById($itemId, true);

        $preloadedData = [
            'id' => $this->donation->getId(),
            'studentFullName' => $userRepository->retrieveById($this->donation->getStudentId())->getFullName(),
            'creationDate' => strftime(
                CommonUtils::HUMAN_DATETIME_FORMAT_STRF,
                $this->donation->getCreationDate()->getTimestamp()
            ),
            'creatorName' => $userRepository->retrieveById($this->donation->getCreatorId())->getFullName(),
            'educationLevel' => $this->donation->getEducationLevel(),
            'schoolYear' => DomainUtils::schoolYearToHuman($this->donation->getSchoolYear()),
            'contents' => $this->donation->getContents()
        ];

        $this->form->initialize($preloadedData);

        $this->name = self::VIEW_NAME;
        $this->id = self::VIEW_ID;
    }

    public function processContent(): void
    {
        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        $filling = [
            'app-url' => $app->getUrl(),
            'view-name' => $this->getName(),
            'back-to-student-overview-url' => $app->getUrl() . StudentOverviewView::VIEW_ROUTE_BASE . $this->donation->getStudentId() . '/overview/',
            'donation-id' => $this->donation->getId(),
            'donation-edit-form-html' => $this->form->getHtml()
        ];
        
        $viewManager->renderTemplate(self::VIEW_RESOURCE_FILE, $filling);
    }
}