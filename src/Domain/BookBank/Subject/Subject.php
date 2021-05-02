<?php

namespace Juancrrn\Lyra\Domain\BookBank\Request;

use DateTime;

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
     * @var int $id
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
     * @var string $bookName
     */
    private $bookName;

    /**
     * @var string $bookIsbn
     */
    private $bookIsbn;

    /**
     * @var string $bookImageUrl
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
        int         $id,
        string      $name,
        string      $educationLevel,
        int         $schoolYear,
        string      $bookName,
        string      $bookIsbn,
        string      $bookImageUrl,
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

    public function getBookName(): string
    {
        return $this->bookName;
    }

    public function getBookIsbn(): string
    {
        return $this->bookIsbn;
    }

    public function getBookImageUrl(): string
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