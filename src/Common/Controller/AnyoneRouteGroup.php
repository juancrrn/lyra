<?php

namespace Juancrrn\Lyra\Common\Controller;

use Exception;
use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\Controller\Controller;
use Juancrrn\Lyra\Common\Controller\RouteGroupModel;
use Juancrrn\Lyra\Common\View\Auth\LoginView;
use Juancrrn\Lyra\Common\View\Error\Error404View;
use Juancrrn\Lyra\Common\View\Home\DashboardView;
use Juancrrn\Lyra\Common\View\Home\HomeView;

/**
 * Vistas de usuarios de cualquier tipo (todos)
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class AnyoneRouteGroup implements RouteGroupModel
{

    private $controllerInstance;

    public function __construct(Controller $controllerInstance)
    {
        $this->controllerInstance = $controllerInstance;
    }

    public function runAll(): void
    {
        $viewManager = App::getSingleton()->getViewManagerInstance();
        
        // Página de inicio
        $this->controllerInstance->get('/?', function () use ($viewManager) {
            $sessionManager = App::getSingleton()->getSessionManagerInstance();
    
            if ($sessionManager->isLoggedIn()) {
                $viewManager->render(new DashboardView);
            } else {
                $viewManager->render(new HomeView);
            }
        });
        
        // Inicio de sesión
        $this->controllerInstance->get('/auth/login/', function () use ($viewManager) {
            $viewManager->render(new LoginView);
        });
        
        // Inicio de sesión (POST del formulario)
        $this->controllerInstance->post('/auth/login/', function () use ($viewManager) {
            $viewManager->render(new LoginView);
        });
        
        // Restablecimiento de contraseña
        $this->controllerInstance->get('/auth/reset/', function () use ($viewManager) {
            throw new Exception('Route declared but not implemented.');
        });
        
        // Restablecimiento de contraseña (POST del formulario)
        $this->controllerInstance->post('/auth/reset/', function () use ($viewManager) {
            throw new Exception('Route declared but not implemented.');
        });
    }

    public function runDefault(): void
    {
        $viewManager = App::getSingleton()->getViewManagerInstance();
        
        // Ruta por defecto (error 404)
        $this->controllerInstance->default(function () use ($viewManager) {
            $viewManager->render(new Error404View);
        });
    }
}