<?php

namespace Juancrrn\Lyra\Common\Controller;

use Exception;
use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\Controller\Controller;
use Juancrrn\Lyra\Common\Controller\RouteGroupModel;
use Juancrrn\Lyra\Common\View\AppManager\AppSettingsView;

/**
 * Vistas de usuarios con permisos de gestor de la aplicación (AppManager)
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class AppManagerRouteGroup implements RouteGroupModel
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
         * Gestión de usuarios
         * 
         */
        
        // Listado de usuarios
        $this->controllerInstance->get('/manage/users/', function () use ($viewManager) {
            throw new Exception('Route declared but not implemented.');
        });

        /*
         *
         * Otros
         * 
         */
        
        // Listado de grupos de permisos
        $this->controllerInstance->get('/manage/permission-groups/', function () use ($viewManager) {
            throw new Exception('Route declared but not implemented.');
        });
        
        // App settings
        $this->controllerInstance->get(AppSettingsView::VIEW_ROUTE, function () use ($viewManager) {
            $viewManager->render(new AppSettingsView);
        });
        
        // App settings (form POST)
        $this->controllerInstance->post(AppSettingsView::VIEW_ROUTE, function () use ($viewManager) {
            $viewManager->render(new AppSettingsView);
        });
    }
}