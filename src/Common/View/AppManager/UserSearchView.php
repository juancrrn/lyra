<?php

namespace Juancrrn\Lyra\Common\View\AppManager;

use Juancrrn\Lyra\Common\Api\AppManager\UserSearchApi;
use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\View\ViewModel;
use Juancrrn\Lyra\Domain\User\User;

/**
 * App settings overview view
 *
 * @package lyra
 * 
 * @author juancrrn
 *
 * @version 0.0.1
 */

class UserSearchView extends ViewModel
{
    private const VIEW_RESOURCE_FILE    = 'views/app_manager/view_user_search';
    public  const VIEW_NAME             = 'Gestión de usuarios';
    public  const VIEW_ID               = 'app-manager-user-search';
    public  const VIEW_ROUTE            = '/manage/users/';

    /*private $form;*/

    public function __construct()
    {
        $app = App::getSingleton();

        $sessionManager = $app->getSessionManagerInstance();

        $sessionManager->requirePermissionGroups([ User::NPG_APP_MANAGER ]);

        /*$this->form = new AppSettingsEditForm(self::VIEW_ROUTE);

        $appSettingRepository = new AppSettingRepository($app->getDbConn());

        $this->form->handle();

        $appSettings = $appSettingRepository->retrieveAll();

        $this->form->initialize($appSettings);*/

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
            'user-search-form-html' => $viewManager->fillTemplate(
                'ajax-forms/common/part_user_search_form',
                [
                    'query-url' => $app->getUrl() . UserSearchApi::API_ROUTE,
                    'target-url' => $app->getUrl() . 'TODO' . '{id}/overview/'
                ]
            )
        ];
        
        $viewManager->renderTemplate(self::VIEW_RESOURCE_FILE, $filling);
    }
}