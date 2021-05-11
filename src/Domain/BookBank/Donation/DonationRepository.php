<?php

namespace Juancrrn\Lyra\Domain\BookBank\Donation;

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\CommonUtils;
use Juancrrn\Lyra\Domain\BookBank\Donation\Donation;
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
    public function insert(Donation $item): bool|int
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

    public function retrieveById(int $id): Donation
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