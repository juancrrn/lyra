<?php

namespace Juancrrn\Lyra\Common\View\BookBank\Volunteer;

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\View\ViewModel;
use Juancrrn\Lyra\Domain\BookBank\Request\RequestRepository;
use Juancrrn\Lyra\Domain\StaticForm\BookBank\Volunteer\CheckInAssistantReturnLiteForm;
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

class CheckInAssistantReturnLiteView extends ViewModel
{
    
    private const VIEW_RESOURCE_FILE    = 'views/bookbank/volunteer/view_check_in_assistant_return_lite';
    public  const VIEW_NAME             = 'Devolución - Asistente de recepción';
    public  const VIEW_ID               = 'bookbank-volunteering-check-in-assistant-return-lite';
    public  const VIEW_ROUTE_BASE       = '/bookbank/check-in/students/';
    public  const VIEW_ROUTE            = self::VIEW_ROUTE_BASE . '([0-9]+)/requests/([0-9]+)/return/';

    private $student;

    private $form;

    public function __construct(int $studentId, int $requestId)
    {
        $app = App::getSingleton();

        $sessionManager = $app->getSessionManagerInstance();

        $sessionManager->requirePermissionGroups([ User::NPG_BOOKBANK_VOLUNTEER ]);

        $viewManager = $app->getViewManagerInstance();

        $userRepo = new UserRepository($app->getDbConn());

        if (! $userRepo->findById($studentId)) {
            $viewManager->addErrorMessage('El parámetro de identificador de usuario es inválido.', '');
        }

        $this->student = $userRepo->retrieveById($studentId, true);

        if (! $this->student->hasPermission(User::NPG_STUDENT)) {
            $viewManager->addErrorMessage('El parámetro de identificador de usuario es inválido.', '');
        }

        $requestRepo = new RequestRepository($app->getDbConn());

        if (
            // Request exists
            ! $requestRepo->findById($requestId) ||
            // Request is associated with student
            $requestRepo->retrieveById($requestId)->getStudentId() != $studentId
        ) {
            $viewManager->addErrorMessage(
                'El parámetro de identificador de solicitud es inválido.',
                CheckInAssistantStudentOverviewView::VIEW_ROUTE_BASE . $this->student->getId() . '/overview/'
            );
        }

        $this->form = new CheckInAssistantReturnLiteForm(
            self::VIEW_ROUTE_BASE . $studentId . '/requests/' . $requestId . '/return/',
            $requestId
        );

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
            'view-name' => 'Devolver paquete',
            'assistant-view-name' => CheckInAssistantStudentOverviewView::VIEW_NAME,
            'back-to-overview-url' => CheckInAssistantStudentOverviewView::VIEW_ROUTE_BASE . $this->student->getId() . '/overview/',
            'student-card' => $this->student->generateCard(),
            'form' => $this->form->getHtml()
        ];

        $viewManager->renderTemplate(self::VIEW_RESOURCE_FILE, $filling);
    }
}