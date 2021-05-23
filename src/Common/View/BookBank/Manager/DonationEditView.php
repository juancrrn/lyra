<?php

namespace Juancrrn\Lyra\Common\View\BookBank\Manager;

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\View\ViewModel;
use Juancrrn\Lyra\Domain\BookBank\Donation\DonationRepository;
use Juancrrn\Lyra\Domain\BookBank\Request\RequestRepository;
use Juancrrn\Lyra\Domain\User\User;

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
    private const VIEW_RESOURCE_FILE    = 'views/view_donation_edit';
    public  const VIEW_NAME             = 'Editar donación';
    public  const VIEW_ID               = 'bookbank-manager-donation-edit';
    public  const VIEW_ROUTE_BASE       = '/bookbank/manage/donations/';
    public  const VIEW_ROUTE            = self::VIEW_ROUTE_BASE . '([0-9]+)/edit/';

    public $donation;

    public function __construct(int $itemId)
    {
        $app = App::getSingleton();
        
        $sessionManager = $app->getSessionManagerInstance();

        $sessionManager->requirePermissionGroups([ User::NPG_BOOKBANK_MANAGER ]);

        $donationRepository = new DonationRepository($app->getDbConn());

        if (! $donationRepository->findById($itemId)) {
            $app->getViewManagerInstance()->addErrorMessage('El parámetro de identificador de donación es inválido.', '');
        }

        $this->donation = $donationRepository->retrieveById($itemId);

        $this->name = self::VIEW_NAME;
        $this->id = self::VIEW_ID;
    }

    public function processContent(): void
    {
        dd($this->donation);
    }
}