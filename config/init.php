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
if (RCS_DEV_MODE) {
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

/**
 * Preparar autocarga de clases registrando una función anónima como
 * implementación de __autoload().
 *
 * @see https://www.php.net/manual/en/function.spl-autoload-register.php
 * @see https://www.php-fig.org/psr/psr-4/
 */
spl_autoload_register(function ($class)
{
    // Prefijo de espacio de nombres específico del proyecto.
    $prefix = 'Juancrrn\\Lyra\\';

    // Directorio base para el prefijo del espacio de nombres.
    $baseDir = __DIR__ . '/../src/';

    // ¿La clase utiliza el prefijo del espacio de nombres?
    $len = strlen($prefix);

    if (strncmp($prefix, $class, $len) !== 0) {
        // No, entonces ir al siguiente autoloader.
        return;
    }

    // Obtener el nombre relativo de la clase.
    $relative_class = substr($class, $len);

    // Reemplazar el prefijo del espacio de nombres con directorio base,
    // reemplazar los separadores del espacio de nombres con separadores de
    // directorio en el nombre relativo de la clase y añadir la extensión de
    // PHP.
    $file = str_replace('/', DIRECTORY_SEPARATOR, $baseDir) . str_replace('\\', DIRECTORY_SEPARATOR, $relative_class) . '.php';

    // Si el fichero existe, cargarlo.
    if (file_exists($file)) {
        require $file;
    }
});

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