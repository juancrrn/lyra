<?php

namespace Juancrrn\Lyra\Domain\TimePlanner\Appointment;

use DateTime;
use Juancrrn\Lyra\Common\CommonUtils;
use stdClass;

/**
 * Time planner appointment
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class Appointment
{
    
    /**
     * @var int       
     */
    private $id;

    /**
     * @var int
     */
    private $slotId;

    /**
     * @var null|string
     */
    private $studentId;

    /**
     * @var null|string
     */
    private $studentGovId;

    /**
     * @var null|string
     */
    private $studentFirstName;

    /**
     * @var null|string
     */
    private $studentLastName;

    /**
     * @var null|DateTime
     */
    private $studentBirthDate;

    /**
     * @var null|string
     */
    private $studentEmailAddress;

    /**
     * @var null|string
     */
    private $studentPhoneNumber;

    /**
     * @var null|string
     */
    private $legalRepGovId;

    /**
     * @var null|string
     */
    private $legalRepFirstName;

    /**
     * @var null|string
     */
    private $legalRepLastName;

    /**
     * @var null|DateTime
     */
    private $legalRepBirthDate;

    /**
     * @var null|string
     */
    private $legalRepEmailAddress;

    /**
     * @var null|string
     */
    private $legalRepPhoneNumber;

    /**
     * @var null|string
     */
    private $requestSpecification;

    public function __construct(
        ?int      $id,

        int       $slotId,

        ?string   $studentId,

        ?string   $studentGovId,
        ?string   $studentFirstName,
        ?string   $studentLastName,
        ?DateTime $studentBirthDate,
        ?string   $studentEmailAddress,
        ?string   $studentPhoneNumber,

        ?string   $legalRepGovId,
        ?string   $legalRepFirstName,
        ?string   $legalRepLastName,
        ?DateTime $legalRepBirthDate,
        ?string   $legalRepEmailAddress,
        ?string   $legalRepPhoneNumber,

        ?string   $requestSpecification
    )
    {
        $this->id                   = $id;

        $this->slotId               = $slotId;

        $this->studentId            = $studentId;

        $this->studentGovId         = $studentGovId;
        $this->studentFirstName     = $studentFirstName;
        $this->studentLastName      = $studentLastName;
        $this->studentBirthDate     = $studentBirthDate;
        $this->studentEmailAddress  = $studentEmailAddress;
        $this->studentPhoneNumber   = $studentPhoneNumber;

        $this->legalRepGovId        = $legalRepGovId;
        $this->legalRepFirstName    = $legalRepFirstName;
        $this->legalRepLastName     = $legalRepLastName;
        $this->legalRepBirthDate    = $legalRepBirthDate;
        $this->legalRepEmailAddress = $legalRepEmailAddress;
        $this->legalRepPhoneNumber  = $legalRepPhoneNumber;

        $this->requestSpecification = $requestSpecification;
    }
    
    public static function fromMysqlFetch(stdClass $object): self
    {
        $studentBirthDate =
            $object->student_birth_date == null ? null :
            DateTime::createFromFormat(
                CommonUtils::MYSQL_DATE_FORMAT,
                $object->student_birth_date
            );
        $legalRepBirthDate =
            $object->legal_rep_birth_date == null ? null :
            DateTime::createFromFormat(
                CommonUtils::MYSQL_DATE_FORMAT,
                $object->legal_rep_birth_date
            );

        return new self(
            $object->id,

            $object->slot_id,

            $object->student_id,

            $object->student_gov_id,
            $object->student_first_name,
            $object->student_last_name,
            $studentBirthDate,
            $object->student_email_address,
            $object->student_phone_number,

            $object->legal_rep_gov_id,
            $object->legal_rep_first_name,
            $object->legal_rep_last_name,
            $legalRepBirthDate,
            $object->legal_rep_email_address,
            $object->legal_rep_phone_number,

            $object->request_specification
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getSlotId(): int
    {
        return $this->slotId;
    }

    public function getStudentId(): ?string
    {
        return $this->studentId;
    }

    public function getStudentGovId(): ?string
    {
        return $this->studentGovId;
    }

    public function getStudentFirstName(): ?string
    {
        return $this->studentFirstName;
    }

    public function getStudentLastName(): ?string
    {
        return $this->studentLastName;
    }

    public function getStudentBirthDate(): ?DateTime
    {
        return $this->studentBirthDate;
    }

    public function getStudentEmailAddress(): ?string
    {
        return $this->studentEmailAddress;
    }

    public function getStudentPhoneNumber(): ?string
    {
        return $this->studentPhoneNumber;
    }

    public function getLegalRepGovId(): ?string
    {
        return $this->legalRepGovId;
    }

    public function getLegalRepFirstName(): ?string
    {
        return $this->legalRepFirstName;
    }

    public function getLegalRepLastName(): ?string
    {
        return $this->legalRepLastName;
    }

    public function getLegalRepBirthDate(): ?DateTime
    {
        return $this->legalRepBirthDate;
    }

    public function getLegalRepEmailAddress(): ?string
    {
        return $this->legalRepEmailAddress;
    }

    public function getLegalRepPhoneNumber(): ?string
    {
        return $this->legalRepPhoneNumber;
    }

    public function getRequestSpecification(): ?string
    {
        return $this->requestSpecification;
    }
}