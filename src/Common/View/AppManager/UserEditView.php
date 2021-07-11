<?php

namespace Juancrrn\Lyra\Common\View\AppManager;

use Juancrrn\Lyra\Common\Api\AppManager\UserSearchApi;
use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\View\ViewModel;
use Juancrrn\Lyra\Domain\StaticForm\AppManager\UserEditForm;
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

class UserEditView extends ViewModel
{
    private const VIEW_RESOURCE_FILE    = 'views/app_manager/view_user_edit';
    public  const VIEW_NAME             = 'Editar usuario';
    public  const VIEW_ID               = 'app-manager-user-edit';
    public  const VIEW_ROUTE_BASIC      = '/manage/users/';
    public  const VIEW_ROUTE            = self::VIEW_ROUTE_BASIC . '([0-9]+)/edit/';

    private $form;

    public function __construct(int $userId)
    {
        $app = App::getSingleton();

        $sessionManager = $app->getSessionManagerInstance();

        $sessionManager->requirePermissionGroups([ User::NPG_APP_MANAGER ]);

        $this->form = new UserEditForm(self::VIEW_ROUTE, $userId);

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