<?php

require_once __DIR__ . '/../config/init.php';

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\Controller\AnyoneRouteGroup;
use Juancrrn\Lyra\Common\Controller\AppManagerRouteGroup;
use Juancrrn\Lyra\Common\Controller\BookBankManagerRouteGroup;
use Juancrrn\Lyra\Common\Controller\BookBankVolunteerRouteGroup;
use Juancrrn\Lyra\Common\Controller\LegalRepRouteGroup;
use Juancrrn\Lyra\Common\Controller\LoggedInRouteGroup;
use Juancrrn\Lyra\Common\Controller\StudentRouteGroup;

$controllerInstance = App::getSingleton()->getControllerInstance();

/**
 * Pruebas y demostración
 */
$controllerInstance->get('/demo/sandbox/', function () {
    require_once __DIR__ . '/../demo/SandBox.php';
});

/**
 * Vistas de usuarios de cualquier tipo (todos)
 */
(new AnyoneRouteGroup($controllerInstance))->runAll();

/**
 * Vistas de usuarios con permisos de gestor de la aplicación (AppManager)
 */
(new AppManagerRouteGroup($controllerInstance))->runAll();

/**
 * Vistas de usuarios con permisos de gestor del banco de libros
 * (BookBankManager)
 */
(new BookBankManagerRouteGroup($controllerInstance))->runAll();

/**
 * Vistas de usuarios con permisos de voluntario del banco de libros
 * (BookBankVolunteer)
 */
(new BookBankVolunteerRouteGroup($controllerInstance))->runAll();

/**
 * Vistas de usuarios con permisos de representante legal de estudiantes
 * (LegalRep)
 */
(new LegalRepRouteGroup($controllerInstance))->runAll();

/**
 * Vistas de usuarios que hayan iniciado sesión
 */
(new LoggedInRouteGroup($controllerInstance))->runAll();

/**
 * Vistas de usuarios con permisos de estudiante (Student) (puede ser receptor
 * de paquetes del banco de libros)
 */
(new StudentRouteGroup($controllerInstance))->runAll();

?>