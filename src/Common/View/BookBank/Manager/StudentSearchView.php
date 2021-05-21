<?php

namespace Juancrrn\Lyra\Common\View\BookBank\Manager;

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\View\ViewModel;
use Juancrrn\Lyra\Domain\StaticForm\BookBank\Manager\StudentSearchForm;
use Juancrrn\Lyra\Domain\User\User;

/**
 * Vista de resumen de banco de libros de un estudiante
 *
 * @package lyra
 * 
 * @author juancrrn
 *
 * @version 0.0.1
 */

class StudentSearchView extends ViewModel
{
    private const VIEW_RESOURCE_FILE    = 'views/bookbank/manager/view_student_search';
    public  const VIEW_NAME             = 'Gestión de banco de libros';
    public  const VIEW_ID               = 'bookbank-manage-student-search';
    public  const VIEW_ROUTE            = '/bookbank/manage/students/';

    private $form;

    public function __construct()
    {
        $sessionManager = App::getSingleton()->getSessionManagerInstance();

        $sessionManager->requirePermissionGroups([ User::NPG_BOOKBANK_MANAGER ]);

        $this->name = self::VIEW_NAME;
        $this->id = self::VIEW_ID;

        $this->form = new StudentSearchForm(self::VIEW_ROUTE); 

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