<?php

namespace Juancrrn\Lyra\Domain\TimePlanner\Appointment;

use DateTime;
use Juancrrn\Lyra\Common\CommonUtils;
use Juancrrn\Lyra\Domain\Repository;
use Juancrrn\Lyra\Domain\TimePlanner\Slot\SlotRepository;
use mysqli;

/**
 * Time planner appointment repository
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class AppointmentRepository implements Repository
{

    protected $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    public function insert(Appointment $appointment): int
    {
        $query = <<< SQL
        INSERT INTO
            time_planner_appointments
            (
                slot_id,
                student_id,
                student_gov_id,
                student_first_name,
                student_last_name,
                student_birth_date,
                student_email_address,
                student_phone_number,
                legal_rep_gov_id,
                legal_rep_first_name,
                legal_rep_last_name,
                legal_rep_birth_date,
                legal_rep_email_address,
                legal_rep_phone_number,
                request_specification
            )
        VALUES
            (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )
        SQL;
        
        $stmt = $this->db->prepare($query);

        $slotId                 = $appointment->getSlotId();
        $studentId              = $appointment->getStudentId();
        $studentGovId           = $appointment->getStudentGovId();
        $studentFirstName       = $appointment->getStudentFirstName();
        $studentLastName        = $appointment->getStudentLastName();
        $studentBirthDate       =
            $appointment->getStudentBirthDate() != null ?
            $appointment->getStudentBirthDate()->format(CommonUtils::MYSQL_DATE_FORMAT) :
            null;
        $studentEmailAddress    = $appointment->getStudentEmailAddress();
        $studentPhoneNumber     = $appointment->getStudentPhoneNumber();
        $legalRepGovId          = $appointment->getLegalRepGovId();
        $legalRepFirstName      = $appointment->getLegalRepFirstName();
        $legalRepLastName       = $appointment->getLegalRepLastName();
        $legalRepBirthDate      =
            $appointment->getLegalRepBirthDate() != null ?
            $appointment->getLegalRepBirthDate()->format(CommonUtils::MYSQL_DATE_FORMAT) :
            null;
        $legalRepEmailAddress   = $appointment->getLegalRepEmailAddress();
        $legalRepPhoneNumber    = $appointment->getLegalRepPhoneNumber();
        $requestSpecification   = $appointment->getRequestSpecification();
        
        $stmt->bind_param(
            "iisssssssssssss", 
            $slotId,
            $studentId,
            $studentGovId,
            $studentFirstName,
            $studentLastName,
            $studentBirthDate,
            $studentEmailAddress,
            $studentPhoneNumber,
            $legalRepGovId,
            $legalRepFirstName,
            $legalRepLastName,
            $legalRepBirthDate,
            $legalRepEmailAddress,
            $legalRepPhoneNumber,
            $requestSpecification
        );

        $stmt->execute();

        $insert_id = $stmt->insert_id;
        
        $stmt->close();

        return $insert_id;
    }

    public function findById(int $id): bool|int
    {
        return false;
    }

    public function findByStudentGovId(string $testGovId): array
    {
        $query = <<< SQL
        SELECT
            *
        FROM
            time_planner_appointments
        WHERE
            student_gov_id = ?
        LIMIT 1
        SQL;

        $stmt = $this->db->prepare($query);
        
        $stmt->bind_param(
            's',
            $testGovId
        );

        $stmt->execute();
        $result = $stmt->get_result();

        $appointments = array();

        while ($appointment = $result->fetch_object()) {
            $appointments[] = Appointment::fromMysqlFetch($appointment);
        }

        $stmt->close();

        return $appointments;
    }

    public function findByStudentGovIdAndSlotDate(string $testStudentGovId, DateTime $testSlotDate): bool
    {
        $slotRepo = new SlotRepository($this->db);

        $query = <<< SQL
        SELECT
            id
        FROM
            time_planner_appointments
        WHERE
            slot_id = ?
        AND
            student_gov_id = ?
        LIMIT 1
        SQL;

        $stmt = $this->db->prepare($query);

        foreach($slotRepo->findByDate($testSlotDate) as $slotId) {
            $stmt->bind_param('is', $slotId, $testStudentGovId);
            $stmt->execute();

            $stmt->store_result();

            if ($stmt->num_rows > 0)
                return true;
        }

        return false;
    }

    public function findFuture(): array
    {
        $currentDate = (new DateTime)->format(CommonUtils::MYSQL_DATE_FORMAT);

        $query = <<< SQL
        SELECT
            id
        FROM
            time_planner_appointments
        WHERE
            slot_id
        IN (
            SELECT
                id
            FROM
                time_planner_slots
            WHERE
                `date` >= ?
            ORDER BY
                `date` ASC,
                `time` ASC
        )
        SQL;
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param(
            's',
            $currentDate
        );
        $stmt->execute();
        $result = $stmt->get_result();

        $appointmentIds = [];

        while ($appointment = $result->fetch_object()) {
            $appointmentIds[] = $appointment->id;
        }

        $stmt->close();

        return $appointmentIds;
    }

    public function retrieveById(int $id): Appointment
    {
        $query = <<< SQL
        SELECT
            *
        FROM
            time_planner_appointments
        WHERE
            id = ?
        LIMIT 1
        SQL;
        
        $stmt = $this->db->prepare($query);

        $stmt->bind_param(
            'i',
            $id
        );

        $stmt->execute();
        $result = $stmt->get_result();

        $item = Appointment::fromMysqlFetch($result->fetch_object());

        $stmt->close();

        return $item;
    }

    public function retrieveAll(): array
    {
        throw new \Exception('Not implemented');
    }

    public function retrieveByIds(array $ids): array
    {
        $items = [];

        foreach ($ids as $id) {
            $items[] = $this->retrieveById($id);
        }

        return $items;
    }

    public function countBySlotId(int $slotId): int
    {
        $query = <<< SQL
        SELECT
            id
        FROM
            time_planner_appointments
        WHERE
            slot_id = ?
        SQL;
        
        $stmt = $this->db->prepare($query);
        
        $stmt->bind_param('i', $slotId);

        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        
        return $num_rows;
    }

    public function verifyConstraintsById(int $id): bool|array
    {
        throw new \Exception('Not implemented');
    }

    public function deleteById(int $id): void
    {
        throw new \Exception('Not implemented');
    }
}