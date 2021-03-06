<?php

namespace Juancrrn\Lyra\Common\Controller;

use Juancrrn\Lyra\Common\Api\TimePlanner\Appointment\CreateApi;
use Juancrrn\Lyra\Common\Api\TimePlanner\Slot\AvailableDatesApi;
use Juancrrn\Lyra\Common\Api\TimePlanner\Slot\AvailableTimesApi;
use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\Controller\Controller;
use Juancrrn\Lyra\Common\Controller\RouteGroupModel;
use Juancrrn\Lyra\Common\Http;
use Juancrrn\Lyra\Common\View\Auth\LoginView;
use Juancrrn\Lyra\Common\View\Auth\PasswordResetProcessView;
use Juancrrn\Lyra\Common\View\Auth\PasswordResetRequestView;
use Juancrrn\Lyra\Common\View\Error\Error404View;
use Juancrrn\Lyra\Common\View\Home\DashboardView;
use Juancrrn\Lyra\Common\View\Home\HomeView;
use Juancrrn\Lyra\Common\View\TimePlanner\LandingView;

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
        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        $apiManager = $app->getApiManagerInstance();
        
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
        
        // Solicitud de restablecimiento de contraseña
        $this->controllerInstance->get('/auth/reset/request/', function () use ($viewManager) {
            $viewManager->render(new PasswordResetRequestView);
        });
        
        // Solicitud de restablecimiento de contraseña (POST del formulario)
        $this->controllerInstance->post('/auth/reset/request/', function () use ($viewManager) {
            $viewManager->render(new PasswordResetRequestView);
        });
        
        // Proceso de restablecimiento de contraseña
        $this->controllerInstance->get('/auth/reset/process/([0-9a-zA-Z]*)', function ($token) use ($viewManager) {
            $viewManager->render(new PasswordResetProcessView($token));
        });
        
        // Proceso de restablecimiento de contraseña (POST del formulario)
        $this->controllerInstance->post('/auth/reset/process/([0-9a-zA-Z]*)', function ($token) use ($viewManager) {
            $viewManager->render(new PasswordResetProcessView($token));
        });

        // Express time planner functionality route redirect
        $this->controllerInstance->get('/timeplanner/?', function () use ($app) {
            Http::redirect($app->getUrl() . LandingView::VIEW_ROUTE);
        });

        // Express time planner functionality
        $this->controllerInstance->get(LandingView::VIEW_ROUTE, function () use ($viewManager) {
            $viewManager->render(new LandingView);
        });

        // Slot available dates API
        $this->controllerInstance->get(AvailableDatesApi::API_ROUTE, function () use ($apiManager) {
            $apiManager->call(new AvailableDatesApi);
        });

        // Slot available times API
        $this->controllerInstance->post(AvailableTimesApi::API_ROUTE, function () use ($apiManager) {
            $apiManager->call(new AvailableTimesApi);
        });

        // Appointment creation API
        $this->controllerInstance->post(CreateApi::API_ROUTE, function () use ($apiManager) {
            $apiManager->call(new CreateApi);
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