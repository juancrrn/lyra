<?php

namespace Juancrrn\Lyra\Common\Controller;

use Exception;
use Juancrrn\Lyra\Common\Api\AppManager\PermissionGroupsApi;
use Juancrrn\Lyra\Common\Api\AppManager\UserSearchApi;
use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\Controller\Controller;
use Juancrrn\Lyra\Common\Controller\RouteGroupModel;
use Juancrrn\Lyra\Common\View\AppManager\AppSettingsView;
use Juancrrn\Lyra\Common\View\AppManager\UserCreateView;
use Juancrrn\Lyra\Common\View\AppManager\UserEditView;
use Juancrrn\Lyra\Common\View\AppManager\UserSearchView;

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
        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        $apiManager = $app->getApiManagerInstance();

        /*
         *
         * Gestión de usuarios
         * 
         */
        
        // User sarch view
        $this->controllerInstance->get(UserSearchView::VIEW_ROUTE, function () use ($viewManager) {
            $viewManager->render(new UserSearchView);
        });
        
        // User search API
        $this->controllerInstance->post(UserSearchApi::API_ROUTE, function () use ($apiManager) {
            $apiManager->call(new UserSearchApi);
        });
        
        // User creation
        $this->controllerInstance->get(UserCreateView::VIEW_ROUTE, function () use ($viewManager) {
            $viewManager->render(new UserCreateView);
        });
        
        // User creation form POST
        $this->controllerInstance->post(UserCreateView::VIEW_ROUTE, function () use ($viewManager) {
            $viewManager->render(new UserCreateView);
        });
        
        // User edition
        $this->controllerInstance->get(UserEditView::VIEW_ROUTE, function (int $userId) use ($viewManager) {
            $viewManager->render(new UserEditView($userId));
        });
        
        // User edition form POST
        $this->controllerInstance->post(UserEditView::VIEW_ROUTE, function (int $userId) use ($viewManager) {
            $viewManager->render(new UserEditView($userId));
        });

        // Permission groups API
        $this->controllerInstance->post(PermissionGroupsApi::API_ROUTE, function () use ($apiManager) {
            $apiManager->call(new PermissionGroupsApi);
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