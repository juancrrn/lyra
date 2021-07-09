<?php

namespace Juancrrn\Lyra\Common\Api\BookBank\Volunteer;

use Juancrrn\Lyra\Common\Api\ApiModel;
use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Domain\User\User;
use Juancrrn\Lyra\Domain\User\UserRepository;

class StudentSearchApi extends ApiModel
{

    public const API_ROUTE = '/api/bookbank/volunteer/students/search/';

    public function consume(?object $requestContent): void
    {
        $app = App::getSingleton();

        $sessionManager = $app->getSessionManagerInstance();

        $sessionManager->requirePermissionGroups([ User::NPG_BOOKBANK_VOLUNTEER ]);

        $apiManager = $app->getApiManagerInstance();

        $userRepository = new UserRepository($app->getDbConn()); 

        $searchResult = $userRepository->searchStudents($requestContent->query, true);

        $apiManager->apiRespond(
            200,
            $searchResult
        );
    }
}