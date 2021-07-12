<?php

namespace Juancrrn\Lyra\Domain\StaticForm\AppManager;

use DateTime;
use Juancrrn\Lyra\Common\Api\AppManager\PermissionGroupsApi;
use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\CommonUtils;
use Juancrrn\Lyra\Common\TemplateUtils;
use Juancrrn\Lyra\Common\ValidationUtils;
use Juancrrn\Lyra\Domain\PermissionGroup\PermissionGroupRepository;
use Juancrrn\Lyra\Domain\StaticForm\StaticFormModel;
use Juancrrn\Lyra\Domain\User\User;
use Juancrrn\Lyra\Domain\User\UserRepository;

/**
 * User edit form
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class UserEditForm extends StaticFormModel
{

    private const FORM_ID = 'form-app-manager-user-edit';
    private const FORM_FIELDS_NAME_PREFIX = 'app-manager-user-edit-form-';

    private $user;

    public function __construct(string $action, int $userId)
    {
        parent::__construct(self::FORM_ID, [ 'action' => $action ]);

        $app = App::getSingleton();

        $userRepo = new UserRepository($app->getDbConn());

        $this->user = $userRepo->retrieveById($userId, true);
    }
    
    protected function generateFields(array & $preloadedData = []): string
    {
        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        $userRepo = new UserRepository($app->getDbConn());

        if ($this->user->getRepresentativeId() != null) {
            $representativeGovId = mb_strtoupper($userRepo->retrieveById($this->user->getRepresentativeId())->getGovId());
        } else {
            $representativeGovId = '';
        }

        $lastLoginDate =
            $this->user->getLastLoginDate() == null ? 'Nunca' :
            strftime(
                CommonUtils::HUMAN_DATETIME_FORMAT_STRF,
                $this->user->getLastLoginDate()->getTimestamp()
            );

        return $viewManager->fillTemplate(
            'forms/app_manager/inputs_user_edit_form',
            [
                'app-name'      => $app->getName(),
                'image-url'     => $app->getUrl() . '/img/default-user-image.png',
                'prefix'        => self::FORM_FIELDS_NAME_PREFIX,
                'id'            => $this->user->getId(),
                'fist-name'     => $this->user->getFirstName(),
                'last-name'     => $this->user->getLastName(),
                'gov-id'        => mb_strtoupper($this->user->getGovId()),
                'query-url'     => $app->getUrl() . PermissionGroupsApi::API_ROUTE,
                'checkbox-name' => self::FORM_FIELDS_NAME_PREFIX . 'permission-groups',
                'permission-group-list' => $this->generatePermissionGroupsAndTemplates(),
                'email-address' => $this->user->getEmailAddress(),
                'phone-number'  => $this->user->getPhoneNumber(),
                'birth-date'    => $this->user->getBirthDate()->format(CommonUtils::MYSQL_DATE_FORMAT),
                'last-login-date' => $lastLoginDate,
                'status-options' => TemplateUtils::generateSelectOptions(
                    User::getStatusesForSelectOptions(),
                    $this->user->getStatus()
                ),
                'password-reset-btn-target-url' => 'TEST_TODO', // TODO
                'representative-gov-id' => $representativeGovId,
                'registration-date' => strftime(
                    CommonUtils::HUMAN_DATETIME_FORMAT_STRF,
                    $this->user->getRegistrationDate()->getTimestamp()
                ),
            ]
        );
    }
    
    protected function process(array & $postedData): void
    {
        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        $newFirstName = $postedData[self::FORM_FIELDS_NAME_PREFIX . 'first-name'] ?? null;

        if (empty($newFirstName)) {
            $viewManager->addErrorMessage('El campo de nombre no puede estar vacío.');
        }

        $newLastName = $postedData[self::FORM_FIELDS_NAME_PREFIX . 'last-name'] ?? null;

        if (empty($newLastName)) {
            $viewManager->addErrorMessage('El campo de apellidos no puede estar vacío.');
        }

        $newGovId = mb_strtolower($postedData[self::FORM_FIELDS_NAME_PREFIX . 'gov-id']) ?? null;

        $userRepo = new UserRepository($app->getDbConn());

        if (! empty($newGovId) && ($newGovId != mb_strtolower($this->user->getGovId()))) {
            if (ValidationUtils::validateGovId($newGovId)) {
                if ($userRepo->findByGovId($newGovId)) {
                    $viewManager->addErrorMessage('Ya existe un usuario registrado con el NIF o NIE especificado.');
                }
            } else {
                $viewManager->addErrorMessage('El NIF o NIE introducido no es válido.');
            }
        } else {
            $newGovId = null;
        }

        $newEmailAddress = $postedData[self::FORM_FIELDS_NAME_PREFIX . 'email-address'] ?? null;

        if (empty($newEmailAddress)) {
            $viewManager->addErrorMessage('El campo de correo electrónico no puede estar vacío.');
        }

        $newPhoneNumber = $postedData[self::FORM_FIELDS_NAME_PREFIX . 'phone-number'] ?? null;

        if (empty($newPhoneNumber)) {
            $viewManager->addErrorMessage('El campo de número de teléfono no puede estar vacío.');
        }

        $newBirthDate = $postedData[self::FORM_FIELDS_NAME_PREFIX . 'birth-date'] ?? null;

        if (empty($newBirthDate)) {
            $viewManager->addErrorMessage('El campo de fecha de nacimiento no puede estar vacío.');
        } else {
            $newBirthDate = DateTime::createFromFormat(
                CommonUtils::MYSQL_DATE_FORMAT,
                $newBirthDate
            );

            if (! $newBirthDate) {
                $viewManager->addErrorMessage('El campo de fecha de nacimiento debe contener una fecha válida. Por favor, utiliza el formato AAAA-MM-DD.');
            }
        }

        $newStatus = $postedData[self::FORM_FIELDS_NAME_PREFIX . 'status'] ?? null;

        if (! User::validStatus($newStatus)) {
            $viewManager->addErrorMessage('Hubo un error al procesar el campo de estado.');
        }

        $newRepresentativeGovId = $postedData[self::FORM_FIELDS_NAME_PREFIX . 'representative-gov-id'] ?? null;
        $newRepresentativeGovId = $newRepresentativeGovId != '' ? $newRepresentativeGovId : null;

        if ($newRepresentativeGovId != null) {
            $newRepresentativeId = $userRepo->findByGovId($newRepresentativeGovId);

            if (! $newRepresentativeId) {
                $viewManager->addErrorMessage('El NIF o NIE de representante legal introducido no existe.');
                $newRepresentativeId = null;
            } else {
                $newRepresentative = $userRepo->retrieveById($newRepresentativeId);

                if (! $newRepresentative->hasPermission(User::NPG_LEGALREP)) {
                    $viewManager->addErrorMessage('El representante legal cuyo NIF o NIE se ha introducido no tiene permisos para serlo.');
                    $newRepresentativeId = null;
                }
            }
        } else {
            $newRepresentativeId = null;
        }

        $newPermissionGroupIds = $postedData[self::FORM_FIELDS_NAME_PREFIX . 'permission-groups'] ?? null;

        $permissionGroupRepo = new PermissionGroupRepository($app->getDbConn());

        if (empty($newPermissionGroupIds)) {
            $viewManager->addErrorMessage('La lista de grupos de permisos no puede quedar vacía.');
        } else {
            foreach ($newPermissionGroupIds as $newPermissionGroupId) {
                if (! $permissionGroupRepo->findById($newPermissionGroupId)) {
                    $viewManager->addErrorMessage('Hubo un error al procesar un grupo de permisos.');
                }
            }
        }

        if (! $viewManager->anyErrorMessages()) {
            $updatedUser = new User(
                $this->user->getId(),
                $newGovId,
                $newFirstName,
                $newLastName,
                $newBirthDate,
                $newEmailAddress,
                $newPhoneNumber,
                $newRepresentativeId,
                null,
                null,
                null,
                $newStatus,
                null
            );

            $userRepo->update($updatedUser, $newPermissionGroupIds);

            $viewManager->addSuccessMessage('El usuario fue actualizado correctamente.');
        }
    }

    private function generatePermissionGroupsAndTemplates(): string
    {
        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        // Add content list item template to HTML (available to AJAX)

        $viewManager->addTemplateElement(
            'app-manager-user-part-permission-group-list-item-editable',
            'app_manager/view_user_part_permission_group_list_item_editable',
            [
                'checkbox-name' => self::FORM_FIELDS_NAME_PREFIX . 'permission-groups',
                'id' => '',
                'full-name' => '',
                'description' => ''
            ]
        );
        
        $viewManager->addTemplateElement(
            'app-manager-user-part-permission-group-list-item-empty',
            'app_manager/view_user_part_permission_group_list_item_empty',
            []
        );

        // Add search list item template to HTML (available to AJAX)

        $viewManager->addTemplateElement(
            'app-manager-user-part-permission-group-search-item',
            'app_manager/view_user_part_permission_group_search_item',
            [
                'id' => '',
                'full-name' => '',
                'description' => ''
            ]
        );
        
        $viewManager->addTemplateElement(
            'app-manager-user-part-permission-group-search-item-empty',
            'app_manager/view_user_part_permission_group_search_item_empty',
            []
        );

        // Initial contents
        
        $html = '';

        if (! empty($this->user->getPermissionGroups())) {
            foreach ($this->user->getPermissionGroups() as $permissionGroup) {
                $html .= $viewManager->fillTemplate(
                    'html_templates/app_manager/view_user_part_permission_group_list_item_editable',
                    [
                        'checkbox-name' => self::FORM_FIELDS_NAME_PREFIX . 'permission-groups',
                        'id' => $permissionGroup->getId(),
                        'full-name' => $permissionGroup->getFullName(),
                        'description' => $permissionGroup->getDescription()
                    ]
                );
            }
        } else {
            $html = $viewManager->fillTemplate(
                'html_templates/app_manager/view_user_part_permission_group_list_item_empty',
                []
            );
        }

        return $html;
    }
}