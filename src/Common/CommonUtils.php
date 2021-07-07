<?php

namespace Juancrrn\Lyra\Common;

/**
 * Clase para agrupar utilidades de la aplicación en general
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class CommonUtils
{
    
    /**
     * Formato estándar de tipo de dato DATETIME de MySQL para PHP DateTime.
     */
    public const MYSQL_DATETIME_FORMAT = 'Y-m-d H:i:s';
    
    /**
     * Formato estándar de tipo de dato DATE de MySQL para PHP DateTime.
     */
    public const MYSQL_DATE_FORMAT = 'Y-m-d';
    public const REGEX_DATE_FORMAT = '#^\d{4}-\d{2}-\d{2}$#';
    
    /**
     * Formato estándar de tipo de dato TIME de MySQL para PHP DateTime.
     */
    public const MYSQL_TIME_FORMAT = 'H:i:s';
    public const REGEX_TIME_FORMAT = '#^\d{2}:\d{2}:\d{2}$#';
    
    /**
     * Formato estándar de tipo de dato DATETIME para legibilidad humana.
     */
    public const HUMAN_DATETIME_FORMAT = 'j \d\e F \d\e\l Y \a \l\a\s H:i';
    public const HUMAN_DATETIME_FORMAT_STRF = '%e de %B de %Y a las %H:%M';
    
    /**
     * Formato estándar de tipo de dato DATE para legibilidad humana.
     */
    public const HUMAN_DATE_FORMAT = 'j \d\e F \d\e\l Y';
    public const HUMAN_DATE_FORMAT_STRF = '%e de %B de %Y';
    
    /**
     * Formato estándar de tipo de dato TIME para legibilidad humana.
     */
    public const HUMAN_TIME_FORMAT = 'H:i';
    public const HUMAN_TIME_FORMAT_STRF = '%H:%M';
}