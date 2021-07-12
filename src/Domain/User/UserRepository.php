<?php

namespace Juancrrn\Lyra\Domain\User;

use DateTime;
use InvalidArgumentException;
use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\CommonUtils;
use Juancrrn\Lyra\Domain\PermissionGroup\PermissionGroupRepository;
use Juancrrn\Lyra\Domain\Repository;
use mysqli;

/**
 * Repositorio de usuarios
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class UserRepository implements Repository
{

    protected $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    /**
     * Inserta un elemento a la base de datos.
     * 
     * @param User $user
     * @param string|null $hashedPassword
     * 
     * @return bool|int
     */
    public function insert(User $user, ?string $hashedPassword = null, ?array $permissionGroupIds = null): bool|int
    {
        $query = <<< SQL
        INSERT INTO
            users
            (
                gov_id,
                first_name,
                last_name,
                birth_date,
                hashed_password,
                email_address,
                phone_number,
                representative_id,
                registration_date,
                last_login_date,
                token,
                status
            )
        VALUES
            ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )
        SQL;

        $stmt = $this->db->prepare($query);
        
        $govId = $user->getGovId(); // Nullable
        $firstName = $user->getFirstName();
        $lastName = $user->getLastName();
        $birthDate = $user->getBirthDate()
            ->format(CommonUtils::MYSQL_DATE_FORMAT);
        $emailAddress = $user->getEmailAddress();
        $phoneNumber = $user->getPhoneNumber();
        $representativeId = $user->getRepresentativeId(); // Nullable
        $registrationDate = $user->getRegistrationDate()
            ->format(CommonUtils::MYSQL_DATETIME_FORMAT);
        $lastLoginDate = $user->getLastLoginDate() ? // Nullable
            $user->getLastLoginDate()
            ->format(CommonUtils::MYSQL_DATETIME_FORMAT) :
            null;
        $token = $user->getToken(); // Nullable
        $status = $user->getStatus();

        $stmt->bind_param(
            'sssssssissss',
            $govId,
            $firstName,
            $lastName,
            $birthDate,
            $hashedPassword,
            $emailAddress,
            $phoneNumber,
            $representativeId,
            $registrationDate,
            $lastLoginDate,
            $token,
            $status
        );
        
        $stmt->execute();

        $id = $this->db->insert_id;

        $stmt->close();

        $this->createPermissionGroupLinksWithIds($id, $permissionGroupIds);

        return $id;
    }

    public function update(User $updatedUser, array $newPermissionGroupIds): void
    {
        $query = <<< SQL
        UPDATE
            users
        SET
            gov_id = ?,
            first_name = ?,
            last_name = ?,
            birth_date = ?,
            email_address = ?,
            phone_number = ?,
            representative_id = ?,
            status = ?
        WHERE
            id = ?
        SQL;

        $stmt = $this->db->prepare($query);

        $govId              = $updatedUser->getGovId();
        $firstName          = $updatedUser->getFirstName();
        $lastName           = $updatedUser->getLastName();
        $birthDate          = $updatedUser->getBirthDate() == null ? null :
            $updatedUser->getBirthDate()->format(CommonUtils::MYSQL_DATE_FORMAT);
        $emailAddress       = $updatedUser->getEmailAddress();
        $phoneNumber        = $updatedUser->getPhoneNumber();
        $representativeId   = $updatedUser->getRepresentativeId();
        $status             = $updatedUser->getStatus();
        $id                 = $updatedUser->getId();
        
        $stmt->bind_param(
            'ssssssisi',
            $govId,
            $firstName,
            $lastName,
            $birthDate,
            $emailAddress,
            $phoneNumber,
            $representativeId,
            $status,
            $id
        );
        
        $stmt->execute();

        $stmt->close();

        $this->updatePermissionGroupsWithIds($newPermissionGroupIds);

        return;
    }

    /**
     * Actualiza la fecha de último inicio de sesión.
     * 
     * @requires    Existe un usuario en la base de datos con el identificador
     *              especificado.
     * 
     * @param int $id
     * @param DateTime $newLastLoginDate
     * 
     * @return bool
     */
    public function updateLastLoginDateById(int $id, ?DateTime $newLastLoginDate = null): bool
    {
        if (! $newLastLoginDate)
            $newLastLoginDate = new DateTime;

        $newLastLoginDate = $newLastLoginDate->format(CommonUtils::MYSQL_DATETIME_FORMAT);

        $query = <<< SQL
        UPDATE
            users
        SET
            last_login_date = ?
        WHERE
            id = ?
        SQL;

        $stmt = $this->db->prepare($query);
        
        $stmt->bind_param('si', $newLastLoginDate, $id);
        
        $result = $stmt->execute();

        $stmt->close();

        return $result;
    }

    public function updatePermissionGroupsWithIds(array $permissionGroupIds): void
    {

    }

    /**
     * Genera un token de recuperación para un usuario, lo guarda en su fila
     * de la base de datos y lo devuelve.
     * 
     * Además, si el usuario estaba en User::STATUS_ACTIVE, lo modifica a
     * User::STATUS_RESET. Si estaba en User::STATUS_INACTIVE, continúa igual.
     * 
     * @requires    Existe un usuario en la base de datos con el identificador
     *              especificado.
     * 
     * @param int $id
     * 
     * @return bool|string  False si no se pudo completar la operación o el
     *                      token en caso positivo.
     */
    public function generateAndUpdateTokenAndStatusById(int $id): bool|string
    {
        $user = $this->retrieveById($id);

        $token = User::generateToken($user->getGovId());

        $status = $user->getStatus() == User::STATUS_INACTIVE ?
            User::STATUS_INACTIVE : User::STATUS_RESET;

        $query = <<< SQL
        UPDATE
            users
        SET
            token = ?,
            status = ?
        WHERE
            id = ?
        SQL;

        $stmt = $this->db->prepare($query);
        $stmt->bind_param('ssi', $token, $status, $id);
        
        $result = $stmt->execute();

        $stmt->close();

        if ($result == true) {
            return $token;
        } else {
            return false;
        }
    }

    /**
     * Termina el proceso de activación o de restablecimiento de contraseña con
     * token, dejando al usuario en User::STATUS_ACTIVE y con su nueva
     * contraseña.
     * 
     * @requires    Existe un usuario en la base de datos con el identificador
     *              especificado.
     * 
     * @param int $id
     * @param string $hashedPassword
     * 
     * @return bool Resultado de la operación.
     */
    public function finalizeTokenProcessById(int $id, string $hashedPassword): bool
    {
        $status = User::STATUS_ACTIVE;
        
        $query = <<< SQL
        UPDATE
            users
        SET
            hashed_password = ?,
            token = null,
            status = ?
        WHERE
            id = ?
        SQL;

        $stmt = $this->db->prepare($query);
        $stmt->bind_param('ssi', $hashedPassword, $status, $id);
        
        $result = $stmt->execute();

        $stmt->close();

        return $result;
    }

    public function search(string $query, ?bool $loadModel = false): array
    {
        $keywords = explode(' ', $query);

        $query = <<< SQL
        SELECT 
            id
        FROM
            users        
        WHERE\n
        SQL;

        for ($i = 0; $i < count($keywords); $i++) {
            $query .= <<< SQL
                (
                        gov_id LIKE ?
                    OR
                        first_name LIKE ?
                    OR
                        last_name LIKE ?
                    OR
                        email_address LIKE ?
                    OR
                        phone_number LIKE ?
                )\n
            SQL;

            if ($i != count($keywords) - 1)
                $query .= "\tAND\n";
        }
        
        $query .= <<< SQL
        LIMIT 8
        SQL;

        $stmt = $this->db->prepare($query);

        $paramTypes = str_repeat('sssss', count($keywords));

        $paramValues = [];

        for ($i = 0; $i < count($keywords); $i++)
            for ($j = 0; $j < 5; $j++)
                $paramValues[] = '%' . $keywords[$i] . '%';

        $stmt->bind_param(
            $paramTypes,
            ...$paramValues
        );

        $stmt->execute();

        $result = $stmt->get_result();

        $ids = [];

        while ($object = $result->fetch_object()) {
            $ids[] = $object->id;
        }

        if ($loadModel) {
            $return = [];

            foreach ($ids as $userId) {
                $return[] = $this->retrieveById($userId);
            }
        } else {
            $return = $ids;
        }

        $stmt->close();

        return $return;
    }

    public function searchStudents(string $query, ?bool $loadModel = false): array
    {
        $keywords = explode(' ', $query);

        $query = <<< SQL
        SELECT 
            id
        FROM
            users        
        WHERE
            EXISTS
                (
                    SELECT
                        id
                    FROM
                        user_permission_group_links
                    WHERE
                        user_id = users.id
                    AND
                        permission_group_id IN (
                            SELECT
                                id
                            FROM
                                permission_groups
                            WHERE
                                short_name = 'student'
                        )
                )
            AND\n
        SQL;

        for ($i = 0; $i < count($keywords); $i++) {
            $query .= <<< SQL
                (
                        gov_id LIKE ?
                    OR
                        first_name LIKE ?
                    OR
                        last_name LIKE ?
                    OR
                        email_address LIKE ?
                    OR
                        phone_number LIKE ?
                )\n
            SQL;

            if ($i != count($keywords) - 1)
                $query .= "\tAND\n";
        }
        
        $query .= <<< SQL
        LIMIT 8
        SQL;

        $stmt = $this->db->prepare($query);

        $paramTypes = str_repeat('sssss', count($keywords));

        $paramValues = [];

        for ($i = 0; $i < count($keywords); $i++)
            for ($j = 0; $j < 5; $j++)
                $paramValues[] = '%' . $keywords[$i] . '%';

        $stmt->bind_param(
            $paramTypes,
            ...$paramValues
        );

        $stmt->execute();

        $result = $stmt->get_result();

        $ids = [];

        while ($object = $result->fetch_object()) {
            $ids[] = $object->id;
        }

        if ($loadModel) {
            $return = [];

            foreach ($ids as $userId) {
                $return[] = $this->retrieveById($userId);
            }
        } else {
            $return = $ids;
        }

        $stmt->close();

        return $return;
    }

    public function findById(int $testId): bool
    {
        $query = <<< SQL
        SELECT 
            id
        FROM
            users
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

    /**
     * Comprueba si existe un usuario en base a un token.
     * 
     * @param string $testToken
     * 
     * @return bool|int El identificador en caso de existir o false en otro
     *                  caso.
     */
    public function findByToken(string $testToken): bool|int
    {
        $query = <<< SQL
        SELECT 
            id
        FROM
            users
        WHERE
            token = ?
        LIMIT 1
        SQL;

        $stmt = $this->db->prepare($query);
        $stmt->bind_param('s', $testToken);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows != 1) {
            $return = false;
        } else {
            $return = $result->fetch_object()->id;
        }

        $stmt->close();

        return $return;
    }

    /**
     * Comprueba si existe un usuario en base a un NIF o NIE.
     * 
     * @param string $testGovId
     * 
     * @return bool|int El identificador en caso de existir o false en otro
     *                  caso.
     */
    public function findByGovId(string $testGovId): bool|int
    {
        $testGovId = mb_strtolower($testGovId);

        $query = <<< SQL
        SELECT 
            id
        FROM
            users
        WHERE
            gov_id = ?
        LIMIT 1
        SQL;

        $stmt = $this->db->prepare($query);
        $stmt->bind_param('s', $testGovId);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows != 1) {
            $return = false;
        } else {
            $return = $result->fetch_object()->id;
        }

        $stmt->close();

        return $return;
    }

    public function retrieveById(int $id, ?bool $loadPermissionGroups = false): User
    {
        $query = <<< SQL
        SELECT
            id,
            gov_id,
            first_name,
            last_name,
            birth_date,
            hashed_password,
            email_address,
            phone_number,
            representative_id,
            registration_date,
            last_login_date,
            token,
            status
        FROM
            users
        WHERE
            id = ?
        LIMIT 1
        SQL;

        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $mysqli_object = $result->fetch_object();

        $element = User::constructFromMysqliObject($mysqli_object);

        if ($loadPermissionGroups)
            $element->setPermissionGroups($this->retrievePermissionGroupsById($element->getId()));

        $stmt->close();

        return $element;
    }

    public function retrieveJustHashedPasswordById(int $id): string
    {
        var_dump($id);
        $query = <<< SQL
        SELECT
            hashed_password
        FROM
            users
        WHERE
            id = ?
        LIMIT 1
        SQL;

        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();

        $result = $stmt->get_result();
        
        $element = $result->fetch_object()->hashed_password;

        $stmt->close();

        return $element;
    }

    public function retrieveAll(?bool $loadPermissionGroups = false): array
    {
        $query = <<< SQL
        SELECT
            id,
            gov_id,
            first_name,
            last_name,
            birth_date,
            hashed_password,
            email_address,
            phone_number,
            representative_id,
            registration_date,
            last_login_date,
            token,
            status
        FROM
            users
        SQL;

        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();

        $elements = [];

        while ($mysqli_object = $result->fetch_object()) {
            $currentElement = User::constructFromMysqliObject($mysqli_object);

            if ($loadPermissionGroups)
                $currentElement->setPermissionGroups($this->retrievePermissionGroupsById($currentElement->getId()));

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

    /*
     *
     * Grupos de permisos
     * 
     */

    public function retrievePermissionGroupsById(int $userId): array
    {
        $query = <<< SQL
        SELECT
            permission_group_id
        FROM
            user_permission_group_links
        WHERE
            user_id = ?
        SQL;

        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $elements = [];

        $permissionGroupRepository = new PermissionGroupRepository($this->db);

        while($mysqli_object = $result->fetch_object()) {
            $elements[] = $permissionGroupRepository->retrieveById($mysqli_object->permission_group_id);
        }

        $stmt->close();

        return $elements;
    }

    /**
     * Crea un enlace entre un usuario y un grupo de permisos.
     * 
     * @requires Existe un usuario con el identificador especificado.
     * @requires Existe un grupo de permisos con el identificador especificado.
     * 
     * @param int $userId
     * @param int $permissionGroupId
     * 
     * @return bool Resultado de la operación.
     */
    public function createPermissionGroupLink(int $userId, int $permissionGroupId): bool
    {
        $query = <<< SQL
        INSERT INTO
            user_permission_group_links
            (
                user_id,
                permission_group_id
            )
        VALUES
            ( ?, ? )
        SQL;

        $stmt = $this->db->prepare($query);

        $stmt->bind_param(
            'ii',
            $userId,
            $permissionGroupId
        );
        
        $result = $stmt->execute();

        $stmt->close();

        return $result;
    }

    public function deletePermissionGroupLink(int $userId, int $permissionGroupId): bool
    {
        $query = <<< SQL
        DELETE FROM
            user_permission_group_links
        WHERE
            user_id = ?
        AND
            permission_group_id = ?
        SQL;

        $stmt = $this->db->prepare($query);

        $stmt->bind_param(
            'ii',
            $userId,
            $permissionGroupId
        );
        
        $result = $stmt->execute();

        $stmt->close();

        return $result;
    }

    public function createPermissionGroupLinks(int $userId, array $permissionGroups): void
    {
        foreach ($permissionGroups as $permissionGroup)
            $this->createPermissionGroupLink($userId, $permissionGroup->getId());
    }

    public function createPermissionGroupLinksWithIds(int $userId, array $permissionGroupIds): void
    {
        foreach ($permissionGroupIds as $permissionGroupId)
            $this->createPermissionGroupLink($userId, $permissionGroupId);
    }

    public function deletePermissionGroupLinksWithIds(int $userId, array $permissionGroupIds): void
    {
        foreach ($permissionGroupIds as $permissionGroupId)
            $this->createPermissionGroupLink($userId, $permissionGroupId);
    }

    public function updatePermissionGroupLinks(int $userId, array $permissionGroups): void
    {
        $ids = [];

        foreach ($permissionGroups as $permissionGroup) {
            $ids[] = $permissionGroup->getId();
        }

        $this->updatePermissionGroupLinksWithIds($userId, $ids);
    }

    public function updatePermissionGroupLinksWithIds(int $userId, array $permissionGroupIds): void
    {
        $currentPermissionGroups = $this->retrievePermissionGroupsById($userId);

        // Permission groups to add
        // $newPermissionGroups - $currentPermissionGroups

        foreach ($permissionGroupIds as $newPermissionGroupId) {
            $inArray = false;

            foreach ($currentPermissionGroups as $currentPermissionGroup)
                if ($currentPermissionGroup->getId() == $newPermissionGroupId)
                    $inArray = true;

            if (! $inArray)
                $this->createPermissionGroupLink($userId, $newPermissionGroupId);
        }

        // Permission groups to delete
        // $currentPermissionGroups - $newPermissionGroups

        foreach ($currentPermissionGroups as $currentPermissionGroup) {
            $inArray = false;

            foreach ($permissionGroupIds as $newPermissionGroupId)
                if ($newPermissionGroupId == $currentPermissionGroup->getId())
                    $inArray = true;

            if (! $inArray)
                $this->deletePermissionGroupLink($userId, $currentPermissionGroup->getId());
        }
    }
}