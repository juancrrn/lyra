<?php

namespace Juancrrn\Lyra\Common\View\BookBank\Volunteer;

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

class CheckInAssistantStudentSearchView extends ViewModel
{
    private const VIEW_RESOURCE_FILE    = 'views/bookbank/volunteer/view_check_in_assistant_student_search';
    public  const VIEW_NAME             = 'Asistente de recepción';
    public  const VIEW_ID               = 'bookbank-volunteering-check-in-assistant-student-search';
    public  const VIEW_ROUTE            = '/bookbank/check-in/students/search/';

    private $form;

    public function __construct()
    {
        $sessionManager = App::getSingleton()->getSessionManagerInstance();

        $sessionManager->requirePermissionGroups([ User::NPG_BOOKBANK_VOLUNTEER ]);

        $this->name = self::VIEW_NAME;
        $this->id = self::VIEW_ID;

        $this->form = new CheckInAssistantStudentSearchForm(self::VIEW_ROUTE); 

        $this->form->handle();
        $this->form->initialize();
    }

    public function processContent(): void
    {
        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        $filling = [
            'view-name' => $this->getName(),
            'student-search-form-html' => $this->form->getHtml()
        ];

        $viewManager->renderTemplate(self::VIEW_RESOURCE_FILE, $filling);
    }
}