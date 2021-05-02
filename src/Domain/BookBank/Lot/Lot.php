<?php

namespace Juancrrn\Lyra\Domain\BookBank\Lot;

use DateTime;

/**
 * Clase para representar un paquete de libros
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class Lot
{

    /*
     * Posibles estados del paquete
     */
    public const STATUS_INITIAL     = 'book_lot_status_initial';
    public const STATUS_READY       = 'book_lot_status_ready';
    public const STATUS_PICKED_UP   = 'book_lot_status_picked_up';
    public const STATUS_RETURNED    = 'book_lot_status_returned';
    public const STATUS_REJECTED    = 'book_lot_status_rejected';

    public const STATUSES = array(
        self::STATUS_INITIAL,
        self::STATUS_READY,
        self::STATUS_PICKED_UP,
        self::STATUS_RETURNED,
        self::STATUS_REJECTED
    );

    /**
     * @var int $id
     */
    private $id;

    /**
     * @var int $requestId
     */
    private $requestId;

    /**
     * @var int $studentId
     */
    private $studentId;

    /**
     * De self::STATUSES.
     * 
     * @var string $status
     */
    private $status;

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
     * @var null|DateTime $pickupDate
     */
    private $pickupDate;

    /**
     * @var null|DateTime $returnDate
     */
    private $returnDate;

    /**
     * @var bool $locked
     */
    private $locked;

    /**
     * Contenido del paquete, si se ha solicitado su carga.
     * 
     * En caso afirmativo, debe ser un array de Subject.
     * 
     * @var null|array $contents
     */
    private $contents;

    public function __construct(
        int         $id,
        int         $requestId,
        int         $studentId,
        string      $status,
        DateTime    $creationDate,
        int         $creatorId,
        string      $educationLevel,
        int         $schoolYear,
        ?DateTime   $pickupDate,
        ?DateTime   $returnDate,
        bool        $locked,
        ?array      $contents
    )
    {
        $this->id              = $id;
        $this->requestId       = $requestId;
        $this->studentId       = $studentId;
        $this->status          = $status;
        $this->creationDate    = $creationDate;
        $this->creatorId       = $creatorId;
        $this->educationLevel  = $educationLevel;
        $this->schoolYear      = $schoolYear;
        $this->pickupDate      = $pickupDate;
        $this->returnDate      = $returnDate;
        $this->locked          = $locked;
        $this->contents        = $contents;
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

    public function getRequestId(): int
    {
        return $this->requestId;
    }

    public function getStudentId(): int
    {
        return $this->studentId;
    }

    public function getStatus(): string
    {
        return $this->status;
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

    public function getPickupDate(): null|DateTime
    {
        return $this->pickupDate;
    }

    public function getReturnDate(): null|DateTime
    {
        return $this->returnDate;
    }

    public function isLocked(): bool
    {
        return $this->locked;
    }

    public function getContents(): null|array
    {
        return $this->contents;
    }
}