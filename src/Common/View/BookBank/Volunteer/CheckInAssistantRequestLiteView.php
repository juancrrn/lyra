<?php

namespace Juancrrn\Lyra\Common\View\BookBank\Volunteer;

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\View\ViewModel;
use Juancrrn\Lyra\Domain\StaticForm\BookBank\Volunteer\CheckInAssistantRequestLiteForm;
use Juancrrn\Lyra\Domain\User\User;
use Juancrrn\Lyra\Domain\User\UserRepository;

/**
 * Check-in assistant request lite view
 *
 * @package lyra
 * 
 * @author juancrrn
 *
 * @version 0.0.1
 */

class CheckInAssistantRequestLiteView extends ViewModel
{
    
    private const VIEW_RESOURCE_FILE    = 'views/bookbank/volunteer/view_check_in_assistant_request_lite';
    public  const VIEW_NAME             = 'Solicitud - Asistente de recepción';
    public  const VIEW_ID               = 'bookbank-volunteering-check-in-assistant-request-lite';
    public  const VIEW_ROUTE_BASE       = '/bookbank/check-in/students/';
    public  const VIEW_ROUTE            = self::VIEW_ROUTE_BASE . '([0-9]+)/requests/create/';

    private $student;

    private $form;

    public function __construct(int $studentId)
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

        // TODO Verify if student can create a request

        $this->form = new CheckInAssistantRequestLiteForm(self::VIEW_ROUTE_BASE . $this->student->getId() . '/requests/create/', $this->student->getId()); 

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
            'view-name' => 'Crear solicitud',
            'assistant-view-name' => CheckInAssistantStudentOverviewView::VIEW_NAME,
            'back-to-overview-url' => CheckInAssistantStudentOverviewView::VIEW_ROUTE_BASE . $this->student->getId() . '/overview/',
            'student-card' => $this->generateStudentCard(),
            'form' => $this->form->getHtml()
        ];

        $viewManager->renderTemplate(self::VIEW_RESOURCE_FILE, $filling);
    }

    private function generateStudentCard(): string
    {
        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        if ($this->student->getRepresentativeId() == null) {
            $userRepresentativeHuman = '(No definido)';
        } else {
            $userRepository = new UserRepository($app->getDbConn());
            $representative = $userRepository
                ->retrieveById($this->student->getRepresentativeId());
            $userRepresentativeHuman = $representative->getFullName();
        }

        $studentCardFilling = [
            'accordion-id' => $this->student->getId(),
            'user-profile-picture' => $app->getUrl() . '/img/default-user-image.png',
            'user-id' => $this->student->getId(),
            'user-full-name' => $this->student->getFullName(),
            'user-gov-id' => $this->student->getGovId(true),
            'user-email-address' => $this->student->getEmailAddress(),
            'user-phone-number' => $this->student->getPhoneNumber(),
            'user-representative-name-human' => $userRepresentativeHuman,
            'user-status-human' => User::statusToHuman(
                $this->student->getStatus()
            )->getTitle()
        ];

        return $viewManager->fillTemplate(
            'views/bookbank/common/part_student_profile_card',
            $studentCardFilling
        );
    }
}