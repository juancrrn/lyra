<?php

namespace Juancrrn\Lyra\Domain\User;

use DateTime;

/**
 * Clase para representar un usuario
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class User
{

    /**
     * Posibles estados de un usuario:
     * 
     * - Inactivo: recién creado, necesita hacer clic en un mensaje en su email
     *   para activar su cuenta. Existe un token.
     * - Activo: puede acceder normalmente.
     * - Restablecimiento: ha solicitado el restablecimiento de su contraseña.
     *   Tiene que hacer clic en un mensaje en su email para restablecerla.
     *   Existe un token.
     */
    public const STATUS_INACTIVE    = 'user_status_inactive';
    public const STATUS_ACTIVE      = 'user_status_active';
    public const STATUS_RESET       = 'user_status_reset';

    public const STATUSES = array(
        self::STATUS_INACTIVE,
        self::STATUS_ACTIVE,
        self::STATUS_RESET
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
     * El usuario puede no disponer de este identificador, en cuyo caso es null.
     * 
     * @var null|string $govId
     */
    private $govId;

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
     * Fecha de nacimiento.
     * 
     * En formato Juancrrn\Lyra\Common\CommonUtils::MYSQL_DATE_FORMAT.
     * 
     * @var DateTime $birthDate
     */
    private $birthDate;

    /**
     * Contraseña hasheada.
     * 
     * Por ser un dato sensible, no se almacenará excepto cuando sea necesario.
     * 
     * @var string $hashedPassword
     */
    // private $hashedPassword;

    /**
     * Dirección de correo electrónico.
     * 
     * @var string $emailAddress
     */
    private $emailAddress;

    /**
     * Número de teléfono.
     * 
     * @var string $phoneNumber
     */
    private $phoneNumber;

    /**
     * Identificador del usuario representante.
     * 
     * @var null|int $representativeId
     */
    private $representativeId;

    /**
     * Fecha y hora de registro.
     * 
     * En formato Juancrrn\Lyra\Common\CommonUtils::MYSQL_DATETIME_FORMAT.
     * 
     * @var DateTime $registrationDate
     */
    private $registrationDate;

    /**
     * Fecha y hora de registro.
     * 
     * En formato Juancrrn\Lyra\Common\CommonUtils::MYSQL_DATETIME_FORMAT.
     * 
     * @var DateTime $lastLoginDate
     */
    private $lastLoginDate;

    /**
     * Token utilizado para la activación y para el restablecimiento de la
     * contraseña.
     * 
     * @var string $token
     */
    private $token;

    /**
     * Estado del usuario.
     * 
     * De self::STATUSES.
     * 
     * @var string $status
     */
    private $status;

    /**
     * Grupos de permisos asociados al usuario, si se ha solicitado su carga.
     * 
     * En caso de existir, es un array de PermissionGroup.
     * 
     * @var null|array $permissionGroups
     */
    private $permissionGroups;

    public function __construct(
        int         $id,
        ?string     $govId,
        string      $firstName,
        string      $lastName,
        DateTime    $birthDate,
        // string   $hashedPassword,
        string      $emailAddress,
        string      $phoneNumber,
        ?int        $representativeId,
        DateTime    $registrationDate,
        ?DateTime   $lastLoginDate,
        ?string     $token,
        string      $status,
        ?array      $permissionGroups
    )
    {
        $this->id               = $id;
        $this->govId            = $govId;
        $this->firstName        = $firstName;
        $this->lastName         = $lastName;
        $this->birthDate        = $birthDate;
        $this->emailAddress     = $emailAddress;
        $this->phoneNumber      = $phoneNumber;
        $this->representativeId = $representativeId;
        $this->registrationDate = $registrationDate;
        $this->lastLoginDate    = $lastLoginDate;
        $this->token            = $token;
        $this->status           = $status;
        $this->permissionGroups = $permissionGroups;
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

    public function getGovId(): null|string
    {
        return $this->govId;
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

    public function getBirthDate(): DateTime
    {
        return $this->birthDate;
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    public function getRepresentativeId(): null|int
    {
        return $this->representativeId;
    }

    public function getRegistrationDate(): DateTime
    {
        return $this->registrationDate;
    }

    public function getLastLoginDate(): DateTime
    {
        return $this->lastLoginDate;
    }

    public function getToken(): null|string
    {
        return $this->token;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getPermissionGroups(): null|array
    {
        return $this->permissionGroups;
    }
}

?>