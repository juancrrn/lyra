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
    public function insert(Subject $item): bool|int
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

        if ($result) {
            return $id;
        } else {
            return false;
        }
    }

    public function update(): bool|int
    {
        throw new \Exception('Not implemented');
    }

    public function findById(int $id): bool|int
    {
        throw new \Exception('Not implemented');
    }

    public function retrieveById(int $id): Subject
    {
        throw new \Exception('Not implemented');
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