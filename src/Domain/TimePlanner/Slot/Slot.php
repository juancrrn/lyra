<?php

namespace Juancrrn\Lyra\Domain\TimePlanner\Slot;

use DateTime;
use stdClass;

/**
 * Time planner slot
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class Slot
{
    
    /**
     * @var int
     */
    private $id;

    /**
     * @var DateTime
     */
    private $date;

    /**
     * @var DateTime
     */
    private $time;

    /**
     * @var null|int
     */
    private $duration;

    /**
     * @var null|int
     */
    private $maxAppointments;
    
    public function __construct(
        int      $id,
        DateTime $date,
        DateTime $time,
        ?int     $duration,
        ?int     $maxAppointments
    )
    {
        $this->id               = $id;
        $this->date             = $date;
        $this->time             = $time;
        $this->duration         = $duration;
        $this->maxAppointments  = $maxAppointments;
    }
    
    public static function fromMysqlFetch(stdClass $object): self
    {
        return new self(
            $object->id,
            $object->date,
            $object->time,
            $object->duration,
            $object->max_appointments
        );
    }
    
    public function getId(): int
    {
        return $this->id;
    }
    
    public function getDate(): DateTime
    {
        return $this->date;
    }
    
    public function getTime(): DateTime
    {
        return $this->time;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }
    
    public function getMaxAppointments(): ?int
    {
        return $this->maxAppointments;
    }
}