<?php

namespace Juancrrn\Lyra\Common\View\BookBank\Volunteer;

use Juancrrn\Lyra\Common\Api\BookBank\Volunteer\StudentSearchApi;
use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\View\ViewModel;
use Juancrrn\Lyra\Domain\StaticForm\BookBank\Manager\StudentSearchForm;
use Juancrrn\Lyra\Domain\StaticForm\BookBank\Volunteer\CheckInAssistantStudentSearchForm;
use Juancrrn\Lyra\Domain\User\User;

/**
 * Check-in assistant student search view
 *
 * @package lyra
 * 
 * @author juancrrn
 *
 * @version 0.0.1
 */

class LotFillingAssistantHomeView extends ViewModel
{
    
    private const VIEW_RESOURCE_FILE    = 'views/bookbank/volunteer/view_lot_filling_assistant_home';
    public  const VIEW_NAME             = 'Asistente de empaquetado';
    public  const VIEW_ID               = 'bookbank-volunteer-lot-filling-assistant-home';
    public  const VIEW_ROUTE            = '/bookbank/lot-filling/home/';

    public function __construct()
    {
        $sessionManager = App::getSingleton()->getSessionManagerInstance();

        $sessionManager->requirePermissionGroups([ User::NPG_BOOKBANK_VOLUNTEER ]);

        $this->name = self::VIEW_NAME;
        $this->id = self::VIEW_ID;
    }

    public function processContent(): void
    {
        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        $filling = [
            'view-name' => $this->getName()
        ];

        $viewManager->renderTemplate(self::VIEW_RESOURCE_FILE, $filling);
    }
}