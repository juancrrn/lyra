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

    public function findById(int $id): bool|int
    {
        throw new \Exception('Not implemented');
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
        $resultado = $stmt->get_result();
        
        $mysqli_object = $resultado->fetch_object();

        $user = PermissionGroup::constructFromMysqliObject($mysqli_object);

        $stmt->close();

        return $user;
    }

    public function retrieveAll(): array
    {
        throw new \Exception('Not implemented');
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