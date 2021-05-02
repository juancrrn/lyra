<?php

namespace Juancrrn\Lyra\Common;

/**
 * Funcionalidad de validación de datos
 * 
 * @package lyra
 * 
 * @author juancrrn
 * 
 * @version 0.0.1
 */

class ValidationUtils
{

    /*
     *
     * Codificación
     * 
     */

    
	
	/**
	 * Comprueba si una cadena está codificada como UTF-8.
	 * 
	 * @see https://www.php.net/manual/en/function.mb-check-encoding.php
	 * @see https://tools.ietf.org/html/rfc3629
	 * 
	 * @param string $string
	 * 
	 * @return string
	 */
	public static function checkUtf8(string $string): bool
	{
		$len = strlen($string);
		for ($i = 0; $i < $len; $i++) {
			$c = ord($string[$i]);
			if ($c > 128) {
				if (($c > 247)) return false;
				elseif ($c > 239) $bytes = 4;
				elseif ($c > 223) $bytes = 3;
				elseif ($c > 191) $bytes = 2;
				else return false;
				if (($i + $bytes) > $len) return false;
				while ($bytes > 1) {
					$i++;
					$b = ord($string[$i]);
					if ($b < 128 || $b > 191) return false;
					$bytes--;
				}
			}
		}

		return true;
	}

	/**
	 * Verifica que una cadena está codificada en UTF-8 y, en caso contrario,
     * la convierte.
	 * 
	 * @param null|string $string
	 * 
	 * @return null|string
	 */
	public static function ensureUtf8(null|string $string): null|string
	{
		if (is_null($string)) {
			return null;
		} elseif (self::checkUtf8($string)) {
			return $string;
		} else {
			return mb_convert_encoding($string, mb_detect_encoding(($string)));
		}
	}
}