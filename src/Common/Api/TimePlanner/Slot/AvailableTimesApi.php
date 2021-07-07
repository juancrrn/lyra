<?php

namespace Juancrrn\Lyra\Common\Api\TimePlanner\Slot;

use DateTime;
use Juancrrn\Lyra\Common\Api\ApiModel;
use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\CommonUtils;
use Juancrrn\Lyra\Domain\TimePlanner\Slot\SlotRepository;

class AvailableTimesApi extends ApiModel
{

    public const API_ROUTE = '/api/timeplanner/slots/available-times/';

    public function consume(?object $requestContent): void
    {
        $app = App::getSingleton();

        // No session requirements

        $apiManager = $app->getApiManagerInstance();

        $slotRepo = new SlotRepository($app->getDbConn());

        if (isset($requestContent->query)) {
            $apiManager->apiRespond(
                200,
                $slotRepo->retrieveAvailableSlotTimesByDate(
                    DateTime::createFromFormat(
                        CommonUtils::MYSQL_DATE_FORMAT,
                        $requestContent->query
                    )
                )
            );
        } else {
            $apiManager->apiRespond(
                400,
                null
            );
        }
    }
}