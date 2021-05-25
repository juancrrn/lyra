<?php

namespace Juancrrn\Lyra\Common\Controller;

use Exception;
use Juancrrn\Lyra\Common\Api\BookBank\Common\SubjectSearchApi;
use Juancrrn\Lyra\Common\Api\BookBank\Manager\StudentSearchApi;
use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\Controller\Controller;
use Juancrrn\Lyra\Common\Controller\RouteGroupModel;
use Juancrrn\Lyra\Common\View\BookBank\Manager\DonationEditView;
use Juancrrn\Lyra\Common\View\BookBank\Manager\RequestEditView;
use Juancrrn\Lyra\Common\View\BookBank\Manager\StudentOverviewView;
use Juancrrn\Lyra\Common\View\BookBank\Manager\StudentSearchView;

/**
 * Vistas de usuarios con permisos de gestor del banco de libros
 * (BookBankManager)
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class BookBankManagerRouteGroup implements RouteGroupModel
{

    private $controllerInstance;

    public function __construct(Controller $controllerInstance)
    {
        $this->controllerInstance = $controllerInstance;
    }

    public function runAll(): void
    {
        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        $apiManager = $app->getApiManagerInstance();
        
        /*
         *
         * Asignaturas
         * 
         */
        
        // Lista de asignaturas
        $this->controllerInstance->get('/bookbank/manage/subjects/', function () use ($viewManager) {
            throw new Exception('Route declared but not implemented.');
        });
        
        // Búsqueda AJAX de asignaturas
        $this->controllerInstance->post('/bookbank/manage/subjects/search/', function () use ($apiManager) {
            $apiManager->call(new SubjectSearchApi);
        });
        
        /*
         *
         * Gestión manual de paquetes de estudiantes
         * 
         */
        
        // Búsqueda de estudiantes
        $this->controllerInstance->get(StudentSearchView::VIEW_ROUTE, function () use ($viewManager) {
            $viewManager->render(new StudentSearchView);
        });
        
        // Búsqueda de estudiantes (POST del formulario)
        $this->controllerInstance->post(StudentSearchView::VIEW_ROUTE, function () use ($viewManager) {
            $viewManager->render(new StudentSearchView);
        });
        
        // Listado de paquetes de un estudiante
        $this->controllerInstance->get(StudentOverviewView::VIEW_ROUTE, function (int $itemId) use ($viewManager) {
            $viewManager->render(new StudentOverviewView($itemId));
        });
        
        // Edición de una donación
        $this->controllerInstance->get(DonationEditView::VIEW_ROUTE, function (int $itemId) use ($viewManager) {
            $viewManager->render(new DonationEditView($itemId));
        });
        
        // Edición de una donación (POST del formulario)
        $this->controllerInstance->post(DonationEditView::VIEW_ROUTE, function (int $itemId) use ($viewManager) {
            $viewManager->render(new DonationEditView($itemId));
        });
        
        // Edición de una solicitud (con o sin paquete)
        $this->controllerInstance->get(RequestEditView::VIEW_ROUTE, function (int $itemId) use ($viewManager) {
            $viewManager->render(new RequestEditView($itemId));
        });
    }
}