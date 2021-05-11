<?php

namespace Juancrrn\Lyra\Domain\BookBank\Lot;

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\CommonUtils;
use Juancrrn\Lyra\Domain\BookBank\Lot\Lot;
use Juancrrn\Lyra\Domain\Repository;

/**
 * Repositorio de donaciones
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class LotRepository implements Repository
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
     * @param Lot $item
     * 
     * @return bool|int
     */
    public function insert(Lot $item): bool|int
    {
        $query = <<< SQL
        INSERT INTO
            book_lots
            (
                id,
                request_id,
                student_id,
                status,
                creation_date,
                creator_id,
                education_level,
                school_year,
                pickup_date,
                return_date,
                locked
            )
        VALUES
            ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )
        SQL;

        $stmt = $this->db->prepare($query);

        $id = $item->getId();
        $requestId = $item->getRequestId();
        $studentId = $item->getStudentId();
        $status = $item->getStatus();
        $creationDate = $item->getCreationDate()
            ->format(CommonUtils::MYSQL_DATETIME_FORMAT);
        $creatorId = $item->getCreatorId();
        $educationLevel = $item->getEducationLevel();
        $schoolYear = $item->getSchoolYear();
        $pickupDate = $item->getPickupDate() ? // Nullable
            $item->getPickupDate()
                ->format(CommonUtils::MYSQL_DATETIME_FORMAT):
            null;
        $returnDate = $item->getReturnDate() ? // Nullable
            $item->getReturnDate()
                ->format(CommonUtils::MYSQL_DATETIME_FORMAT):
            null;
        $locked = $item->isLocked();

        $stmt->bind_param(
            'iiissisissi',
            $id,
            $requestId,
            $studentId,
            $status,
            $creationDate,
            $creatorId,
            $educationLevel,
            $schoolYear,
            $pickupDate,
            $returnDate,
            $locked
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

    public function retrieveById(int $id): Lot
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

    /**
     * Crea una relación de contenido entre un paquete y una asignatura en la
     * base de datos.
     * 
     * @param int $lotId
     * @param int $subjectId
     * 
     * @return bool|int
     */
    public function insertContent(int $lotId, int $subjectId): bool|int
    {
        $query = <<< SQL
        INSERT INTO
            book_lot_contents
            (
                lot_id,
                subject_id
            )
        VALUES
            ( ?, ? )
        SQL;

        $stmt = $this->db->prepare($query);

        $stmt->bind_param(
            'ii',
            $lotId,
            $subjectId
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
}