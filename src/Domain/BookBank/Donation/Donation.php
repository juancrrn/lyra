<?php

namespace Juancrrn\Lyra\Domain\BookBank\Donation;

use DateTime;
use Juancrrn\Lyra\Common\CommonUtils;

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

    /**
     * @var null|array $contents
     */
    private $contents;

    public function __construct(
        ?int        $id,
        int         $studentId,
        DateTime    $creationDate,
        int         $creatorId,
        string      $educationLevel,
        int         $schoolYear,
        ?array      $contents = null
    )
    {
        $this->id               = $id;
        $this->studentId        = $studentId;
        $this->creationDate     = $creationDate;
        $this->creatorId        = $creatorId;
        $this->educationLevel   = $educationLevel;
        $this->schoolYear       = $schoolYear;
        $this->contents         = $contents;
    }

    public static function constructFromMysqliObject(object $mysqli_object): self
    {
        $creationDate = DateTime::createFromFormat(
            CommonUtils::MYSQL_DATETIME_FORMAT,
            $mysqli_object->creation_date
        );

        return new self(
            $mysqli_object->id,
            $mysqli_object->student_id,
            $creationDate,
            $mysqli_object->creator_id,
            $mysqli_object->education_level,
            $mysqli_object->school_year
        );
    }

    /*
     * 
     * Setters
     * 
     */

    public function setContents(array $contents): void
    {
        $this->contents = $contents;
    }

    /*
     * 
     * Getters
     * 
     */

    public function getId(): null|int
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

    public function getContents(): null|array
    {
        return $this->contents;
    }
}