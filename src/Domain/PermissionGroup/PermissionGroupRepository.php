<?php

namespace Juancrrn\Lyra\Domain\PermissionGroup;

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Domain\Repository;

/**
 * Repositorio de grupos de permisos
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class PermissionGroupRepository implements Repository
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

    public function findById(int $testId): bool
    {
        $query = <<< SQL
        SELECT 
            id
        FROM
            permission_groups
        WHERE
            id = ?
        LIMIT 1
        SQL;

        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $testId);
        $stmt->execute();

        $result = $stmt->get_result();

        $return = $result->num_rows == 1;

        $stmt->close();

        return $return;
    }

    public function retrieveById(int $id): PermissionGroup
    {
        $query = <<< SQL
        SELECT
            id,
            type,
            short_name,
            full_name,
            description,
            parent,
            creation_date,
            creator_id
        FROM
            permission_groups
        WHERE
            id = ?
        LIMIT 1
        SQL;

        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $mysqli_object = $result->fetch_object();

        $element = PermissionGroup::constructFromMysqliObject($mysqli_object);

        $stmt->close();

        return $element;
    }

    public function retrieveAll(): array
    {
        $query = <<< SQL
        SELECT
            id,
            type,
            short_name,
            full_name,
            description,
            parent,
            creation_date,
            creator_id
        FROM
            permission_groups
        SQL;

        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $elements = array();

        while($mysqli_object = $result->fetch_object()) {
            $elements[] = PermissionGroup::constructFromMysqliObject($mysqli_object);
        }

        $stmt->close();

        return $elements;
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