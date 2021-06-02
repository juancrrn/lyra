<?php

namespace Juancrrn\Lyra\Domain\BookBank\Lot;

use DateTime;
use InvalidArgumentException;
use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\CommonUtils;
use Juancrrn\Lyra\Domain\BookBank\Lot\Lot;
use Juancrrn\Lyra\Domain\BookBank\Subject\SubjectRepository;
use Juancrrn\Lyra\Domain\Repository;
use RuntimeException;

/**
 * Repositorio de paquetes
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class LotRepository implements Repository
{

    protected $db;

    public function __construct(\mysqli $db)
    {
        $this->db = $db;
    }

    public function insert(Lot $item): int
    {
        $query = <<< SQL
        INSERT INTO
            book_lots
            (
                id,
                request_id,
                status,
                creation_date,
                creator_id,
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
        $status = $item->getStatus();
        $creationDate = $item->getCreationDate()
            ->format(CommonUtils::MYSQL_DATETIME_FORMAT);
        $creatorId = $item->getCreatorId();
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
            $status,
            $creationDate,
            $creatorId,
            $pickupDate,
            $returnDate,
            $locked
        );
        
        $stmt->execute();

        $id = $this->db->insert_id;

        $stmt->close();

        $itemContents = $item->getContents();

        if (is_array($itemContents) && ! empty($itemContents))
            $this->insertContents($id, $itemContents);

        return $id;
    }

    public function update(Lot $item): bool
    {
        $query = <<< SQL
        UPDATE
            book_lots
        SET
            request_id = ?,
            status = ?,
            creation_date = ?,
            creator_id = ?,
            pickup_date = ?,
            return_date = ?,
            locked = ?
        WHERE
            id = ?
        SQL;

        $stmt = $this->db->prepare($query);

        $id = $item->getId();
        $requestId = $item->getRequestId();
        $status = $item->getStatus();
        $creationDate = $item->getCreationDate()
            ->format(CommonUtils::MYSQL_DATETIME_FORMAT);
        $creatorId = $item->getCreatorId();
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
            'iissisissii',
            $requestId,
            $studentId,
            $status,
            $creationDate,
            $creatorId,
            $educationLevel,
            $schoolYear,
            $pickupDate,
            $returnDate,
            $locked,
            $id
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

    public function updateStatus(
        int $itemId,
        string $status,
        ?bool $setPickupDate = false,
        ?bool $setReturnDate = false
    ): bool
    {
        if (! in_array($status, Lot::STATUSES)) 
            throw new InvalidArgumentException('Invalid status.');

        if ($setPickupDate == true) {
            $query = <<< SQL
                UPDATE
                    book_lots
                SET
                    status = ?,
                    pickup_date = ?
                WHERE
                    id = ?
                SQL;

            $pickupDate = (new DateTime)->format(CommonUtils::MYSQL_DATETIME_FORMAT);

            $stmt = $this->db->prepare($query);

            $stmt->bind_param(
                'ssi',
                $status,
                $pickupDate,
                $itemId
            );
        } elseif ($setReturnDate == true) {
            $query = <<< SQL
                UPDATE
                    book_lots
                SET
                    status = ?,
                    return_date = ?
                WHERE
                    id = ?
                SQL;

            $returnDate = (new DateTime)->format(CommonUtils::MYSQL_DATETIME_FORMAT);

            $stmt = $this->db->prepare($query);

            $stmt->bind_param(
                'ssi',
                $status,
                $returnDate,
                $id
            );
        } else {
            $query = <<< SQL
                UPDATE
                    book_lots
                SET
                    status = ?
                WHERE
                    id = ?
                SQL;

            $stmt = $this->db->prepare($query);
            
            $stmt->bind_param(
                'si',
                $status,
                $id
            );
        }
        
        $result = $stmt->execute();

        $stmt->close();

        return $result;
    }

    public function findById(int $id): bool|int
    {
        throw new \Exception('Not implemented');
    }

    public function findByStudentId(int $studentId): array
    {
        $query = <<< SQL
        SELECT
            id
        FROM
            book_lots
        WHERE
            student_id = ?
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

    public function findByRequestId(int $requestId): int
    {
        $query = <<< SQL
        SELECT
            id
        FROM
            book_lots
        WHERE
            request_id = ?
        LIMIT 1
        SQL;

        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $requestId);
        $stmt->execute();
        $result = $stmt->get_result();

        $itemId = $result->fetch_object()->id;

        $stmt->close();

        return $itemId;
    }

    public function retrieveById(int $id, ?bool $loadContents = false): Lot
    {
        $query = <<< SQL
        SELECT
            id,
            request_id,
            status,
            creation_date,
            creator_id,
            pickup_date,
            return_date,
            locked
        FROM
            book_lots
        WHERE
            id = ?
        LIMIT 1
        SQL;

        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $mysqli_object = $result->fetch_object();

        $element = Lot::constructFromMysqliObject($mysqli_object);

        if ($loadContents)
            $element->setContents($this->retrieveContentsById($id));

        $stmt->close();

        return $element;
    }

    public function retrieveAll(): array
    {
        throw new \Exception('Not implemented');
    }

    public function verifyConstraintsById(int $id): bool|array
    {
        throw new \Exception('Not implemented');
    }

    public function deleteById(int $id, ?bool $deleteContents = false): void
    {
        throw new \Exception('Not implemented');

        // TODO delete contents and modify constraints validation

        $constraintsVerification = $this->verifyConstraintsById($id);

        if (! $constraintsVerification)
            throw new RuntimeException('Found constraints: ' . implode(',', $constraintsVerification));

        $query = <<< SQL
            DELETE FROM
                book_lots
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

    /*
     *
     * Contenidos de paquetes
     * 
     */

    public function retrieveContentsById(int $lotId): array
    {
        $query = <<< SQL
        SELECT
            subject_id
        FROM
            book_lot_contents
        WHERE
            lot_id = ?
        SQL;

        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $lotId);
        $stmt->execute();
        $result = $stmt->get_result();

        $elements = array();

        $subjectRepository = new SubjectRepository($this->db);

        while($mysqli_object = $result->fetch_object()) {
            $elements[] = $subjectRepository->retrieveById($mysqli_object->subject_id);
        }

        $stmt->close();

        return $elements;
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
    public function insertContent(int $lotId, int $subjectId): int
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
        
        $stmt->execute();

        $id = $this->db->insert_id;

        $stmt->close();

        return $id;
    }

    public function deleteContent(int $lotId, int $subjectId): int
    {
        $query = <<< SQL
        DELETE FROM
            book_lot_contents
        WHERE
            lot_id = ?,
        AND
            subject_id = ?
        SQL;

        $stmt = $this->db->prepare($query);

        $stmt->bind_param(
            'ii',
            $lotId,
            $subjectId
        );
        
        $result = $stmt->execute();

        $stmt->close();

        return $result;
    }

    public function insertContents(int $lotId, array $subjects): void
    {
        foreach ($subjects as $subject)
            $this->insertContent($lotId, $subject->getId());
    }

    /**
     * Actualiza los enlaces de contenidos correspondientes a un paquete.
     * 
     * @param int $lotId            Identificador del paquete.
     * @param array $newContents    Array de Subject correspondiente a los
     *                              contenidos que deberán quedar al final
     *                              del proceso.
     */
    public function updateContents(int $lotId, array $newContentSubjects): void
    {
        $currentContentSubjects = $this->retrieveContentsById($lotId);

        // Contents to add
        // $newContents - $currentContents

        foreach ($newContentSubjects as $newContentSubject) {
            $inArray = false;

            foreach ($currentContentSubjects as $currentContentSubject)
                if ($currentContentSubject->getId() == $newContentSubject->getId())
                    $inArray = true;

            if (! $inArray)
                $this->insertContent($lotId, $newContentSubject->getId());
        }

        // Contents to delete
        // $currentContents - $newContents

        foreach ($currentContentSubjects as $currentContentSubject) {
            $inArray = false;

            foreach ($newContentSubjects as $newContentSubject)
                if ($newContentSubject->getId() == $currentContentSubject->getId())
                    $inArray = true;

            if (! $inArray)
                $this->deleteContent($lotId, $currentContentSubject->getId());
        }
    }
}