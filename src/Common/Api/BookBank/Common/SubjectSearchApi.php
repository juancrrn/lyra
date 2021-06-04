<?php

namespace Juancrrn\Lyra\Common\Api\BookBank\Common;

use Juancrrn\Lyra\Common\Api\ApiModel;
use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Domain\BookBank\Subject\SubjectRepository;

class SubjectSearchApi extends ApiModel
{

    public const API_ROUTE = '/bookbank/manage/subjects/search/';

    public function consume(object $requestContent): void
    {
        $app = App::getSingleton();

        $apiManager = $app->getApiManagerInstance();

        $subjectRepository = new SubjectRepository($app->getDbConn()); 

        $searchResult = $subjectRepository->search($requestContent->query, true);

        $apiManager->apiRespond(
            200,
            $searchResult
        );
    }
}