<?php

namespace Juancrrn\Lyra\Common\View\LegalRep;

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

class RepresentedOverviewView extends ViewModel
{
    
    private const VIEW_RESOURCE_FILE    = 'views/legal_rep/represented_overview_view';
    public  const VIEW_NAME             = 'Representación legal';
    public  const VIEW_ID               = 'legal-rep-represented-overview-view';
    public  const VIEW_ROUTE            = '/legal-rep/overview/';

    public function __construct()
    {
        $app = App::getSingleton();

        $sessionManager = $app->getSessionManagerInstance();

        $sessionManager->requirePermissionGroups([ User::NPG_LEGALREP ]);

        $this->name = self::VIEW_NAME;
        $this->id = self::VIEW_ID;
    }

    public function processContent(): void
    {
        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        $filling = [
            'view-name' => $this->getName(),
            'app-name' => $app->getName()
        ];

        $viewManager->renderTemplate(self::VIEW_RESOURCE_FILE, $filling);
    }
}