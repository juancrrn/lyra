<?php

namespace Juancrrn\Lyra\Domain\BookBank\Lot;

use DateTime;
use InvalidArgumentException;
use Juancrrn\Lyra\Common\CommonUtils;
use Juancrrn\Lyra\Domain\GenericHumanModel;

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
    private const STATUS_INITIAL_TITLE = 'Paquete en estado inicial';
    private const STATUS_INITIAL_DESC = 'El paquete ha sido creado pero aún no está listo para recoger.';

    public const STATUS_READY       = 'book_lot_status_ready';
    private const STATUS_READY_TITLE = 'Paquete preparado';
    private const STATUS_READY_DESC = 'El paquete ha sido completado y está listo para su recogida.';

    public const STATUS_PICKED_UP   = 'book_lot_status_picked_up';
    private const STATUS_PICKED_UP_TITLE = 'Paquete recogido';
    private const STATUS_PICKED_UP_DESC = 'El paquete ha sido recogido y se encuentra en propiedad del estudiante.';

    public const STATUS_RETURNED    = 'book_lot_status_returned';
    private const STATUS_RETURNED_TITLE = 'Paquete devuelto';
    private const STATUS_RETURNED_DESC = 'El paquete ha sido devuelto y ha terminado su ciclo.';

    public const STATUS_REJECTED    = 'book_lot_status_rejected';
    private const STATUS_REJECTED_TITLE = 'Paquete rechazado por el estudiante';
    private const STATUS_REJECTED_DESC = 'El paquete ha sido rechazado por el estudiante.';

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
        string      $status,
        DateTime    $creationDate,
        int         $creatorId,
        ?DateTime   $pickupDate,
        ?DateTime   $returnDate,
        bool        $locked,
        ?array      $contents
    )
    {
        $this->id              = $id;
        $this->requestId       = $requestId;
        $this->status          = $status;
        $this->creationDate    = $creationDate;
        $this->creatorId       = $creatorId;
        $this->pickupDate      = $pickupDate;
        $this->returnDate      = $returnDate;
        $this->locked          = $locked;
        $this->contents        = $contents;
    }

    public static function constructFromMysqliObject(object $mysqli_object): self
    {
        $creationDate = DateTime::createFromFormat(
            CommonUtils::MYSQL_DATETIME_FORMAT,
            $mysqli_object->creation_date
        );

        $pickupDate =
            isset($mysqli_object->pickup_date) ?
            DateTime::createFromFormat(
                CommonUtils::MYSQL_DATETIME_FORMAT,
                $mysqli_object->pickup_date
            )
            : null;
            
        $returnDate =
            isset($mysqli_object->return_date) ?
            DateTime::createFromFormat(
                CommonUtils::MYSQL_DATETIME_FORMAT,
                $mysqli_object->return_date
            )
            : null;

        return new self(
            $mysqli_object->id,
            $mysqli_object->request_id,
            $mysqli_object->status,
            $creationDate,
            $mysqli_object->creator_id,
            $pickupDate,
            $returnDate,
            $mysqli_object->locked,
            null
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

    public function getId(): int
    {
        return $this->id;
    }

    public function getRequestId(): int
    {
        return $this->requestId;
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
            case self::STATUS_INITIAL:
                return new GenericHumanModel(
                    self::STATUS_INITIAL, self::STATUS_INITIAL_TITLE, self::STATUS_INITIAL_DESC
                );
            case self::STATUS_READY:
                return new GenericHumanModel(
                    self::STATUS_READY, self::STATUS_READY_TITLE, self::STATUS_READY_DESC
                );
            case self::STATUS_PICKED_UP:
                return new GenericHumanModel(
                    self::STATUS_PICKED_UP, self::STATUS_PICKED_UP_TITLE, self::STATUS_PICKED_UP_DESC
                );
            case self::STATUS_RETURNED:
                return new GenericHumanModel(
                    self::STATUS_RETURNED, self::STATUS_RETURNED_TITLE, self::STATUS_RETURNED_DESC
                );
            case self::STATUS_REJECTED:
                return new GenericHumanModel(
                    self::STATUS_REJECTED, self::STATUS_REJECTED_TITLE, self::STATUS_REJECTED_DESC
                );
        }
    }
}