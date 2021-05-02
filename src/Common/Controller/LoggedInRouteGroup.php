<?php

namespace Juancrrn\Lyra\Common\Controller;

use Exception;
use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\Controller\Controller;
use Juancrrn\Lyra\Common\Controller\RouteGroupModel;
use Juancrrn\Lyra\Domain\StaticForm\Auth\LogoutForm;

/**
 * Vistas de usuarios que hayan iniciado sesión
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class LoggedInRouteGroup implements RouteGroupModel
{

    private $controllerInstance;

    public function __construct(Controller $controllerInstance)
    {
        $this->controllerInstance = $controllerInstance;
    }

    public function runAll(): void
    {
        $viewManager = App::getSingleton()->getViewManagerInstance();
        
        // Cierre de sesión (POST del formulario)
        $this->controllerInstance->post('/auth/logout/', function () use ($viewManager) {
            (new LogoutForm('/auth/logout/'))->handle();
        });
        
        // Perfil propio
        $this->controllerInstance->get('/self/profile/', function () use ($viewManager) {
            throw new Exception('Route declared but not implemented.');
        });
    }
}