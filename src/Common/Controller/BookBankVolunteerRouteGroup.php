<?php

namespace Juancrrn\Lyra\Common\Controller;

use Exception;
use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\Controller\Controller;
use Juancrrn\Lyra\Common\Controller\RouteGroupModel;
use Juancrrn\Lyra\Common\View\BookBank\Volunteer\CheckInAssistantStudentOverviewView;
use Juancrrn\Lyra\Common\View\BookBank\Volunteer\CheckInAssistantStudentSearchView;
use Juancrrn\Lyra\Common\View\TimePlanner\Volunteer\AppointmentListView;

/**
 * Vistas de usuarios con permisos de voluntario del banco de libros
 * (BookBankVolunteer)
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class BookBankVolunteerRouteGroup implements RouteGroupModel
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
         * Asistente de creación de usuarios (estudiante y representante legal)
         * 
         */
        
        /*
         *
         * Time planner (reserved appointments)
         * 
         */

        $this->controllerInstance->get(AppointmentListView::VIEW_ROUTE, function () use ($viewManager) {
            $viewManager->render(new AppointmentListView);
        });
        
        /*
         *
         * Asistente de recepción
         * 
         */
        
        // Student search
        $this->controllerInstance->get(CheckInAssistantStudentSearchView::VIEW_ROUTE, function () use ($viewManager) {
            $viewManager->render(new CheckInAssistantStudentSearchView);
        });
        
        // Student search (form POST)
        $this->controllerInstance->post(CheckInAssistantStudentSearchView::VIEW_ROUTE, function () use ($viewManager) {
            $viewManager->render(new CheckInAssistantStudentSearchView);
        });

        // Resumen de un usuario en el banco de libros y oferta de gestiones
            // Donación: añadir una donación de libros
            // Devolución: devolver un paquete de libros
            // Solicitud: solicitar un paquete de libros
            // Recogida: recoger un paquete de libros
        
        // Student overview
        $this->controllerInstance->get(CheckInAssistantStudentOverviewView::VIEW_ROUTE, function (int $itemId) use ($viewManager) {
            $viewManager->render(new CheckInAssistantStudentOverviewView($itemId));
        });

        // Finalización de gestiones (POST del formulario)
        $this->controllerInstance->post('/bookbank/check-in/([0-9]+)/process/', function () use ($viewManager) {
            throw new Exception('Route declared but not implemented.');
        });

        // Finalización de gestiones (vista final de salida, redireccionada tras el POST)
        $this->controllerInstance->post('/bookbank/check-in/([0-9]+)/done/', function () use ($viewManager) {
            throw new Exception('Route declared but not implemented.');
        });
        
        /*
         *
         * Asistente de empaquetado
         * 
         */

        // Inicio del asistente
        $this->controllerInstance->post('/bookbank/lot-fill/welcome/', function () use ($viewManager) {
            throw new Exception('Route declared but not implemented.');
        });

        // Rellenado de un paquete de libros
        $this->controllerInstance->post('/bookbank/lot-fill/process/', function () use ($viewManager) {
            throw new Exception('Route declared but not implemented.');
        });
    }
}