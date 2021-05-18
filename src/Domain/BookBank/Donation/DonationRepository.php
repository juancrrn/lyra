<?php

namespace Juancrrn\Lyra\Domain\BookBank\Donation;

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\CommonUtils;
use Juancrrn\Lyra\Domain\BookBank\Donation\Donation;
use Juancrrn\Lyra\Domain\BookBank\Subject\SubjectRepository;
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

class DonationRepository implements Repository
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
     * @param Donation $item
     * 
     * @return bool|int
     */
    public function insert(Donation $item): int
    {
        $query = <<< SQL
        INSERT INTO
            book_donations
            (
                id,
                student_id,
                creation_date,
                creator_id,
                education_level,
                school_year
            )
        VALUES
            ( ?, ?, ?, ?, ?, ? )
        SQL;

        $stmt = $this->db->prepare($query);

        $id = $item->getId();
        $studentId = $item->getStudentId();
        $creationDate = $item->getCreationDate()
            ->format(CommonUtils::MYSQL_DATETIME_FORMAT);
        $creatorId = $item->getCreatorId();
        $educationLevel = $item->getEducationLevel();
        $schoolYear = $item->getSchoolYear();

        $stmt->bind_param(
            'iisisi',
            $id,
            $studentId,
            $creationDate,
            $creatorId,
            $educationLevel,
            $schoolYear
        );
        
        $result = $stmt->execute();

        $id = $this->db->insert_id;

        $stmt->close();

        return $id;
    }

    public function update(): bool|int
    {
        throw new \Exception('Not implemented');
    }

    public function findById(int $id): bool|int
    {
        throw new \Exception('Not implemented');
    }

    public function retrieveById(int $id, ?bool $loadContents = false): Donation
    {
        $query = <<< SQL
        SELECT
            id,
            student_id,
            creation_date,
            creator_id,
            education_level,
            school_year
        FROM
            book_donations
        WHERE
            id = ?
        LIMIT 1
        SQL;

        $stmt = $this->db->prepare($query);

        $stmt->bind_param('i', $id);
        $stmt->execute();
        
        $result = $stmt->get_result();

        $item = Donation::constructFromMysqliObject($result->fetch_object());

        if ($loadContents)
            $item->setContents($this->retrieveContentsById($id));

        $stmt->close();

        return $item;
    }

    public function findByStudentId(int $studentId): array
    {
        $query = <<< SQL
        SELECT
            id
        FROM
            book_donations
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

    /*
     *
     * Contenidos de paquetes
     * 
     */

    public function retrieveContentsById(int $donationId): array
    {
        $query = <<< SQL
        SELECT
            subject_id
        FROM
            book_donation_contents
        WHERE
            donation_id = ?
        SQL;

        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $donationId);
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
     * Crea una relación de contenido entre una donación y una asignatura en la
     * base de datos.
     * 
     * @param int $donationId
     * @param int $subjectId
     * 
     * @return bool|int
     */
    public function insertContent(int $donationId, int $subjectId): bool|int
    {
        $query = <<< SQL
        INSERT INTO
            book_donation_contents
            (
                donation_id,
                subject_id
            )
        VALUES
            ( ?, ? )
        SQL;

        $stmt = $this->db->prepare($query);

        $stmt->bind_param(
            'ii',
            $donationId,
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