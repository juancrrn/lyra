<?php

namespace Juancrrn\Lyra\Common\View\BookBank\Manager;

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\View\ViewModel;
use Juancrrn\Lyra\Domain\StaticForm\BookBank\Manager\StudentSearchForm;
use Juancrrn\Lyra\Domain\User\User;
use Juancrrn\Lyra\Domain\User\UserRepository;

/**
 * Vista de resumen de banco de libros de un estudiante
 *
 * @package lyra
 * 
 * @author juancrrn
 *
 * @version 0.0.1
 */

class StudentOverviewView extends ViewModel
{
    private const VIEW_RESOURCE_FILE    = 'views/bookbank/manager/view_student_overview';
    public  const VIEW_NAME             = 'Gestión de estudiante en banco de libros';
    public  const VIEW_ID               = 'bookbank-manage-student-overview';
    public  const VIEW_ROUTE_BASIC      = '/bookbank/manage/students/';
    public  const VIEW_ROUTE            = self::VIEW_ROUTE_BASIC . '([0-9]+)/overview/';

    private $student;

    public function __construct(int $studentId)
    {
        $app = App::getSingleton();

        $sessionManager = $app->getSessionManagerInstance();

        $sessionManager->requirePermissionGroups([ User::NPG_BOOKBANK_MANAGER ]);

        $this->name = self::VIEW_NAME;
        $this->id = self::VIEW_ID;

        $userRepository = new UserRepository($app->getDbConn());

        if (! $userRepository->findById($studentId)) {
            $app->getViewManagerInstance()->addErrorMessage('El parámetro de identificador de usuario es inválido.', '');
        }

        $this->student = $userRepository->retrieveById($studentId, true);

        if (! $this->student->hasPermission(User::NPG_STUDENT)) {
            $app->getViewManagerInstance()->addErrorMessage('El parámetro de identificador de usuario es inválido.', '');
        }
    }

    public function processContent(): void
    {
        dd($this->student);
        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        $filling = [
            'view-name' => $this->getName(),
            'student-search-form-html' => $this->form->getHtml()
        ];

        $viewManager->renderTemplate(self::VIEW_RESOURCE_FILE, $filling);
    }
}