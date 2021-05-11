<?php

namespace Juancrrn\Lyra\Domain\BookBank\Donation;

use DateTime;

/**
 * Clase para representar una donación
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class Donation
{

    /**
     * @var int $id
     */
    private $id;

    /**
     * @var int $studentId
     */
    private $studentId;

    /**
     * @var DateTime $creationDate
     */
    private $creationDate;

    /**
     * @var int $creatorId
     */
    private $creatorId;

    /**
     * De Juancrrn\Lyra\Domain\GlobalUtils::EDU_LEVELS.
     * 
     * @var string $educationLevel
     */
    private $educationLevel;

    /**
     * @var int $schoolYear
     */
    private $schoolYear;

    public function __construct(
        int         $id,
        int         $studentId,
        DateTime    $creationDate,
        int         $creatorId,
        string      $educationLevel,
        int         $schoolYear
    )
    {
        $this->id               = $id;
        $this->studentId        = $studentId;
        $this->creationDate     = $creationDate;
        $this->creatorId        = $creatorId;
        $this->educationLevel   = $educationLevel;
        $this->schoolYear       = $schoolYear;
    }

    /*
     * 
     * Getters
     * 
     */

    public function getId(): int
    {
        return $this->id;
    }

    public function getStudentId(): int
    {
        return $this->studentId;
    }

    public function getCreationDate(): DateTime
    {
        return $this->creationDate;
    }

    public function getCreatorId(): int
    {
        return $this->creatorId;
    }

    public function getEducationLevel(): string
    {
        return $this->educationLevel;
    }

    public function getSchoolYear(): int
    {
        return $this->schoolYear;
    }
}