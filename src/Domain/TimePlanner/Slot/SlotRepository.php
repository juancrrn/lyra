<?php

namespace Juancrrn\Lyra\Domain\TimePlanner\Slot;

use DateTime;
use Juancrrn\Lyra\Common\CommonUtils;
use Juancrrn\Lyra\Domain\Repository;
use Juancrrn\Lyra\Domain\TimePlanner\Appointment\AppointmentRepository;
use mysqli;

/**
 * Time planner slot repository
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class SlotRepository implements Repository
{

    protected $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    public function findById(int $id): bool
    {
        return false;
    }

    public function findByDate(DateTime $testDate): array
    {
        $query = <<< SQL
        SELECT
            *
        FROM
            time_planner_slots
        WHERE
            date = ?
        SQL;

        $stmt = $this->db->prepare($query);

        $testDateFormatted = $testDate->format(CommonUtils::MYSQL_DATE_FORMAT);
        
        $stmt->bind_param('s', $testDateFormatted);

        $stmt->execute();
        $result = $stmt->get_result();
        $slots = array();

        while ($slot = $result->fetch_object()) {
            $slots[] = Slot::fromMysqlFetch($slot);
        }

        $stmt->close();

        return $slots;
    }

    public function findByDateAndTime(DateTime $testDate, DateTime $testTime): array
    {
        $query = <<< SQL
        SELECT
            *
        FROM
            time_planner_slots
        WHERE
            date = ?
        AND
            time = ?
        SQL;

        $stmt = $this->db->prepare($query);

        $testDateFormatted = $testDate->format(CommonUtils::MYSQL_DATE_FORMAT);
        $testTimeFormatted = $testTime->format(CommonUtils::MYSQL_TIME_FORMAT);
        
        $stmt->bind_param(
            'ss',
            $testDateFormatted,
            $testTimeFormatted
        );

        $stmt->execute();
        $result = $stmt->get_result();
        $slots = array();

        while ($slot = $result->fetch_object()) {
            $slots[] = Slot::fromMysqlFetch($slot);
        }

        $stmt->close();

        return $slots;
    }

    public function retrieveById(int $id): mixed
    {
        $query = <<< SQL
        SELECT
            *
        FROM
            time_planner_slots
        WHERE
            id = ?
        LIMIT 1
        SQL;

        $stmt = $this->db->prepare($query);
        
        $stmt->bind_param('i', $id);

        $stmt->execute();
        $result = $stmt->get_result();
        
        $object = $result->fetch_object();
        $slot = Slot::fromMysqlFetch($object);

        $stmt->close();

        return $slot;
    }

    public function retrieveAll(): array
    {
        $query = <<< SQL
        SELECT
            *
        FROM
            time_planner_slots
        SQL;

        $stmt = $this->db->prepare($query);

        $stmt->execute();
        $result = $stmt->get_result();
        $slots = array();

        while ($slot = $result->fetch_object()) {
            $slots[] = Slot::fromMysqlFetch($slot);
        }

        $stmt->close();

        return $slots;
    }

    public function retrieveAvailableSlotDates(): array
    {
        $allSlots = $this->retrieveAll();
        
        $availableDates = array();
        
        foreach ($allSlots as $slot) {
            if (
                ! in_array($slot->getDate(), $availableDates) &&
                ! $this->isFull($slot->getId())
            ) {
                $availableDates[] = $slot->getDate();
            }
        }
        
        return $availableDates;
    }

    public function retrieveAvailableSlotTimesByDate(DateTime $testDate): array
    {
        $allSlotIds = $this->findByDate($testDate);
        
        $availableTimes = array();
        
        foreach ($allSlotIds as $slotId) {
            $slot = $this->retrieveById($slotId);

            if (! $this->isFull($slot->getId())) {
                $availableTimes[] = $slot->getTime();
            }
        }
        
        return $availableTimes;
    }

    public function isFull(int $id): bool
    {
        $slot = $this->retrieveById($id);

        $appointmentRepo = new AppointmentRepository($this->db);

        return $appointmentRepo->countBySlotId($slot->getId()) < $slot->getMaxAppointments();
    }

    public function validateDateAndTimeAvailability(DateTime $testDate, DateTime $testTime): bool
    {
        $slotId = $this->findByDateAndTime($testDate, $testTime);
        
        if (! empty($slotId))
            return ! $this->isFull($slotId[0]);
        
        return false;
    }

    public function verifyConstraintsById(int $id): bool|array
    {
        return false;
    }

    public function deleteById(int $id): void
    {

    }
}