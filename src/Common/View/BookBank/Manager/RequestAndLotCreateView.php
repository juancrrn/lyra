<?php

namespace Juancrrn\Lyra\Common\View\BookBank\Manager;

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\View\ViewModel;
use Juancrrn\Lyra\Domain\DomainUtils;
use Juancrrn\Lyra\Domain\StaticForm\BookBank\Manager\DonationCreateForm;
use Juancrrn\Lyra\Domain\StaticForm\BookBank\Manager\RequestAndLotCreateForm;
use Juancrrn\Lyra\Domain\User\User;
use Juancrrn\Lyra\Domain\User\UserRepository;

/**
 * Request creation view
 *
 * @package lyra
 * 
 * @author juancrrn
 *
 * @version 0.0.1
 */

class RequestAndLotCreateView extends ViewModel
{
    private const VIEW_RESOURCE_FILE    = 'views/bookbank/manager/view_request_and_lot_create';
    public  const VIEW_NAME             = 'Crear solicitud y paquete';
    public  const VIEW_ID               = 'bookbank-manager-request-and-lot-create';
    public  const VIEW_ROUTE_BASE       = '/bookbank/manage/students/';
    public  const VIEW_ROUTE            = self::VIEW_ROUTE_BASE . '([0-9]+)/requests/create/';

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

        $this->form = new RequestAndLotCreateForm(self::VIEW_ROUTE_BASE . $this->student->getId() . '/donations/create/', $this->student->getId()); 

        $this->form->handle();

        $this->form->initialize();

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
            'back-to-student-overview-url' => $app->getUrl() . StudentOverviewView::VIEW_ROUTE_BASE . $this->student->getId() . '/overview/',
            'request-and-lot-create-form-html' => $this->form->getHtml()
        ];
        
        $viewManager->renderTemplate(self::VIEW_RESOURCE_FILE, $filling);
    }
}