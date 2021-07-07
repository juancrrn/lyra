<?php

namespace Juancrrn\Lyra\Common\Api\TimePlanner\Slot;

use Juancrrn\Lyra\Common\Api\ApiModel;
use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Domain\TimePlanner\Slot\SlotRepository;

class AvailableDatesApi extends ApiModel
{

    public const API_ROUTE = '/api/timeplanner/slots/available-dates/';

    public function consume(?object $requestContent): void
    {
        $app = App::getSingleton();

        // No session requirements

        $apiManager = $app->getApiManagerInstance();

        $slotRepo = new SlotRepository($app->getDbConn());

        $apiManager->apiRespond(
            200,
            $slotRepo->retrieveAvailableSlotDates()
        );
    }
}