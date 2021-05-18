<?php

namespace Juancrrn\Lyra\Common\Controller;

use Exception;
use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\Controller\Controller;
use Juancrrn\Lyra\Common\Controller\RouteGroupModel;
use Juancrrn\Lyra\Common\View\BookBank\Student\OverviewView;

/**
 * Vistas de usuarios con permisos de estudiante (Student) (puede ser receptor
 * de paquetes del banco de libros)
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class StudentRouteGroup implements RouteGroupModel
{

    private $controllerInstance;

    public function __construct(Controller $controllerInstance)
    {
        $this->controllerInstance = $controllerInstance;
    }

    public function runAll(): void
    {
        $viewManager = App::getSingleton()->getViewManagerInstance();
        
        // Listado de paquetes propios
        $this->controllerInstance->get('/bookbank/student/overview/', function () use ($viewManager) {
            $viewManager->render(new OverviewView);
        });
    }
}