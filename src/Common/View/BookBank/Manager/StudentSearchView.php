<?php

namespace Juancrrn\Lyra\Common\View\BookBank\Manager;

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\View\ViewModel;

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
    private const VIEW_RESOURCE_FILE    = 'bookbank/manager/view_student_search';
    public  const VIEW_NAME             = 'Gestión de banco de libros';
    public  const VIEW_ID               = 'bookbank-manage-students';
    public  const VIEW_ROUTE            = '/bookbank/manage/students/';

    public function __construct()
    {
        $sessionManager = App::getSingleton()->getSessionManagerInstance();

        $sessionManager->requireLoggedIn();

        $this->name = self::VIEW_NAME;
        $this->id = self::VIEW_ID;
    }

    public function processContent(): void
    {
        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        $filling = array(
            'view-name' => $this->getName(),
            'search-request-url' => $app->getUrl() . '/bookbank/manage/students/search/'
        );

        $viewManager->renderTemplate(self::VIEW_RESOURCE_FILE, $filling);
    }
}