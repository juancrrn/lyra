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

    public function retrieveById(int $id, ?bool $loadPermissionGroups = false)/*: static*/
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
        $resultado = $stmt->get_result();
        
        $mysqli_object = $resultado->fetch_object();

        $user = User::constructFromMysqliObject($mysqli_object);

        if ($loadPermissionGroups)
            $user->setPermissionGroups($this->retrievePermissionGroupsById($user->getId()));

        $stmt->close();

        return $user;
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

        $permissionGroups = array();

        $permissionGroupRepository = new PermissionGroupRepository($this->db);

        while($mysqli_object = $result->fetch_object()) {
            $permissionGroups[] = $permissionGroupRepository->retrieveById($mysqli_object->permission_group_id);
        }

        $stmt->close();

        return $permissionGroups;
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
        
        $hashedPassword = $result->fetch_object()->hashed_password;

        $stmt->close();

        return $hashedPassword;
    }

    public function retrieveAll(): array
    {
        throw new \Exception('Not implemented');
        /*$query = <<< SQL
        SELECT 
            id,
            gov_id,
            type,
            first_name,
            last_name,
            phone_number,
            email_address,
            birth_date,
            registration_date,
            last_login_date
        FROM
            users
        SQL;

        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $resultado = $stmt->get_result();

        $users = array();

        while ($mysqli_object = $resultado->fetch_object()) {
            $users[] = $this->switchAndCompleteType($mysqli_object);
        }

        $stmt->close();

        return $users;*/
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