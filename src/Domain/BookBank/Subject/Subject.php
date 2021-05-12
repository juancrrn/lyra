<?php

namespace Juancrrn\Lyra\Domain\BookBank\Subject;

use DateTime;
use Juancrrn\Lyra\Common\CommonUtils;

/**
 * Clase para representar una asignatura
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class Subject
{

    /**
     * @var null|int $id
     */
    private $id;

    /**
     * @var string $name
     */
    private $name;

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
     * @var null|string $bookName
     */
    private $bookName;

    /**
     * @var null|string $bookIsbn
     */
    private $bookIsbn;

    /**
     * @var null|string $bookImageUrl
     */
    private $bookImageUrl;

    /**
     * @var DateTime $creationDate
     */
    private $creationDate;

    /**
     * @var int $creatorId
     */
    private $creatorId;

    public function __construct(
        ?int        $id,
        string      $name,
        string      $educationLevel,
        int         $schoolYear,
        ?string     $bookName,
        ?string     $bookIsbn,
        ?string     $bookImageUrl,
        DateTime    $creationDate,
        int         $creatorId
    )
    {
        $this->id               = $id;
        $this->name             = $name;
        $this->educationLevel   = $educationLevel;
        $this->schoolYear       = $schoolYear;
        $this->bookName         = $bookName;
        $this->bookIsbn         = $bookIsbn;
        $this->bookImageUrl     = $bookImageUrl;
        $this->creationDate     = $creationDate;
        $this->creatorId        = $creatorId;
    }

    public static function constructFromMysqliObject(object $mysqli_object): self
    {
        $bookName = isset($mysqli_object->book_name) ?
            $mysqli_object->book_name : null;
        $bookIsbn = isset($mysqli_object->book_isbn) ?
            $mysqli_object->book_isbn : null;
        $bookImageUrl = isset($mysqli_object->book_image_url) ?
            $mysqli_object->book_image_url : null;
            
        $creationDate = DateTime::createFromFormat(
            CommonUtils::MYSQL_DATETIME_FORMAT,
            $mysqli_object->creation_date
        );

        return new self(
            $mysqli_object->id,
            $mysqli_object->name,
            $mysqli_object->education_level,
            $mysqli_object->school_year,
            $bookName,
            $bookIsbn,
            $bookImageUrl,
            $creationDate,
            $mysqli_object->creator_id
        );
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

    public function getName(): string
    {
        return $this->name;
    }

    public function getEducationLevel(): string
    {
        return $this->educationLevel;
    }

    public function getSchoolYear(): int
    {
        return $this->schoolYear;
    }

    public function getBookName(): null|string
    {
        return $this->bookName;
    }

    public function getBookIsbn(): null|string
    {
        return $this->bookIsbn;
    }

    public function getBookImageUrl(): null|string
    {
        return $this->bookImageUrl;
    }

    public function getCreationDate(): DateTime
    {
        return $this->creationDate;
    }

    public function getCreatorId(): int
    {
        return $this->creatorId;
    }
}