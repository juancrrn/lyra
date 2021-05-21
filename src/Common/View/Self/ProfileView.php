<?php

namespace Juancrrn\Lyra\Common\View\Self;

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\CommonUtils;
use Juancrrn\Lyra\Common\View\ViewModel;
use Juancrrn\Lyra\Domain\User\User;
use Juancrrn\Lyra\Domain\User\UserRepository;

/**
 * Vista de perfil
 *
 * @package lyra
 * 
 * @author juancrrn
 *
 * @version 0.0.1
 */

class ProfileView extends ViewModel
{
    private const VIEW_RESOURCE_FILE    = 'views/self/view_profile';
    public  const VIEW_NOMBRE           = 'Perfil';
    public  const VIEW_ID               = 'self-profile';
    public  const VIEW_ROUTE            = '/self/profile/';

    private $user;

    public function __construct()
    {
        $sessionManager = App::getSingleton()->getSessionManagerInstance();

        $sessionManager->requireLoggedIn();

        $this->name = self::VIEW_NOMBRE;
        $this->id = self::VIEW_ID;

        $this->user = $sessionManager->getLoggedInUser();
    }

    public function processContent(): void
    {
        $app = App::getSingleton();

        if ($this->user->getRepresentativeId() == null) {
            $userRepresentativeHuman = '(No definido)';
        } else {
            $userRepository = new UserRepository($app->getDbConn());
            $representative = $userRepository
                ->retrieveById($this->user->getRepresentativeId());
            $userRepresentativeHuman = $representative->getFullName();
        }

        $permissionGroupItemsHtml = '';

        $viewManager = $app->getViewManagerInstance();

        foreach ($this->user->getPermissionGroups() as $group) {
            $permissionGroupItemsHtml .= $viewManager->fillTemplate(
                'views/self/view_profile_part_permission_group_item',
                array(
                    'full-name' => $group->getFullName(),
                    'description' => $group->getDescription()
                )
            );
        }

        $filling = array(
            'app-name' => $app->getName(),
            'user-image' => $app->getUrl() . '/img/default-user-image.png',
            'user-full-name' => $this->user->getFullName(),
            'user-gov-id' => mb_strtoupper($this->user->getGovId()), // TODO añadir a los que no tienen NIF o NIE
            'user-email-address' => $this->user->getEmailAddress(),
            'user-phone-number' => $this->user->getPhoneNumber(),
            'user-birth-date-human' => strftime(
                CommonUtils::HUMAN_DATE_FORMAT_STRF,
                $this->user->getBirthDate()->getTimestamp()
            ),
            'user-last-login-date-human' => strftime(
                CommonUtils::HUMAN_DATETIME_FORMAT_STRF,
                $this->user->getLastLoginDate()->getTimestamp()
            ),
            'user-status-human' => User::statusToHuman(
                $this->user->getStatus()
            )->getTitle(),
            'user-representative-human' => $userRepresentativeHuman,
            'user-registration-date-human' => strftime(
                CommonUtils::HUMAN_DATETIME_FORMAT_STRF,
                $this->user->getRegistrationDate()->getTimestamp()
            ),
            'user-permission-group-list-human' => $permissionGroupItemsHtml
        );

        $app->getViewManagerInstance()->renderTemplate(self::VIEW_RESOURCE_FILE, $filling);
    }
}