<?php

namespace Juancrrn\Lyra\Common\Api;

use JsonSerializable;

class ApiManager
{

    public function call(ApiModel $api): void
    {
        $requestDataInput = file_get_contents('php://input');
        $requestContent = json_decode($requestDataInput);
        
        $api->consume($requestContent);
    }
    
    /**
     * Genera una respuesta HTTP con datos JSON y un código estado HTTP, y para 
     * la ejecución del script.
     * 
     * @param int    $httpStatusCode HTTP status code
     * @param object $data           Data to send in the response
     * @param array  $messages       Messages to send in the response
     */
    public static function apiRespond(
        int $httpStatusCode,
        mixed $data,
        ?array $messages = []
    ): void
    {
        http_response_code($httpStatusCode);

        $responseData = [
            'data' => $data,
            'messages' => $messages
        ];

        header('Content-Type: application/json; charset=utf-8');
        
        echo json_encode($responseData);
        
        die();
    }
}