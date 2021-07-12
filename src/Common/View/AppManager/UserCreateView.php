<?php

namespace Juancrrn\Lyra\Common\View\AppManager;

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\View\ViewModel;
use Juancrrn\Lyra\Domain\StaticForm\AppManager\UserCreateForm;
use Juancrrn\Lyra\Domain\User\User;

/**
 * User create view
 *
 * @package lyra
 * 
 * @author juancrrn
 *
 * @version 0.0.1
 */

class UserCreateView extends ViewModel
{
    private const VIEW_RESOURCE_FILE    = 'views/app_manager/view_user_create';
    public  const VIEW_NAME             = 'Crear usuario';
    public  const VIEW_ID               = 'app-manager-user-create';
    public  const VIEW_ROUTE            = '/manage/users/create/';

    private $form;

    public function __construct()
    {
        $app = App::getSingleton();

        $sessionManager = $app->getSessionManagerInstance();

        $sessionManager->requirePermissionGroups([ User::NPG_APP_MANAGER ]);

        $this->form = new UserCreateForm(self::VIEW_ROUTE);

        $this->form->handle();

        $this->form->initialize();

        $this->name = self::VIEW_NAME;
        $this->id = self::VIEW_ID;
    }

    public function processContent(): void
    {
        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        $filling = [
            'view-name' => $this->getName(),
            'back-to-search-url' => $app->getUrl() . UserSearchView::VIEW_ROUTE,
            'user-edit-form-html' => $this->form->getHtml(),
        ];
        
        $viewManager->renderTemplate(self::VIEW_RESOURCE_FILE, $filling);
    }
}