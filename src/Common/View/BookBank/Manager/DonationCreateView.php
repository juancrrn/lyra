<?php

namespace Juancrrn\Lyra\Common\View\BookBank\Manager;

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\View\ViewModel;
use Juancrrn\Lyra\Domain\DomainUtils;
use Juancrrn\Lyra\Domain\StaticForm\BookBank\Manager\DonationCreateForm;
use Juancrrn\Lyra\Domain\User\User;
use Juancrrn\Lyra\Domain\User\UserRepository;

/**
 * Vista de creación de donación
 *
 * @package lyra
 * 
 * @author juancrrn
 *
 * @version 0.0.1
 */

class DonationCreateView extends ViewModel
{
    private const VIEW_RESOURCE_FILE    = 'views/bookbank/manager/view_donation_create';
    public  const VIEW_NAME             = 'Crear donación';
    public  const VIEW_ID               = 'bookbank-manager-donation-create';
    public  const VIEW_ROUTE_BASE       = '/bookbank/manage/students/';
    public  const VIEW_ROUTE            = self::VIEW_ROUTE_BASE . '([0-9])+/donations/create/';

    private $student;
    private $form;

    public function __construct(int $studentId)
    {
        $app = App::getSingleton();
        
        $sessionManager = $app->getSessionManagerInstance();

        $sessionManager->requirePermissionGroups([ User::NPG_BOOKBANK_MANAGER ]);

        $userRepository = new UserRepository($app->getDbConn());

        if (! $userRepository->findById($studentId)) {
            $app->getViewManagerInstance()->addErrorMessage('El parámetro de identificador de estudiante es inválido.', '');
        }

        $this->student = $userRepository->retrieveById($studentId);

        $this->form = new DonationCreateForm(self::VIEW_ROUTE_BASE . $this->student->getId() . '/donations/create/', $this->student->getId()); 

        $this->form->handle();

        $preloadedData = [
            'studentFullName' => $this->student->getFullName(),
            'creationDate' => 'Ahora mismo',
            'creatorName' => $app->getSessionManagerInstance()->getLoggedInUser()->getFullName(),
            'schoolYear' => DomainUtils::schoolYearToHuman($app->getSetting('school-year')),
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
            'back-to-student-overview-url' => $app->getUrl() . StudentOverviewView::VIEW_ROUTE_BASIC . $this->student->getId() . '/overview/',
            'donation-create-form-html' => $this->form->getHtml()
        ];
        
        $viewManager->renderTemplate(self::VIEW_RESOURCE_FILE, $filling);
    }
}