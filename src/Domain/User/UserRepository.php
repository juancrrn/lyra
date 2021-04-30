<?php

namespace Juancrrn\Lyra\Domain\User;

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Domain\PermissionGroup\PermissionGroupRepository;
use Juancrrn\Lyra\Domain\Repository;

/**
 * Repositorio de usuarios
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class UserRepository implements Repository
{

    /**
     * @var \mysqli $db     Conexión a la base de datos.
     */
    protected $db;

    /**
     * Constructor
     * 
     * @param \mysqli $db   Conexión a la base de datos.
     */
    public function __construct(\mysqli $db)
    {
        $this->db = App::getSingleton()->getDbConn();
    }

    public function update(): bool|int
    {
        throw new \Exception('Not implemented');
    }

    public function findById(int $id): bool|int
    {
        throw new \Exception('Not implemented');
    }

    public function findByGovId(string $testGovId): bool|int
    {
        $testGovId = mb_strtolower($testGovId);

        $query = <<< SQL
        SELECT 
            id
        FROM
            users
        WHERE
            gov_id = ?
        LIMIT 1
        SQL;

        $stmt = $this->db->prepare($query);
        $stmt->bind_param('s', $testGovId);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows != 1) {
            $return = false;
        } else {
            $return = $result->fetch_object()->id;
        }

        $stmt->close();

        return $return;
    }

    public function retrieveById(int $id, ?bool $loadPermissionGroups = false): User
    {
        $query = <<< SQL
        SELECT
            id,
            gov_id,
            first_name,
            last_name,
            birth_date,
            hashed_password,
            email_address,
            phone_number,
            representative_id,
            registration_date,
            last_login_date,
            token,
            status
        FROM
            users
        WHERE
            id = ?
        LIMIT 1
        SQL;

        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $mysqli_object = $result->fetch_object();

        $element = User::constructFromMysqliObject($mysqli_object);

        if ($loadPermissionGroups)
            $element->setPermissionGroups($this->retrievePermissionGroupsById($element->getId()));

        $stmt->close();

        return $element;
    }

    public function retrievePermissionGroupsById(int $userId): array
    {
        $query = <<< SQL
        SELECT
            permission_group_id
        FROM
            user_permission_group_links
        WHERE
            user_id = ?
        SQL;

        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $elements = array();

        $permissionGroupRepository = new PermissionGroupRepository($this->db);

        while($mysqli_object = $result->fetch_object()) {
            $elements[] = $permissionGroupRepository->retrieveById($mysqli_object->permission_group_id);
        }

        $stmt->close();

        return $elements;
    }

    public function retrieveJustHashedPasswordById(int $id): string
    {
        $query = <<< SQL
        SELECT
            hashed_password
        FROM
            users
        WHERE
            id = ?
        LIMIT 1
        SQL;

        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();

        $result = $stmt->get_result();
        
        $element = $result->fetch_object()->hashed_password;

        $stmt->close();

        return $element;
    }

    public function retrieveAll(?bool $loadPermissionGroups = false): array
    {
        $query = <<< SQL
        SELECT
            id,
            gov_id,
            first_name,
            last_name,
            birth_date,
            hashed_password,
            email_address,
            phone_number,
            representative_id,
            registration_date,
            last_login_date,
            token,
            status
        FROM
            users
        SQL;

        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();

        $elements = array();

        while ($mysqli_object = $result->fetch_object()) {
            $currentElement = User::constructFromMysqliObject($mysqli_object);

            if ($loadPermissionGroups)
                $currentElement->setPermissionGroups($this->retrievePermissionGroupsById($currentElement->getId()));

            $elements[] = $currentElement;
        }

        $stmt->close();

        return $elements;
    }

    public function verifyConstraintsById(int $id): bool|array
    {
        throw new \Exception('Not implemented');
    }

    public function deleteById(int $id): bool
    {
        throw new \Exception('Not implemented');
    }
}