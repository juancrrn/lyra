<?php

namespace Juancrrn\Lyra\Domain;

use InvalidArgumentException;

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
    private const EDU_LEVEL_ESO_1_TITLE = 'ESO 1';
    private const EDU_LEVEL_ESO_1_DESC = '1er curso de ESO';

    public const EDU_LEVEL_ESO_2    = 'edu_level_eso_2';
    private const EDU_LEVEL_ESO_2_TITLE = 'ESO 2';
    private const EDU_LEVEL_ESO_2_DESC = '2o curso de ESO';

    public const EDU_LEVEL_ESO_3    = 'edu_level_eso_3';
    private const EDU_LEVEL_ESO_3_TITLE = 'ESO 3';
    private const EDU_LEVEL_ESO_3_DESC = '3er curso de ESO';

    public const EDU_LEVEL_ESO_4    = 'edu_level_eso_4';
    private const EDU_LEVEL_ESO_4_TITLE = 'ESO 4';
    private const EDU_LEVEL_ESO_4_DESC = '4o curso de ESO';

    public const EDU_LEVEL_BACH_1   = 'edu_level_bach_1';
    private const EDU_LEVEL_BACH_1_TITLE = 'Bach 1';
    private const EDU_LEVEL_BACH_1_DESC = '1er curso de Bachillerato';

    public const EDU_LEVEL_BACH_2   = 'edu_level_bach_2';
    private const EDU_LEVEL_BACH_2_TITLE = 'Bach 2';
    private const EDU_LEVEL_BACH_2_DESC = '2o curso de Bachillerato';

    public const EDU_LEVEL_OTHER    = 'edu_level_other';
    private const EDU_LEVEL_OTHER_TITLE = 'Otro';
    private const EDU_LEVEL_OTHER_DESC = 'Otro curso';

    public const EDU_LEVELS = array(
        self::EDU_LEVEL_ESO_1,
        self::EDU_LEVEL_ESO_2,
        self::EDU_LEVEL_ESO_3,
        self::EDU_LEVEL_ESO_4,
        self::EDU_LEVEL_BACH_1,
        self::EDU_LEVEL_BACH_2,
        self::EDU_LEVEL_OTHER
    );

    /*
     *
     * Estados
     * 
     */

    /**
     * Transforma el estado a una representación humanamente legible.
     * 
     * @param string $status
     * 
     * @return GenericHumanModel
     */
    public static function educationLevelToHuman(string $educationLevel): GenericHumanModel
    {
        if (! in_array($educationLevel, self::EDU_LEVELS))
            throw new InvalidArgumentException('Invalid education level.');

        switch ($educationLevel) {
            case self::EDU_LEVEL_ESO_1:
                return new GenericHumanModel(
                    self::EDU_LEVEL_ESO_1, self::EDU_LEVEL_ESO_1_TITLE, self::EDU_LEVEL_ESO_1_DESC
                );
            case self::EDU_LEVEL_ESO_2:
                return new GenericHumanModel(
                    self::EDU_LEVEL_ESO_2, self::EDU_LEVEL_ESO_2_TITLE, self::EDU_LEVEL_ESO_2_DESC
                );
            case self::EDU_LEVEL_ESO_3:
                return new GenericHumanModel(
                    self::EDU_LEVEL_ESO_3, self::EDU_LEVEL_ESO_3_TITLE, self::EDU_LEVEL_ESO_3_DESC
                );
            case self::EDU_LEVEL_ESO_4:
                return new GenericHumanModel(
                    self::EDU_LEVEL_ESO_4, self::EDU_LEVEL_ESO_4_TITLE, self::EDU_LEVEL_ESO_4_DESC
                );
            case self::EDU_LEVEL_BACH_1:
                return new GenericHumanModel(
                    self::EDU_LEVEL_BACH_1, self::EDU_LEVEL_BACH_1_TITLE, self::EDU_LEVEL_BACH_1_DESC
                );
            case self::EDU_LEVEL_BACH_2:
                return new GenericHumanModel(
                    self::EDU_LEVEL_BACH_2, self::EDU_LEVEL_BACH_2_TITLE, self::EDU_LEVEL_BACH_2_DESC
                );
            case self::EDU_LEVEL_OTHER:
                return new GenericHumanModel(
                    self::EDU_LEVEL_OTHER, self::EDU_LEVEL_OTHER_TITLE, self::EDU_LEVEL_OTHER_DESC
                );
        }
    }

    public static function getEducationLevelsForSelectOptions(): array
    {
        $array = [];

        foreach (self::EDU_LEVELS as $levelValue) {
            $array[$levelValue] = self::educationLevelToHuman($levelValue)->getTitle();
        }

        return $array;
    }

    public static function validEducationLevel(string $testEducationLevel): bool
    {
        return in_array($testEducationLevel, self::EDU_LEVELS);
    }

    /*
     *
     * Nivel educativo
     * 
     */

    public static function schoolYearToHuman(int $schoolYear): string
    {
        return substr($schoolYear, 0, 4) . ' - ' . substr($schoolYear, 4, 4);
    }
}