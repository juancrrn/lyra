<?php

namespace Juancrrn\Lyra\Common\Controller;

use Exception;
use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\Controller\Controller;
use Juancrrn\Lyra\Common\Controller\RouteGroupModel;

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
        $viewManager = App::getSingleton()->getViewManagerInstance();
        
        /*
         *
         * Asignaturas
         * 
         */
        
        // Lista de asignaturas
        $this->controllerInstance->get('/book-bank/subjects/', function () use ($viewManager) {
            throw new Exception('Route declared but not implemented.');
        });
        
        /*
         *
         * Gestión manual de paquetes de estudiantes
         * 
         */
        
        // Búsqueda de estudiantes
        $this->controllerInstance->get('/book-bank/manual/students/', function () use ($viewManager) {
            throw new Exception('Route declared but not implemented.');
        });
        
        // Listado de paquetes de un estudiante
            // Con formularios AJAX para la edición
            // Y un menú para buscar otro estudiante
        $this->controllerInstance->get('/book-bank/manual/students/([0-9]+)/', function (int $studentId) use ($viewManager) {
            throw new Exception('Route declared but not implemented.');
        });
    }
}

?>