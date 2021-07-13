<?php

namespace Juancrrn\Lyra\Common\View\BookBank\Volunteer;

use Juancrrn\Lyra\Common\Api\BookBank\Volunteer\StudentSearchApi;
use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\View\ViewModel;
use Juancrrn\Lyra\Domain\StaticForm\BookBank\Manager\StudentSearchForm;
use Juancrrn\Lyra\Domain\StaticForm\BookBank\Volunteer\CheckInAssistantStudentSearchForm;
use Juancrrn\Lyra\Domain\StaticForm\BookBank\Volunteer\LotFillingAssistantRankAndFillForm;
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

class LotFillingAssistantRunView extends ViewModel
{
    
    private const VIEW_RESOURCE_FILE    = 'views/bookbank/volunteer/view_lot_filling_assistant_run';
    public  const VIEW_NAME             = 'Asistente de empaquetado';
    public  const VIEW_ID               = 'bookbank-volunteer-lot-filling-assistant-run';
    public  const VIEW_ROUTE            = '/bookbank/lot-filling/run/';

    private $form;

    public function __construct()
    {
        $app = App::getSingleton();

        $sessionManager = $app->getSessionManagerInstance();

        $sessionManager->requirePermissionGroups([ User::NPG_BOOKBANK_VOLUNTEER ]);

        $this->form = new LotFillingAssistantRankAndFillForm(self::VIEW_ROUTE);

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
            'view-name' => $this->getName(),
            'form' => $this->form->getHtml()
        ];

        $viewManager->renderTemplate(self::VIEW_RESOURCE_FILE, $filling);
    }
}