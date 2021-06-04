<?php

namespace Juancrrn\Lyra\Common\View\BookBank\Manager;

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\CommonUtils;
use Juancrrn\Lyra\Common\View\ViewModel;
use Juancrrn\Lyra\Domain\BookBank\Donation\DonationRepository;
use Juancrrn\Lyra\Domain\BookBank\Lot\LotRepository;
use Juancrrn\Lyra\Domain\BookBank\Request\Request;
use Juancrrn\Lyra\Domain\BookBank\Request\RequestRepository;
use Juancrrn\Lyra\Domain\DomainUtils;
use Juancrrn\Lyra\Domain\StaticForm\BookBank\Manager\DonationEditForm;
use Juancrrn\Lyra\Domain\StaticForm\BookBank\Manager\RequestAndLotEditForm;
use Juancrrn\Lyra\Domain\User\User;
use Juancrrn\Lyra\Domain\User\UserRepository;

/**
 * Request edition view
 *
 * @package lyra
 * 
 * @author juancrrn
 *
 * @version 0.0.1
 */

class RequestAndLotEditView extends ViewModel
{
    private const VIEW_RESOURCE_FILE    = 'views/bookbank/manager/view_request_and_lot_edit';
    public  const VIEW_NAME             = 'Editar solicitud y paquete';
    public  const VIEW_ID               = 'bookbank-manager-request-and-lot-edit';
    public  const VIEW_ROUTE_BASE       = '/bookbank/manage/requests/';
    public  const VIEW_ROUTE            = self::VIEW_ROUTE_BASE . '([0-9]+)/edit/';

    private $form;
    private $request;

    public function __construct(int $itemId)
    {
        $app = App::getSingleton();
        
        $sessionManager = $app->getSessionManagerInstance();

        $sessionManager->requirePermissionGroups([ User::NPG_BOOKBANK_MANAGER ]);

        $requestRepository = new RequestRepository($app->getDbConn());

        if (! $requestRepository->findById($itemId)) {
            $app->getViewManagerInstance()->addErrorMessage('El parámetro de identificador de solicitud es inválido.', '');
        }

        $this->form = new RequestAndLotEditForm(self::VIEW_ROUTE_BASE . $itemId . '/edit/', $itemId); 

        $this->form->handle();

        // Retrieve the request after handling the form
        $this->request = $requestRepository->retrieveById($itemId, true);

        if ($this->request->getStatus() == Request::STATUS_PROCESSED) {
            $lotRepository = new LotRepository($app->getDbConn());

            $lot = $lotRepository->retrieveById($lotRepository->findByRequestId($this->request->getId()), true);
        } else {
            $lot = null;
        }

        $preloadedData = [
            'request' => $this->request,
            'lot' => $lot
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
            'back-to-student-overview-url' => $app->getUrl() . StudentOverviewView::VIEW_ROUTE_BASIC . $this->request->getStudentId() . '/overview/',
            'request-id' => $this->request->getId(),
            'request-edit-form-html' => $this->form->getHtml()
        ];
        
        $viewManager->renderTemplate(self::VIEW_RESOURCE_FILE, $filling);
    }
}