<?php

namespace Juancrrn\Lyra\Domain\TimePlanner\Appointment;

use DateTime;
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
        $studentBirthDate       = $appointment->getStudentBirthDate();
        $studentEmailAddress    = $appointment->getStudentEmailAddress();
        $studentPhoneNumber     = $appointment->getStudentPhoneNumber();
        $legalRepGovId          = $appointment->getLegalRepGovId();
        $legalRepFirstName      = $appointment->getLegalRepFirstName();
        $legalRepLastName       = $appointment->getLegalRepLastName();
        $legalRepBirthDate      = $appointment->getLegalRepBirthDate();
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

        foreach($slotRepo->findByDate($testSlotDate) as $slot) {
            $currentSlotId = $slot->getId();

            $stmt->bind_param('is', $currentSlotId, $testStudentGovId);
            $stmt->execute();

            $stmt->store_result();

            if ($stmt->num_rows > 0)
                return true;
        }

        return false;
    }

    public function retrieveById(int $id): mixed
    {

    }

    public function retrieveAll(): array
    {
        /*
         * ATENCIÓN: LIMITADO PARA QUE NO APAREZCAN LOS DE JUNIO. ///////////// TODO Limitar a partir de la fecha actual
         */

        $query = <<< SQL
        SELECT
            *
        FROM
            time_planner_appointments
        WHERE
            slot_id > 150
        ORDER BY
            slot_id
        SQL;
        
        $stmt = $this->db->prepare($query);

        $stmt->execute();
        $result = $stmt->get_result();
        $appointments = array();

        while ($appointment = $result->fetch_object()) {
            $appointments[] = Appointment::fromMysqlFetch($appointment);
        }

        $stmt->close();

        return $appointments;
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
        return false;
    }

    public function deleteById(int $id): void
    {

    }
}