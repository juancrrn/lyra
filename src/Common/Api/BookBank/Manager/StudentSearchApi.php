<?php

namespace Juancrrn\Lyra\Common\Api\BookBank\Manager;

use Juancrrn\Lyra\Common\Api\ApiModel;
use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Domain\User\User;
use Juancrrn\Lyra\Domain\User\UserRepository;

class StudentSearchApi extends ApiModel
{

    public const API_ROUTE = '/api/bookbank/manage/students/search/';

    public function consume(?object $requestContent): void
    {
        $app = App::getSingleton();

        $sessionManager = $app->getSessionManagerInstance();

        $sessionManager->requirePermissionGroups([ User::NPG_BOOKBANK_MANAGER ]);

        $apiManager = $app->getApiManagerInstance();

        $userRepository = new UserRepository($app->getDbConn()); 

        $searchResult = $userRepository->search($requestContent->query, true);

        $apiManager->apiRespond(
            200,
            $searchResult
        );
    }
}