<?php

namespace Juancrrn\Lyra\Domain\BookBank\Lot;

use DateTime;
use InvalidArgumentException;
use Juancrrn\Lyra\Common\CommonUtils;
use Juancrrn\Lyra\Domain\BookBank\Lot\Lot;
use Juancrrn\Lyra\Domain\BookBank\Subject\SubjectRepository;
use Juancrrn\Lyra\Domain\DomainConstraintsException;
use Juancrrn\Lyra\Domain\Repository;

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
                return_date
            )
        VALUES
            ( ?, ?, ?, ?, ?, ?, ? )
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

        $stmt->bind_param(
            'iississ',
            $id,
            $requestId,
            $status,
            $creationDate,
            $creatorId,
            $pickupDate,
            $returnDate
        );
        
        $stmt->execute();

        $id = $this->db->insert_id;

        $stmt->close();

        $item->setId($id);

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
            return_date = ?
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

        $stmt->bind_param(
            'iissisissi',
            $requestId,
            $studentId,
            $status,
            $creationDate,
            $creatorId,
            $educationLevel,
            $schoolYear,
            $pickupDate,
            $returnDate,
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

    public function smartUpdate(int $id, string $newStatus, array $newContents): void
    {
        $formerLot = $this->retrieveById($id);

        /*
            - from initial to:
                - initial : do nothing
                - ready : do nothing
                - rejected : do nothing
                - picked up : pickup
                - returned : add pickup y return
            - from ready to:
                - initial : do nothing
                - ready : do nothing
                - rejected : do nothing
                - picked up : pickup
                - returned : add pickup y return
            - from rejected to:
                - initial : do nothing
                - ready : do nothing
                - rejected : do nothing
                - picked up : pickup
                - returned : add pickup y return
        */

        if (
            $formerLot->getStatus() == Lot::STATUS_INITIAL ||
            $formerLot->getStatus() == Lot::STATUS_READY ||
            $formerLot->getStatus() == Lot::STATUS_REJECTED
        ) {
            if ($newStatus == Lot::STATUS_PICKED_UP) {
                $this->updatePickupDate($id);
            } elseif (
                $newStatus == Lot::STATUS_PICKED_UP ||
                $newStatus == Lot::STATUS_RETURNED
            ) {
                $this->updatePickupDate($id);
                $this->updateReturnDate($id);
            }
        }

        /*
            - from picked up to:
                - initial : remove pickup
                - ready : remove pickup
                - rejected : remove pickup
                - picked up : do nothing
                - returned : add return
        */

        elseif ($formerLot->getStatus() == Lot::STATUS_PICKED_UP) {
            if (
                $newStatus == Lot::STATUS_INITIAL ||
                $newStatus == Lot::STATUS_READY ||
                $newStatus == Lot::STATUS_REJECTED
            ) {
                $this->updateRemovePickupDate($id);
            } elseif ($newStatus == LOT::STATUS_RETURNED) {
                $this->updateReturnDate($id);
            }
        }

        /*
            - from returned to:
                - initial : remove pickup y return
                - ready : remove pickup y return
                - rejected : remove pickup y return
                - picked up : remove return
                - returned : do nothing
        */
        
        elseif ($formerLot->getStatus() == Lot::STATUS_RETURNED) {
            if (
                $newStatus == Lot::STATUS_INITIAL ||
                $newStatus == Lot::STATUS_READY ||
                $newStatus == Lot::STATUS_REJECTED
            ) {
                $this->updateRemovePickupDate($id);
                $this->updateRemoveReturnDate($id);
            } elseif ($newStatus == LOT::STATUS_PICKED_UP) {
                $this->updateRemoveReturnDate($id);
            }
        }

        $this->updateStatus($id, $newStatus);
        
        $this->updateContentsWithIds($id, $newContents);
    }

    public function updateStatus(int $id, $newStatus): void
    {
        if (! Lot::validStatus($newStatus)) 
            throw new InvalidArgumentException('Invalid status.');

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
            $newStatus,
            $id
        );
            
        $stmt->execute();

        $stmt->close();
    }

    public function updatePickupDate(int $id, ?DateTime $newPickupDate = null): void
    {
        $newPickupDate = $newPickupDate ?? new DateTime;
        $pickupDate = $newPickupDate->format(CommonUtils::MYSQL_DATETIME_FORMAT);

        $query = <<< SQL
        UPDATE
            book_lots
        SET
            pickup_date = ?
        WHERE
            id = ?
        SQL;

        $stmt = $this->db->prepare($query);

        $stmt->bind_param(
            'si',
            $pickupDate,
            $itemId
        );
            
        $stmt->execute();

        $stmt->close();
    }

    public function updateReturnDate(int $id, ?DateTime $newReturnDate = null): void
    {
        $newReturnDate = $newReturnDate ?? new DateTime;
        $returnDate = $newReturnDate->format(CommonUtils::MYSQL_DATETIME_FORMAT);

        $query = <<< SQL
        UPDATE
            book_lots
        SET
            return_date = ?
        WHERE
            id = ?
        SQL;

        $stmt = $this->db->prepare($query);

        $stmt->bind_param(
            'si',
            $returnDate,
            $itemId
        );
            
        $stmt->execute();

        $stmt->close();
    }

    public function updateRemovePickupDate(int $id): void
    {
        $query = <<< SQL
        UPDATE
            book_lots
        SET
            pickup_date = null
        WHERE
            id = ?
        SQL;

        $stmt = $this->db->prepare($query);

        $stmt->bind_param(
            'i',
            $itemId
        );
            
        $stmt->execute();

        $stmt->close();
    }

    public function updateRemoveReturnDate(int $id): void
    {
        $query = <<< SQL
        UPDATE
            book_lots
        SET
            return_date = null
        WHERE
            id = ?
        SQL;

        $stmt = $this->db->prepare($query);

        $stmt->bind_param(
            'i',
            $itemId
        );
            
        $stmt->execute();

        $stmt->close();
    }

    /*public function updateStatus(
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
    }*/

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

    public function verifyConstraintsById(int $id): array
    {
        $constraints = [];

        if (! empty($this->findContentsById($id))) {
            $constraints[] = 'book_lot_contents.lot_id';
        }

        return $constraints;
    }

    public function deleteById(int $id, ?bool $deleteContents = false): void
    {
        if ($deleteContents == true) {
            $this->deleteAllContents($id);
        }

        $constraints = $this->verifyConstraintsById($id);

        if (! empty($constraints))
            throw new DomainConstraintsException($constraints, 'Could not delete item: constraints exist.');

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

    public function findContentsById(int $lotId): array
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

        while($mysqli_object = $result->fetch_object()) {
            $elements[] = $mysqli_object->subject_id;
        }

        $stmt->close();

        return $elements;
    }

    public function retrieveContentsById(int $lotId): array
    {
        $subjectIds = $this->findContentsById($lotId);

        $elements = [];

        if (! empty($subjectIds)) {

            $subjectRepository = new SubjectRepository($this->db);
            $elements = [];
    
            foreach ($subjectIds as $subjectId) {
                $elements[] = $subjectRepository->retrieveById($subjectId);
            }
        }

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
            lot_id = ?
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

    public function deleteContentsWithIds(int $lotId, array $subjectIds): void
    {
        foreach ($subjectIds as $subjectId)
            $this->deleteContent($lotId, $subjectId);
    }

    public function deleteAllContents(int $id): void
    {
        $this->deleteContentsWithIds($id, $this->findContentsById($id));
    }

    public function insertContents(int $lotId, array $subjects): void
    {
        foreach ($subjects as $subject)
            $this->insertContent($lotId, $subject->getId());
    }

    public function insertContentsById(int $lotId, array $subjectIds): void
    {
        foreach ($subjectIds as $subjectId)
            $this->insertContent($lotId, $subjectId);
    }

    /**
     * Actualiza los enlaces de contenidos correspondientes a un paquete.
     * 
     * @param int $lotId            Identificador del paquete.
     * @param array $newContents    Array de Subject correspondiente a los
     *                              contenidos que deberán quedar al final
     *                              del proceso.
     */
    
    public function updateContents(int $requestId, array $newContentSubjects): void
    {
        $ids = [];

        foreach ($newContentSubjects as $newContentSubject) {
            $ids[] = $newContentSubject->getId();
        }

        $this->updateContentsWithIds($requestId, $ids);
    }

    public function updateContentsWithIds(int $requestId, array $newContentSubjectIds): void
    {
        $currentContentSubjects = $this->retrieveContentsById($requestId);

        // Contents to add
        // $newContents - $currentContents

        foreach ($newContentSubjectIds as $newContentSubjectId) {
            $inArray = false;

            foreach ($currentContentSubjects as $currentContentSubject)
                if ($currentContentSubject->getId() == $newContentSubjectId)
                    $inArray = true;

            if (! $inArray)
                $this->insertContent($requestId, $newContentSubjectId);
        }

        // Contents to delete
        // $currentContents - $newContents

        foreach ($currentContentSubjects as $currentContentSubject) {
            $inArray = false;

            foreach ($newContentSubjectIds as $newContentSubjectId)
                if ($newContentSubjectId == $currentContentSubject->getId())
                    $inArray = true;

            if (! $inArray)
                $this->deleteContent($requestId, $currentContentSubject->getId());
        }
    }
}