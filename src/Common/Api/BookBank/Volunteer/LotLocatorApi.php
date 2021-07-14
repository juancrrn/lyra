<?php

namespace Juancrrn\Lyra\Common\Api\BookBank\Volunteer;

use Juancrrn\Lyra\Common\Api\ApiModel;
use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Domain\BookBank\Request\RequestRepository;
use Juancrrn\Lyra\Domain\Pdf\PdfUtils;
use Juancrrn\Lyra\Domain\User\User;
use Juancrrn\Lyra\Domain\User\UserRepository;

class LotLocatorApi extends ApiModel
{

    public const API_ROUTE_BASE = '/api/bookbank/volunteer/requests/';
    public const API_ROUTE      = self::API_ROUTE_BASE . '([0-9]+)/locator/';

    public $request;

    public $student;

    public function __construct(int $requestId)
    {
        $app = App::getSingleton();

        $sessionManager = $app->getSessionManagerInstance();

        $sessionManager->requirePermissionGroups([ User::NPG_BOOKBANK_VOLUNTEER ], false, true);

        $apiManager = $app->getApiManagerInstance();

        $requestRepo = new RequestRepository($app->getDbConn());

        if (! $requestRepo->findById($requestId)) {
            $apiManager->apiRespond(
                400,
                null,
                [ 'El parámetro de identificador de la solicitud no es válido.' ]
            );
        }

        $this->request = $requestRepo->retrieveById($requestId);

        $userRepo = new UserRepository($app->getDbConn());

        $this->student = $userRepo->retrieveById($this->request->getStudentId());
    }

    public function consume(?object $requestContent): void
    {
        PdfUtils::renderLotLocatorPdf(
            $this->request,
            $this->student
        );
    }
}