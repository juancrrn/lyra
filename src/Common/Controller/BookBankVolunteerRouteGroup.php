<?php

namespace Juancrrn\Lyra\Common\Controller;

use Exception;
use Juancrrn\Lyra\Common\Api\BookBank\Volunteer\StudentSearchApi;
use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\Controller\Controller;
use Juancrrn\Lyra\Common\Controller\RouteGroupModel;
use Juancrrn\Lyra\Common\View\BookBank\Volunteer\CheckInAssistantDonationLiteView;
use Juancrrn\Lyra\Common\View\BookBank\Volunteer\CheckInAssistantPickupLiteView;
use Juancrrn\Lyra\Common\View\BookBank\Volunteer\CheckInAssistantRequestLiteView;
use Juancrrn\Lyra\Common\View\BookBank\Volunteer\CheckInAssistantReturnLiteView;
use Juancrrn\Lyra\Common\View\BookBank\Volunteer\CheckInAssistantStudentOverviewView;
use Juancrrn\Lyra\Common\View\BookBank\Volunteer\CheckInAssistantStudentSearchView;
use Juancrrn\Lyra\Common\View\BookBank\Volunteer\LotFillingAssistantHomeView;
use Juancrrn\Lyra\Common\View\BookBank\Volunteer\LotFillingAssistantRunView;
use Juancrrn\Lyra\Common\View\TimePlanner\Volunteer\AppointmentListView;

/**
 * Vistas de usuarios con permisos de voluntario del banco de libros
 * (BookBankVolunteer)
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class BookBankVolunteerRouteGroup implements RouteGroupModel
{

    private $controllerInstance;

    public function __construct(Controller $controllerInstance)
    {
        $this->controllerInstance = $controllerInstance;
    }

    public function runAll(): void
    {
        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        $apiManager = $app->getApiManagerInstance();
        
        // Student search API
        $this->controllerInstance->post(StudentSearchApi::API_ROUTE, function () use ($apiManager) {
            $apiManager->call(new StudentSearchApi);
        });
        
        /*
         *
         * Asistente de creación de usuarios (estudiante y representante legal)
         * 
         */
        
        /*
         *
         * Time planner (reserved appointments)
         * 
         */

        $this->controllerInstance->get(AppointmentListView::VIEW_ROUTE, function () use ($viewManager) {
            $viewManager->render(new AppointmentListView);
        });
        
        /*
         *
         * Check-in assistant
         * 
         */
        
        // Student search
        $this->controllerInstance->get(CheckInAssistantStudentSearchView::VIEW_ROUTE, function () use ($viewManager) {
            $viewManager->render(new CheckInAssistantStudentSearchView);
        });
        
        // Student overview
        $this->controllerInstance->get(CheckInAssistantStudentOverviewView::VIEW_ROUTE, function (int $itemId) use ($viewManager) {
            $viewManager->render(new CheckInAssistantStudentOverviewView($itemId));
        });
        
        // Donation lite view
        $this->controllerInstance->get(CheckInAssistantDonationLiteView::VIEW_ROUTE, function (int $studentId) use ($viewManager) {
            $viewManager->render(new CheckInAssistantDonationLiteView($studentId));
        });
        
        // Donation lite view form POST
        $this->controllerInstance->post(CheckInAssistantDonationLiteView::VIEW_ROUTE, function (int $studentId) use ($viewManager) {
            $viewManager->render(new CheckInAssistantDonationLiteView($studentId));
        });
        
        // Pickup lite view
        $this->controllerInstance->get(CheckInAssistantPickupLiteView::VIEW_ROUTE, function (int $studentId, int $requestId) use ($viewManager) {
            $viewManager->render(new CheckInAssistantPickupLiteView($studentId, $requestId));
        });
        
        // Pickup lite view form POST
        $this->controllerInstance->post(CheckInAssistantPickupLiteView::VIEW_ROUTE, function (int $studentId, int $requestId) use ($viewManager) {
            $viewManager->render(new CheckInAssistantPickupLiteView($studentId, $requestId));
        });
        
        // Request lite view
        $this->controllerInstance->get(CheckInAssistantRequestLiteView::VIEW_ROUTE, function (int $studentId) use ($viewManager) {
            $viewManager->render(new CheckInAssistantRequestLiteView($studentId));
        });
        
        // Request lite view form POST
        $this->controllerInstance->post(CheckInAssistantRequestLiteView::VIEW_ROUTE, function (int $studentId) use ($viewManager) {
            $viewManager->render(new CheckInAssistantRequestLiteView($studentId));
        });
        
        // Return lite view
        $this->controllerInstance->get(CheckInAssistantReturnLiteView::VIEW_ROUTE, function (int $studentId, int $requestId) use ($viewManager) {
            $viewManager->render(new CheckInAssistantReturnLiteView($studentId, $requestId));
        });
        
        // Return lite view form POST
        $this->controllerInstance->post(CheckInAssistantReturnLiteView::VIEW_ROUTE, function (int $studentId, int $requestId) use ($viewManager) {
            $viewManager->render(new CheckInAssistantReturnLiteView($studentId, $requestId));
        });
        
        /*
         *
         * Lot filling assistant
         * 
         */

        // Lot filling assistant home view
        $this->controllerInstance->get(LotFillingAssistantHomeView::VIEW_ROUTE, function () use ($viewManager) {
            $viewManager->render(new LotFillingAssistantHomeView);
        });

        // Lot filling assistant run view
        $this->controllerInstance->get(LotFillingAssistantRunView::VIEW_ROUTE, function () use ($viewManager) {
            $viewManager->render(new LotFillingAssistantRunView);
        });

        // Lot filling assistant run view form POST
        $this->controllerInstance->post(LotFillingAssistantRunView::VIEW_ROUTE, function () use ($viewManager) {
            $viewManager->render(new LotFillingAssistantRunView);
        });
    }
}