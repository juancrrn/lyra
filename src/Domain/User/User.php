<?php

namespace Juancrrn\Lyra\Domain\User;

abstract class User
{

    /**
     * Constantes de tipos de usuario.
     * 
     * Realmente, deberían ser un enumerado, pero PHP no dispone, aún, de ese
     * tipo de datos.
     */
    public const TYPE_LAB_STAFF         = 'user_type_lab_staff';
    public const TYPE_MANAGEMENT_STAFF  = 'user_type_management_staff';
    public const TYPE_MEDICAL_STAFF     = 'user_type_medical_staff';
    public const TYPE_NURSING_STAFF     = 'user_type_nursing_staff';
    public const TYPE_PATIENT           = 'user_type_patient';

    public const TYPES = array(
        self::TYPE_LAB_STAFF,
        self::TYPE_MANAGEMENT_STAFF,
        self::TYPE_MEDICAL_STAFF,
        self::TYPE_NURSING_STAFF,
        self::TYPE_PATIENT
    );

    /**
     * Identificador interno.
     * 
     * @var int $id
     */
    private $id;

    /**
     * Número de identificación fiscal (NIF) o número de identificación de
     * extranjero (NIE).
     * 
     * @var string $govId
     */
    private $govId;

    /**
     * Tipo de usuario.
     * 
     * De tipo self::TYPES.
     * 
     * @var string $type
     */
    private $type;

    /**
     * Nombre.
     * 
     * @var string $firstName
     */
    private $firstName;

    /**
     * Apellidos.
     * 
     * @var string $lastName
     */
    private $lastName;

    /**
     * Número de teléfono.
     * 
     * @var string $phoneNumber
     */
    private $phoneNumber;

    /**
     * Dirección de correo electrónico.
     * 
     * @var string $emailAddress
     */
    private $emailAddress;

    /**
     * Contraseña hasheada.
     * 
     * Por ser un dato sensible, no se almacenará excepto cuando sea necesario.
     * 
     * @var string $hashedPassword
     */
    // private $hashedPassword;

    /**
     * Fecha de nacimiento.
     * 
     * En formato Juancrrn\Lyra\Common\Tools::MYSQL_DATE_FORMAT.
     * 
     * @var \DateTime $birthDate
     */
    private $birthDate;

    /**
     * Fecha y hora de registro.
     * 
     * En formato Juancrrn\Lyra\Common\Tools::MYSQL_DATETIME_FORMAT.
     * 
     * @var \DateTime $registrationDate
     */
    private $registrationDate;

    /**
     * Fecha y hora de registro.
     * 
     * En formato Juancrrn\Lyra\Common\Tools::MYSQL_DATETIME_FORMAT.
     * 
     * @var \DateTime $lastLoginDate
     */
    private $lastLoginDate;

    /*
        id
        gov_id
        type
        first_name
        last_name
        phone_number
        email_address
        hashed_password
        birth_date
        registration_date
        last_login_date
     */

    public function __construct(
        int         $id,
        string      $govId,
        string      $type,
        string      $firstName,
        string      $lastName,
        string      $phoneNumber,
        string      $emailAddress,
        \DateTime   $birthDate,
        \DateTime   $registrationDate,
        ?\DateTime  $lastLoginDate,
    )
    {
        $this->id               = $id;
        $this->govId            = $govId;
        $this->type             = $type;
        $this->firstName        = $firstName;
        $this->lastName         = $lastName;
        $this->phoneNumber      = $phoneNumber;
        $this->emailAddress     = $emailAddress;
        $this->birthDate        = $birthDate;
        $this->registrationDate = $registrationDate;
        $this->lastLoginDate    = $lastLoginDate;
    }

    /**
     * Comprueba si el usuario es de un tipo.
     * 
     * @param string $testType  Tipo de usuario a comprobar, utilizando las
     *                          constantes de tipos definidas arriba.
     * 
     * @return bool
     */
    abstract function isType(string $testType): bool;

    public function getId(): int
    {
        return $this->id;
    }

    public function getGovId(): string
    {
        return $this->govId;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function getBirthDate(): \DateTime
    {
        return $this->birthDate;
    }

    public function getRegistrationDate(): \DateTime
    {
        return $this->registrationDate;
    }

    public function getLastLoginDate(): \DateTime
    {
        return $this->lastLoginDate;
    }

}

?>