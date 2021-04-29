<?php

namespace Juancrrn\Lyra\Domain;

/**
 * Clase para agrupar utilidades delacionadas con el dominio de la aplicación
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class DomainUtils
{
    
    /**
     * Niveles educativos admitidos
     */
    public const EDU_LEVEL_ESO_1    = 'edu_level_eso_1';
    public const EDU_LEVEL_ESO_2    = 'edu_level_eso_2';
    public const EDU_LEVEL_ESO_3    = 'edu_level_eso_3';
    public const EDU_LEVEL_ESO_4    = 'edu_level_eso_4';
    public const EDU_LEVEL_BACH_1   = 'edu_level_bach_1';
    public const EDU_LEVEL_BACH_2   = 'edu_level_bach_2';
    public const EDU_LEVEL_OTHER    = 'edu_level_other';

    public const EDU_LEVELS = array(
        self::EDU_LEVEL_ESO_1,
        self::EDU_LEVEL_ESO_2,
        self::EDU_LEVEL_ESO_3,
        self::EDU_LEVEL_ESO_4,
        self::EDU_LEVEL_BACH_1,
        self::EDU_LEVEL_BACH_2,
        self::EDU_LEVEL_OTHER
    );
}

?>