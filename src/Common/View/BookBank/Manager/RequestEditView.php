<?php

namespace Juancrrn\Lyra\Common\View\BookBank\Manager;

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\View\ViewModel;
use Juancrrn\Lyra\Domain\BookBank\Lot\LotRepository;
use Juancrrn\Lyra\Domain\BookBank\Request\Request;
use Juancrrn\Lyra\Domain\BookBank\Request\RequestRepository;
use Juancrrn\Lyra\Domain\User\User;

/**
 * Vista de edición de solicitud
 *
 * @package lyra
 * 
 * @author juancrrn
 *
 * @version 0.0.1
 */

class RequestEditView extends ViewModel
{
    private const VIEW_RESOURCE_FILE    = 'views/view_request_edit';
    public  const VIEW_NAME             = 'Editar solicitud';
    public  const VIEW_ID               = 'bookbank-manager-request-edit';
    public  const VIEW_ROUTE_BASE       = '/bookbank/manage/requests/';
    public  const VIEW_ROUTE            = self::VIEW_ROUTE_BASE . '([0-9]+)/edit/';

    public $request;
    public $lot = null;

    public function __construct(int $itemId)
    {
        $app = App::getSingleton();
        
        $sessionManager = $app->getSessionManagerInstance();

        $sessionManager->requirePermissionGroups([ User::NPG_BOOKBANK_MANAGER ]);

        $requestRepository = new RequestRepository($app->getDbConn());

        if (! $requestRepository->findById($itemId)) {
            $app->getViewManagerInstance()->addErrorMessage('El parámetro de identificador de solicitud es inválido.', '');
        }

        $this->request = $requestRepository->retrieveById($itemId, true);

        if ($this->request->getStatus() == Request::STATUS_PROCESSED) {
            $lotRepository = new LotRepository($app->getDbConn());

            $this->lot = $lotRepository->retrieveById($lotRepository->findByRequestId($itemId), true);
        }

        $this->name = self::VIEW_NAME;
        $this->id = self::VIEW_ID;
    }

    public function processContent(): void
    {
        var_dump($this->request);
        dd($this->lot);
    }
}