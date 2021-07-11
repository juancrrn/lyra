<?php

namespace Juancrrn\Lyra\Common\View\BookBank\Volunteer;

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\View\ViewModel;
use Juancrrn\Lyra\Domain\User\User;
use Juancrrn\Lyra\Domain\User\UserRepository;

/**
 * Check-in assistant return lite view
 *
 * @package lyra
 * 
 * @author juancrrn
 *
 * @version 0.0.1
 */

class CheckInAssistantPickupLiteView extends ViewModel
{
    
    private const VIEW_RESOURCE_FILE    = 'views/bookbank/volunteer/view_check_in_assistant_pickup_lite';
    public  const VIEW_NAME             = 'Recogida - Asistente de recepción';
    public  const VIEW_ID               = 'bookbank-volunteering-check-in-assistant-pickup-lite';
    public  const VIEW_ROUTE_BASE       = '/bookbank/check-in/students/';
    public  const VIEW_ROUTE            = self::VIEW_ROUTE_BASE . '([0-9]+)/requests/([0-9]+)/pickup/';

    public function __construct(int $studentId, int $requestId)
    {
        $app = App::getSingleton();

        $sessionManager = $app->getSessionManagerInstance();

        $sessionManager->requirePermissionGroups([ User::NPG_BOOKBANK_VOLUNTEER ]);

        $userRepo = new UserRepository($app->getDbConn());

        if (! $userRepo->findById($studentId)) {
            $app->getViewManagerInstance()->addErrorMessage('El parámetro de identificador de usuario es inválido.', '');
        }

        $this->student = $userRepo->retrieveById($studentId, true);

        if (! $this->student->hasPermission(User::NPG_STUDENT)) {
            $app->getViewManagerInstance()->addErrorMessage('El parámetro de identificador de usuario es inválido.', '');
        }

        $this->name = self::VIEW_NAME;
        $this->id = self::VIEW_ID;
    }

    public function processContent(): void
    {
        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        $filling = [
            'view-name' => 'Recoger paquete',
            'assistant-view-name' => CheckInAssistantStudentOverviewView::VIEW_NAME,
            'back-to-overview-url' => CheckInAssistantStudentOverviewView::VIEW_ROUTE_BASE . $this->student->getId() . '/overview/',
            'student-card' => $this->student->generateCard()
        ];

        $viewManager->renderTemplate(self::VIEW_RESOURCE_FILE, $filling);
    }
}