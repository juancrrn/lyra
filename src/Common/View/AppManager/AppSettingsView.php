<?php

namespace Juancrrn\Lyra\Common\View\AppManager;

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\View\ViewModel;
use Juancrrn\Lyra\Domain\AppSetting\AppSettingRepository;
use Juancrrn\Lyra\Domain\StaticForm\AppManager\AppSettingsEditForm;
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

class AppSettingsView extends ViewModel
{
    private const VIEW_RESOURCE_FILE    = 'views/app_manager/view_app_settings';
    public  const VIEW_NAME             = 'Configuración';
    public  const VIEW_ID               = 'app-manager-app-settings';
    public  const VIEW_ROUTE            = '/manage/settings/';

    private $form;

    public function __construct()
    {
        $app = App::getSingleton();

        $sessionManager = $app->getSessionManagerInstance();

        $sessionManager->requirePermissionGroups([ User::NPG_APP_MANAGER ]);

        $this->form = new AppSettingsEditForm(self::VIEW_ROUTE);

        $appSettingRepository = new AppSettingRepository($app->getDbConn());

        $this->form->handle();

        $appSettings = $appSettingRepository->retrieveAll();

        $this->form->initialize($appSettings);

        $this->name = self::VIEW_NAME;
        $this->id = self::VIEW_ID;
    }

    public function processContent(): void
    {
        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        $filling = [
            'app-url' => $app->getUrl(),
            'view-name' => $this->getName(),
            'app-settings-edit-form-html' => $this->form->getHtml()
        ];
        
        $viewManager->renderTemplate(self::VIEW_RESOURCE_FILE, $filling);
    }
}