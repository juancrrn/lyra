<?php

namespace Juancrrn\Lyra\Common\View\BookBank\Manager;

use Juancrrn\Lyra\Common\Api\BookBank\Volunteer\StudentSearchApi;
use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\View\ViewModel;
use Juancrrn\Lyra\Domain\User\User;

/**
 * Vista de resumen de banco de libros de un estudiante
 *
 * @package lyra
 * 
 * @author juancrrn
 *
 * @version 0.0.1
 */

class StudentSearchView extends ViewModel
{
    private const VIEW_RESOURCE_FILE    = 'views/bookbank/manager/view_student_search';
    public  const VIEW_NAME             = 'Gestión de movimientos';
    public  const VIEW_ID               = 'bookbank-manage-student-search';
    public  const VIEW_ROUTE            = '/bookbank/manage/students/';

    public function __construct()
    {
        $app = App::getSingleton();

        $sessionManager = $app->getSessionManagerInstance();

        $sessionManager->requirePermissionGroups([ User::NPG_BOOKBANK_MANAGER ]);

        $this->name = self::VIEW_NAME;
        $this->id = self::VIEW_ID;
    }

    public function processContent(): void
    {
        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        $viewManager->addTemplateElement(
            'common-user-search-form-results-item',
            'common/template_user_search_form_results_item',
            []
        );

        $viewManager->addTemplateElement(
            'common-user-search-form-results-item-empty',
            'common/template_user_search_form_results_item_empty',
            []
        );

        $filling = [
            'view-name' => $this->getName(),
            'student-search-form-html' => $viewManager->fillTemplate(
                'ajax-forms/common/part_user_search_form',
                [
                    'query-url' => $app->getUrl() . StudentSearchApi::API_ROUTE,
                    'target-url' => $app->getUrl() . StudentOverviewView::VIEW_ROUTE_BASIC . '{id}/overview/'
                ]
            )
        ];

        $viewManager->renderTemplate(self::VIEW_RESOURCE_FILE, $filling);
    }
}