<?php

namespace Juancrrn\Lyra\Common\Controller;

use Exception;
use Juancrrn\Lyra\Common\Api\BookBank\Common\SubjectSearchApi;
use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\Controller\Controller;
use Juancrrn\Lyra\Common\Controller\RouteGroupModel;
use Juancrrn\Lyra\Common\View\BookBank\Manager\DonationCreateView;
use Juancrrn\Lyra\Common\View\BookBank\Manager\DonationEditView;
use Juancrrn\Lyra\Common\View\BookBank\Manager\RequestAndLotEditView;
use Juancrrn\Lyra\Common\View\BookBank\Manager\StudentOverviewView;
use Juancrrn\Lyra\Common\View\BookBank\Manager\StudentSearchView;

/**
 * Vistas de usuarios con permisos de gestor del banco de libros
 * (BookBankManager)
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class BookBankManagerRouteGroup implements RouteGroupModel
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
        
        /*
         *
         * Subjects
         * 
         */
        
        // Subject list
        $this->controllerInstance->get('/bookbank/manage/subjects/', function () use ($viewManager) {
            throw new Exception('Route declared but not implemented.');
        });
        
        // Subject AJAX search
        $this->controllerInstance->post(SubjectSearchApi::API_ROUTE, function () use ($apiManager) {
            $apiManager->call(new SubjectSearchApi);
        });
        
        /*
         *
         * Students' records manual management
         * 
         */
        
        // Student search
        $this->controllerInstance->get(StudentSearchView::VIEW_ROUTE, function () use ($viewManager) {
            $viewManager->render(new StudentSearchView);
        });
        
        // Student search (form POST)
        $this->controllerInstance->post(StudentSearchView::VIEW_ROUTE, function () use ($viewManager) {
            $viewManager->render(new StudentSearchView);
        });
        
        // Student overview
        $this->controllerInstance->get(StudentOverviewView::VIEW_ROUTE, function (int $itemId) use ($viewManager) {
            $viewManager->render(new StudentOverviewView($itemId));
        });
        
        // Donation creation
        $this->controllerInstance->get(DonationCreateView::VIEW_ROUTE, function (int $studentId) use ($viewManager) {
            $viewManager->render(new DonationCreateView($studentId));
        });
        
        // Donation creation (form POST)
        $this->controllerInstance->post(DonationCreateView::VIEW_ROUTE, function (int $studentId) use ($viewManager) {
            $viewManager->render(new DonationCreateView($studentId));
        });
        
        // Donation edition
        $this->controllerInstance->get(DonationEditView::VIEW_ROUTE, function (int $itemId) use ($viewManager) {
            $viewManager->render(new DonationEditView($itemId));
        });
        
        // Donation edition (form POST)
        $this->controllerInstance->post(DonationEditView::VIEW_ROUTE, function (int $itemId) use ($viewManager) {
            $viewManager->render(new DonationEditView($itemId));
        });
        
        // Request edition
        $this->controllerInstance->get(RequestAndLotEditView::VIEW_ROUTE, function (int $itemId) use ($viewManager) {
            $viewManager->render(new RequestAndLotEditView($itemId));
        });
        
        // Request edition (form POST)
        $this->controllerInstance->post(RequestAndLotEditView::VIEW_ROUTE, function (int $itemId) use ($viewManager) {
            $viewManager->render(new RequestAndLotEditView($itemId));
        });
    }
}