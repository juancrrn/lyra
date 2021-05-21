<?php

namespace Juancrrn\Lyra\Common\Controller;

use Exception;
use Juancrrn\Lyra\Common\Api\BookBank\Manager\StudentSearchApi;
use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\Controller\Controller;
use Juancrrn\Lyra\Common\Controller\RouteGroupModel;
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
        
        /*
         *
         * Gestión manual de paquetes de estudiantes
         * 
         */
        
        // Búsqueda de estudiantes
        $this->controllerInstance->get('/bookbank/manage/students/', function () use ($viewManager) {
            $viewManager->render(new StudentSearchView);
        });
        
        // Búsqueda de estudiantes (POST del formulario)
        $this->controllerInstance->post('/bookbank/manage/students/', function () use ($viewManager) {
            $viewManager->render(new StudentSearchView);
        });
        
        // Búsqueda de estudiantes (POST del campo de búsqueda AJAX)
        //$this->controllerInstance->post('/bookbank/manage/students/search/', function () use ($apiManager) {
        //    $apiManager->call(new StudentSearchApi);
        //});
        
        // Listado de paquetes de un estudiante
            // Con formularios AJAX para la edición
            // Y un menú para buscar otro estudiante
        $this->controllerInstance->get('/bookbank/manage/students/([0-9]+)/overview/', function (int $studentId) use ($viewManager) {
            $viewManager->render(new StudentOverviewView($studentId));
        });
    }
}