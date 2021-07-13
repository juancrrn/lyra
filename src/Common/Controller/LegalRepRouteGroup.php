<?php

namespace Juancrrn\Lyra\Common\Controller;

use Exception;
use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\Controller\Controller;
use Juancrrn\Lyra\Common\Controller\RouteGroupModel;
use Juancrrn\Lyra\Common\View\LegalRep\RepresentedOverviewView;

/**
 * Vistas de usuarios con permisos de representante legal de estudiantes
 * (LegalRep)
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class LegalRepRouteGroup implements RouteGroupModel
{

    private $controllerInstance;

    public function __construct(Controller $controllerInstance)
    {
        $this->controllerInstance = $controllerInstance;
    }

    public function runAll(): void
    {
        $viewManager = App::getSingleton()->getViewManagerInstance();
        
        // Listado de estudiantes representados
        $this->controllerInstance->get(RepresentedOverviewView::VIEW_ROUTE, function () use ($viewManager) {
            $viewManager->render(new RepresentedOverviewView);
        });
        
        // Listado de paquetes de un estudiante representado
        $this->controllerInstance->get('/self/represented/([0-9]+)/book-lots/', function (int $studentId) use ($viewManager) {
            // TODO Comprobar que puede acceder al estudiante
            throw new Exception('Route declared but not implemented.');
        });
    }
}