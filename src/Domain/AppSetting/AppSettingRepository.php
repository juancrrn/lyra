<?php

namespace Juancrrn\Lyra\Domain\AppSetting;

use Juancrrn\Lyra\Domain\Repository;
use mysqli;

/**
 * App settings repository
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class AppSettingRepository implements Repository
{

    protected $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    public function insert(AppSetting $appSetting): int
    {
        $query = <<< SQL
        INSERT INTO
            app_settings
            (
                short_name,
                full_name,
                description,
                value
            )
        VALUES
            ( ?, ?, ?, ? )
        SQL;

        $stmt = $this->db->prepare($query);
        
        $shortName = $appSetting->getShortName();
        $fullName = $appSetting->getFullName();
        $description = $appSetting->getDescription();
        $value = $appSetting->getValue();

        $stmt->bind_param(
            'ssss',
            $shortName,
            $fullName,
            $description,
            $value
        );
        
        $stmt->execute();

        $id = $this->db->insert_id;

        $stmt->close();

        return $id;
    }

    public function update(): void
    {
        throw new \Exception('Not implemented');
    }

    public function updateValueByShortName(string $shortName, string $newValue): void
    {
        $query = <<< SQL
        UPDATE
            app_settings
        SET
            value = ?
        WHERE
            short_name = ?
        SQL;

        $stmt = $this->db->prepare($query);
        
        $stmt->bind_param('ss', $newValue, $shortName);
        
        $stmt->execute();

        $stmt->close();
    }

    public function findById(int $testId): bool
    {
        $query = <<< SQL
        SELECT 
            id
        FROM
            app_settings
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

    public function findByShortName(int $testShortName): bool|int
    {
        $query = <<< SQL
        SELECT 
            id
        FROM
            app_settings
        WHERE
            short_name = ?
        LIMIT 1
        SQL;

        $stmt = $this->db->prepare($query);
        $stmt->bind_param('s', $testShortName);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $return = $result->fetch_object()->id;
        } else {
            $return = false;
        }

        $stmt->close();

        return $return;
    }

    public function retrieveById(int $id): AppSetting
    {
        $query = <<< SQL
        SELECT
            id,
            short_name,
            full_name,
            description,
            value
        FROM
            app_settings
        WHERE
            id = ?
        LIMIT 1
        SQL;

        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $mysqli_object = $result->fetch_object();

        $element = AppSetting::constructFromMysqliObject($mysqli_object);

        $stmt->close();

        return $element;
    }

    public function retrieveAll(): array
    {
        $query = <<< SQL
        SELECT
            id,
            short_name,
            full_name,
            description,
            value
        FROM
            app_settings
        SQL;

        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();

        $elements = [];

        while ($mysqli_object = $result->fetch_object()) {
            $currentElement = AppSetting::constructFromMysqliObject($mysqli_object);

            $elements[] = $currentElement;
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