<?php

namespace Juancrrn\Lyra\Common\Api\AppManager;

use Juancrrn\Lyra\Common\Api\ApiModel;
use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Domain\PermissionGroup\PermissionGroupRepository;
use Juancrrn\Lyra\Domain\User\User;

class PermissionGroupsApi extends ApiModel
{

    public const API_ROUTE = '/api/manage/permission-groups/';

    public function consume(?object $requestContent): void
    {
        $app = App::getSingleton();

        $sessionManager = $app->getSessionManagerInstance();

        $sessionManager->requirePermissionGroups([ User::NPG_APP_MANAGER ]);

        $apiManager = $app->getApiManagerInstance();

        $permissionGroupRepo = new PermissionGroupRepository($app->getDbConn());

        $apiManager->apiRespond(
            200,
            $permissionGroupRepo->retrieveAll()
        );
    }
}