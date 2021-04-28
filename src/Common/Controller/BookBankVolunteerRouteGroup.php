<?php

namespace Juancrrn\Lyra\Common\Controller;

use Exception;
use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\Controller\Controller;
use Juancrrn\Lyra\Common\Controller\RouteGroupModel;

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
         * Asistente de recepción
         * 
         */

        // Búsqueda de usuario
        $this->controllerInstance->get('/book-bank/check-in/search/', function () use ($viewManager) {
            throw new Exception('Route declared but not implemented.');
        });

        // Resumen de un usuario en el banco de libros y oferta de gestiones
            // Donación: añadir una donación de libros
            // Devolución: devolver un paquete de libros
            // Solicitud: solicitar un paquete de libros
            // Recogida: recoger un paquete de libros
        $this->controllerInstance->get('/book-bank/check-in/([0-9]+)/overview/', function () use ($viewManager) {
            throw new Exception('Route declared but not implemented.');
        });

        // Finalización de gestiones (POST del formulario)
        $this->controllerInstance->post('/book-bank/check-in/([0-9]+)/process/', function () use ($viewManager) {
            throw new Exception('Route declared but not implemented.');
        });

        // Finalización de gestiones (vista final de salida, redireccionada tras el POST)
        $this->controllerInstance->post('/book-bank/check-in/([0-9]+)/done/', function () use ($viewManager) {
            throw new Exception('Route declared but not implemented.');
        });
        
        /*
         *
         * Asistente de empaquetado
         * 
         */

        // Inicio del asistente
        $this->controllerInstance->post('/book-bank/lot-fill/welcome/', function () use ($viewManager) {
            throw new Exception('Route declared but not implemented.');
        });

        // Rellenado de un paquete de libros
        $this->controllerInstance->post('/book-bank/lot-fill/process/', function () use ($viewManager) {
            throw new Exception('Route declared but not implemented.');
        });
    }
}

?>