<?php

namespace Juancrrn\Lyra\Common;

use RuntimeException;

class TemplateUtils
{

    /**
     * Extensión de los ficheros de plantilla HTML.
     */
    private const TEMPLATE_FILE_EXTENSION = '.html';

    /**
     * Genera un contenido visual final a partir de un fichero de plantilla y
     * algunos valores.
     * 
     * @param string $fileName  Nombre del fichero, dentro del directorio de
     *                          recursos.
     * @param array $filling    Array clave-valor con los nombres de los
     *                          placeholders y sus valores.
     * 
     *                          Se recomiendan nombres de placeholders de tipo
     *                          #nombre-compuesto#.
     * 
     *                          Solo pueden darse valores de tipo cadena de
     *                          texto.
     */
    public static function generateTemplateRender(
		string $fileName,
		array $filling,
        string $resourcesPath
	): string
	{
        $fullPath = $resourcesPath . DIRECTORY_SEPARATOR . $fileName . self::TEMPLATE_FILE_EXTENSION;
		
		if (! file_exists($fullPath)) {
            throw new RuntimeException('Template file not found: ' . $fullPath . '.');
        }
        
        $file = file_get_contents($fullPath);
        
        if (empty($file)) {
            throw new RuntimeException('Empty template file: ' . realpath($fullPath) . '.');
		} else {
            // Preparar los nombres de los placeholders.

            $names = array_keys($filling);

            for ($i = 0; $i < count($names); $i++) {
                $names[$i] = '#' . $names[$i] . '#';
            }

			return str_replace($names, array_values($filling), $file);
		}
	}
}