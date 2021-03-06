<?php

namespace Juancrrn\Lyra\Domain\BookBank\Request;

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\CommonUtils;
use Juancrrn\Lyra\Domain\BookBank\Request\Request;
use Juancrrn\Lyra\Domain\Repository;
use mysqli;

/**
 * Repositorio de donaciones
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class RequestRepository implements Repository
{

    protected $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    /**
     * Inserta un elemento a la base de datos.
     * 
     * @param Request $item
     * 
     * @return bool|int
     */
    public function insert(Request $item): int
    {
        $query = <<< SQL
        INSERT INTO
            book_requests
            (
                student_id,
                status,
                creation_date,
                creator_id,
                education_level,
                school_year,
                specification,
                locked
            )
        VALUES
            ( ?, ?, ?, ?, ?, ?, ?, ? )
        SQL;

        $stmt = $this->db->prepare($query);

        $studentId = $item->getStudentId();
        $status = $item->getStatus();
        $creationDate = $item->getCreationDate()
            ->format(CommonUtils::MYSQL_DATETIME_FORMAT);
        $creatorId = $item->getCreatorId();
        $educationLevel = $item->getEducationLevel();
        $schoolYear = $item->getSchoolYear();
        $specification = $item->getSpecification();
        $locked = $item->isLocked();

        $stmt->bind_param(
            'issisisi',
            $studentId,
            $status,
            $creationDate,
            $creatorId,
            $educationLevel,
            $schoolYear,
            $specification,
            $locked
        );
        
        $stmt->execute();

        $id = $this->db->insert_id;

        $stmt->close();

        return $id;
    }

    public function update(): bool|int
    {
        throw new \Exception('Not implemented');
    }

    public function updateStatusSpecificationAndEducationLevelById(
        int $id,
        string $newStatus,
        string $newSpecification,
        string $newEducationLevel
    ): void
    {
        $query = <<< SQL
        UPDATE
            book_requests
        SET
            status = ?,
            specification = ?,
            education_level = ?
        WHERE
            id = ?
        SQL;

        $stmt = $this->db->prepare($query);

        $stmt->bind_param(
            'sssi',
            $newStatus,
            $newSpecification,
            $newEducationLevel,
            $id
        );
        
        $stmt->execute();

        $stmt->close();
    }

    public function updateStatusById(int $id, string $newStatus): void
    {
        $query = <<< SQL
        UPDATE
            book_requests
        SET
            status = ?
        WHERE
            id = ?
        SQL;

        $stmt = $this->db->prepare($query);

        $stmt->bind_param(
            'si',
            $newStatus,
            $id
        );
        
        $stmt->execute();

        $stmt->close();
    }

    public function findById(int $testId): bool
    {
        $query = <<< SQL
        SELECT 
            id
        FROM
            book_requests
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

    public function findByStudentId(int $studentId): array
    {
        $query = <<< SQL
        SELECT
            id
        FROM
            book_requests
        WHERE
            student_id = ?
        ORDER BY
            creation_date
        DESC
        SQL;

        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $studentId);
        $stmt->execute();
        $result = $stmt->get_result();

        $items = array();

        while ($item = $result->fetch_object()) {
            $items[] = $item->id;
        }

        $stmt->close();

        return $items;
    }
    
    public function findReturnsByStudentId(int $studentId): array
    {
        $query = <<< SQL
        SELECT
            id
        FROM
            book_requests
        WHERE
            student_id = ?
        AND
            status = 'book_request_status_processed'
        AND
            EXISTS (
                SELECT
                    id
                FROM
                    book_lots
                WHERE
                    status = 'book_lot_status_picked_up'
                AND
                    request_id = book_requests.id
            )
        ORDER BY
            creation_date
        DESC
        SQL;

        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $studentId);
        $stmt->execute();
        $result = $stmt->get_result();

        $items = array();

        while ($item = $result->fetch_object()) {
            $items[] = $item->id;
        }

        $stmt->close();

        return $items;
    }
    
    public function findPickupsByStudentId(int $studentId): array
    {
        $query = <<< SQL
        SELECT
            id
        FROM
            book_requests
        WHERE
            student_id = ?
        AND
            status = 'book_request_status_processed'
        AND
            EXISTS (
                SELECT
                    id
                FROM
                    book_lots
                WHERE
                    status = 'book_lot_status_ready'
                AND
                    request_id = book_requests.id
            )
        ORDER BY
            creation_date
        DESC
        SQL;

        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $studentId);
        $stmt->execute();
        $result = $stmt->get_result();

        $items = array();

        while ($item = $result->fetch_object()) {
            $items[] = $item->id;
        }

        $stmt->close();

        return $items;
    }
    public function findPending(): array
    {
        $app = App::getSingleton();

        $schoolYear = $app->getSetting('school-year');

        $query = <<< SQL
        SELECT
            id
        FROM
            book_requests
        WHERE
            status = 'book_request_status_pending'
        AND
            school_year = $schoolYear
        ORDER BY
            id
        ASC
        SQL;

        $stmt = $this->db->prepare($query);
        
        $stmt->execute();
        $result = $stmt->get_result();

        $items = array();

        while ($item = $result->fetch_object()) {
            $items[] = $item->id;
        }

        $stmt->close();

        return $items;
    }

    public function retrieveById(int $id): Request
    {
        
        $query = <<< SQL
        SELECT
            id,
            student_id,
            status,
            creation_date,
            creator_id,
            education_level,
            school_year,
            specification,
            locked
        FROM
            book_requests
        WHERE
            id = ?
        LIMIT 1
        SQL;

        $stmt = $this->db->prepare($query);

        $stmt->bind_param('i', $id);
        
        $stmt->execute();

        $result = $stmt->get_result();

        $item = Request::constructFromMysqliObject($result->fetch_object());

        $stmt->close();
        
        return $item;
    }

    public function retrieveByIds(array $ids): array
    {
        
        $query = <<< SQL
        SELECT
            id,
            student_id,
            status,
            creation_date,
            creator_id,
            education_level,
            school_year,
            specification,
            locked
        FROM
            book_requests
        WHERE
            id = ?
        LIMIT 1
        SQL;

        $stmt = $this->db->prepare($query);

        $items = [];

        foreach ($ids as $id) {
            $stmt->bind_param('i', $id);
            
            $stmt->execute();

            $result = $stmt->get_result();

            $items[] = Request::constructFromMysqliObject($result->fetch_object());
        }

        $stmt->close();
        
        return $items;
    }

    public function retrieveAll(): array
    {
        throw new \Exception('Not implemented');
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