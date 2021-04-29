<?php

namespace Juancrrn\Lyra\Domain\User;

use Juancrrn\Lyra\Common\App;
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
     * Constructor.
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
        $testGovId = mb_strtoupper($testGovId);

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

    public function retrieveById(int $id)/*: static*/
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
        WHERE
            id = ?
        LIMIT 1
        SQL;

        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        $mysqli_object = $resultado->fetch_object();

        $user = $this->switchAndCompleteType($mysqli_object);

        $stmt->close();

        return $user;*/
    }

    public function retrieveJustHashedPasswordById(int $id): string
    {
        throw new \Exception('Not implemented');
        /*$query = <<< SQL
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

        return $hashedPassword;*/
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