<?php

namespace Juancrrn\Lyra\Domain\BookBank\Request;

use DateTime;
use InvalidArgumentException;
use Juancrrn\Lyra\Domain\GenericHumanModel;

/**
 * Clase para representar una solicitud
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class Request
{

    /*
     * Posibles estados de la solicitud
     */
    public const STATUS_PENDING         = 'book_request_status_pending';
    private const STATUS_PENDING_TITLE = 'Solicitud pendiente';
    private const STATUS_PENDING_DESC = 'La solicitud ha sido creada pero aún no se ha procesado.';

    public const STATUS_PROCESSED       = 'book_request_status_processed';
    private const STATUS_PROCESSED_TITLE = 'Solicitud procesada';
    private const STATUS_PROCESSED_DESC = 'La solicitud ha sido procesada y existe un paquete asociado a la misma.';

    public const STATUS_REJECTED_STOCK  = 'book_request_status_rejected_stock';
    private const STATUS_REJECTED_STOCK_TITLE = 'Solicitud desestimada por falta de ejemplares';
    private const STATUS_REJECTED_STOCK_DESC = 'La solicitud no ha podido procesarse por falta de ejemplares.';

    public const STATUS_REJECTED_OTHER  = 'book_request_status_rejected_other';
    private const STATUS_REJECTED_OTHER_TITLE = 'Solicitud desestimada por otros motivos';
    private const STATUS_REJECTED_OTHER_DESC = 'La solicitud no ha podido procesarse por otros motivos.';

    public const STATUSES = array(
        self::STATUS_PENDING,
        self::STATUS_PROCESSED,
        self::STATUS_REJECTED_STOCK,
        self::STATUS_REJECTED_OTHER
    );

    /**
     * @var int $id
     */
    private $id;

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
     * @var string $specification
     */
    private $specification;

    /**
     * @var string $locked
     */
    private $locked;

    public function __construct(
        int         $id,
        int         $studentId,
        string      $status,
        DateTime    $creationDate,
        int         $creatorId,
        string      $educationLevel,
        int         $schoolYear,
        string      $specification,
        bool        $locked
    )
    {
        $this->id              = $id;
        $this->studentId       = $studentId;
        $this->status          = $status;
        $this->creationDate    = $creationDate;
        $this->creatorId       = $creatorId;
        $this->educationLevel  = $educationLevel;
        $this->schoolYear      = $schoolYear;
        $this->specification   = $specification;
        $this->locked          = $locked;
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

    public function getSpecification(): string
    {
        return $this->specification;
    }

    public function isLocked(): bool
    {
        return $this->locked;
    }

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
    public static function statusToHuman(string $status): GenericHumanModel
    {
        if (! in_array($status, self::STATUSES))
            throw new InvalidArgumentException('Invalid satus.');

        switch ($status) {
            case self::STATUS_PENDING:
                return new GenericHumanModel(
                    self::STATUS_PENDING, self::STATUS_PENDING_TITLE, self::STATUS_PENDING_DESC
                );
            case self::STATUS_PROCESSED:
                return new GenericHumanModel(
                    self::STATUS_PROCESSED, self::STATUS_PROCESSED_TITLE, self::STATUS_PROCESSED_DESC
                );
            case self::STATUS_REJECTED_STOCK:
                return new GenericHumanModel(
                    self::STATUS_REJECTED_STOCK, self::STATUS_REJECTED_STOCK_TITLE, self::STATUS_REJECTED_STOCK_DESC
                );
            case self::STATUS_REJECTED_OTHER:
                return new GenericHumanModel(
                    self::STATUS_REJECTED_OTHER, self::STATUS_REJECTED_OTHER_TITLE, self::STATUS_REJECTED_OTHER_DESC
                );
        }
    }
}