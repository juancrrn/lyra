<?php

namespace Juancrrn\Lyra\Common\Api\BookBank\Manager;

use Juancrrn\Lyra\Common\Api\ApiModel;
use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Domain\User\UserRepository;
use stdClass;

class StudentSearchApi extends ApiModel
{

    public function consume(object $requestContent): void
    {
        $app = App::getSingleton();

        $apiManager = $app->getApiManagerInstance();

        $userRepository = new UserRepository($app->getDbConn()); 

        $searchResult = $userRepository->search($requestContent->query, true);

        $apiManager->apiRespond(
            200,
            $searchResult
        );
    }
}