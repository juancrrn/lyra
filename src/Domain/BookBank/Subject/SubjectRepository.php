<?php

namespace Juancrrn\Lyra\Domain\BookBank\Subject;

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\CommonUtils;
use Juancrrn\Lyra\Domain\BookBank\Subject\Subject;
use Juancrrn\Lyra\Domain\Repository;

/**
 * Repositorio de asignaturas
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class SubjectRepository implements Repository
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

    /**
     * Inserta un elemento a la base de datos.
     * 
     * @param Subject $item
     * 
     * @return bool|int
     */
    public function insert(Subject $item): int
    {
        $query = <<< SQL
        INSERT INTO
            book_subjects
            (
                id,
                name,
                education_level,
                school_year,
                book_name,
                book_isbn,
                book_image_url,
                creation_date,
                creator_id
            )
        VALUES
            ( ?, ?, ?, ?, ?, ?, ?, ?, ? )
        SQL;

        $stmt = $this->db->prepare($query);

        $id = $item->getId();
        $name = $item->getName();
        $educationLevel = $item->getEducationLevel();
        $schoolYear = $item->getSchoolYear();
        $bookName = $item->getBookName(); // Nullable
        $bookIsbn = $item->getBookIsbn(); // Nullable
        $bookImageUrl = $item->getBookImageUrl(); // Nullable
        $creationDate = $item->getCreationDate()
            ->format(CommonUtils::MYSQL_DATETIME_FORMAT);
        $creatorId = $item->getCreatorId();

        $stmt->bind_param(
            'ississssi',
            $id,
            $name,
            $educationLevel,
            $schoolYear,
            $bookName,
            $bookIsbn,
            $bookImageUrl,
            $creationDate,
            $creatorId
        );
        
        $result = $stmt->execute();

        $id = $this->db->insert_id;

        $stmt->close();

        return $id;
    }

    public function update(Subject $item): void
    {
        $query = <<< SQL
        UPDATE
            book_subjects
        SET
            name = ?,
        AND
            education_level = ?,
        AND
            school_year = ?,
        AND
            book_name = ?,
        AND
            book_isbn = ?,
        AND
            book_image_url = ?,
        AND
            creation_date = ?,
        AND
            creator_id = ?
        WHERE
            id = ?
        SQL;

        $stmt = $this->db->prepare($query);

        $id = $item->getId();
        $name = $item->getName();
        $educationLevel = $item->getEducationLevel();
        $schoolYear = $item->getSchoolYear();
        $bookName = $item->getBookName(); // Nullable
        $bookIsbn = $item->getBookIsbn(); // Nullable
        $bookImageUrl = $item->getBookImageUrl(); // Nullable
        $creationDate = $item->getCreationDate()
            ->format(CommonUtils::MYSQL_DATETIME_FORMAT);
        $creatorId = $item->getCreatorId();

        $stmt->bind_param(
            'ssissssii',
            $name,
            $educationLevel,
            $schoolYear,
            $bookName,
            $bookIsbn,
            $bookImageUrl,
            $creationDate,
            $creatorId,
            $id
        );
        
        $stmt->execute();

        $stmt->close();
    }

    public function findById(int $testId): bool|int
    {
        $query = <<< SQL
        SELECT 
            id
        FROM
            book_subjects
        WHERE
            id = ?
        LIMIT 1
        SQL;

        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $testId);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows != 1) {
            $return = false;
        } else {
            $return = $testId;
        }

        $stmt->close();

        return $return;
    }

    public function retrieveById(int $id): Subject
    {
        $query = <<< SQL
        SELECT
            id,
            name,
            education_level,
            school_year,
            book_name,
            book_isbn,
            book_image_url,
            creation_date,
            creator_id
        FROM
            book_subjects
        WHERE
            id = ?
        LIMIT 1
        SQL;

        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $mysqli_object = $result->fetch_object();

        $element = Subject::constructFromMysqliObject($mysqli_object);

        $stmt->close();

        return $element;
    }

    public function retrieveAll(): array
    {
        throw new \Exception('Not implemented');
    }

    public function search(string $keyword, ?bool $loadModel = false): array
    {
        $keyword = '%' . $keyword . '%';

        $query = <<< SQL
        SELECT 
            id
        FROM
            book_subjects        
        WHERE
            school_year = 20212022
        AND
        (
            name LIKE ?
        OR
            book_name LIKE ?
        OR
            book_isbn LIKE ?
        )
        LIMIT 8
        SQL;

        $stmt = $this->db->prepare($query);
        $stmt->bind_param(
            'sss',
            $keyword,
            $keyword,
            $keyword
        );
        $stmt->execute();

        $result = $stmt->get_result();

        $items = [];

        while ($object = $result->fetch_object()) {
            $items[] = $object->id;
        }

        if ($loadModel) {
            $return = [];

            foreach ($items as $userId) {
                $return[] = $this->retrieveById($userId);
            }
        } else {
            $return = $items;
        }

        $stmt->close();

        return $return;
    }

    public function verifyConstraintsById(int $id): bool|array
    {
        throw new \Exception('Not implemented');
    }

    public function deleteById(int $id): void
    {
        $query = <<< SQL
        DELETE FROM
            book_subjects
        WHERE
            id = ?
        SQL;

        $stmt = $this->db->prepare($query);

        $stmt->bind_param(
            'i',
            $id
        );
        
        $stmt->execute();

        $stmt->close();
    }
}