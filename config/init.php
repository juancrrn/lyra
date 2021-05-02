<?php

/**
 * Inicialización de la aplicación
 *
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

/**
 * Habilitar escritura estricta (strict typing) en PHP para mayor seguridad.
 */
declare(strict_types = 1);

/**
 * Carga del fichero de configuración.
 */
require_once __DIR__ . '/config.php';

/**
 * Habilitar errores para depuración.
 */
if (LYRA_DEV_MODE) {
    ini_set('display_errors', 'On');
    error_reporting(E_ALL);
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
}

/**
 * Configuración de codificación y zona horaria.
 */
ini_set('default_charset', 'UTF-8');
setLocale(LC_ALL, 'es_ES.UTF.8');
setlocale(LC_TIME, 'es_ES');
date_default_timezone_set('Europe/Madrid');

/**
 * Función para depuración rápida.
 */
function dd($var)
{
    var_dump($var);

    die();
}

/**
 * Función para depuración rápida con nombre de fichero y número de línea.
 */
function ddl(?string $message, mixed $var)
{
    $bt = debug_backtrace();
    $caller = array_shift($bt);

    echo 'ddl(): ' . $caller['file'] . ':' . $caller['line'] . "\n";

    if (! is_null($message)) {
        echo $message . "\n";
    }

    if (! is_null($var)) {
        var_dump($var);
    }

    die();
}

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Inicialización de la instancia de la aplicación.
 */

$app = Juancrrn\Lyra\Common\App::getSingleton();

$app->init(
    array(
        'host' => LYRA_DB_HOST,
        'user' => LYRA_DB_USER,
        'password' => LYRA_DB_PASSWORD,
        'name' => LYRA_DB_NAME
    ),

    LYRA_ROOT,
    LYRA_URL,
    LYRA_PATH_BASE,
    LYRA_NAME,

    LYRA_DEF_PASSWORD,

    LYRA_DEV_MODE
);

?>