<?php

namespace Juancrrn\Lyra\Common\Controller;

use Juancrrn\Lyra\Common\Http;

/**
 * Controla las peticiones HTTP (modelo front controller)
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class Controller
{

    private $pathBase = '';

    public function __construct(string $pathBase)
    {
        $this->pathBase = $pathBase;
    }

    /**
     * Procesa una petición sin importar el método (GET, POST, etc.).
     * 
     * @param string $route
     * @param callable $handler
     */
    public function any(string $route, callable $handler): void
    {
        $this->processRequest($route, $handler);
    }

    /**
     * Procesa una petición GET.
     * 
     * @param string $route
     * @param callable $handler
     */
    public function get(string $route, callable $handler): void
    {
        if (Http::isRequestMethod(Http::METHOD_GET))
            $this->processRequest($route, $handler);
    }

    /**
     * Procesa una petición POST.
     * 
     * @param string $route
     * @param callable $handler
     */
    public function post(string $route, callable $handler): void
    {
        if (Http::isRequestMethod(Http::METHOD_POST))
            $this->processRequest($route, $handler);
    }

    /**
     * Procesa una petición PUT.
     * 
     * @param string $route
     * @param callable $handler
     */
    public function put(string $route, callable $handler): void
    {
        if (Http::isRequestMethod(Http::METHOD_PUT))
            $this->processRequest($route, $handler);
    }

    /**
     * Procesa una petición DELETE.
     * 
     * @param string $route
     * @param callable $handler
     */
    public function delete(string $route, callable $handler): void
    {
        if (Http::isRequestMethod(Http::METHOD_DELETE))
            $this->processRequest($route, $handler);
    }

    /**
     * Procesa una petición PATCH.
     * 
     * @param string $route
     * @param callable $handler
     */
    public function patch(string $route, callable $handler): void
    {
        if (Http::isRequestMethod(Http::METHOD_PATCH))
            $this->processRequest($route, $handler);
    }

    /**
     * Procesa una petición en general.
     * 
     * @param string $route
     * @param callable $handler
     */
    public function processRequest(string $route, callable $handler): void
    {
        $matches = array();

        if (Http::matchesRequestUri($this->pathBase, $route, $matches)) {
            echo call_user_func_array($handler, $matches);
            
            die();
        }
    }

    /**
     * Se ejecuta cuando no se ha ejecutado ningún controlador anteriormente.
     */
    public function default(callable $handler): void
    {
        http_response_code(404);
        
        echo call_user_func($handler);
    }
}

?>