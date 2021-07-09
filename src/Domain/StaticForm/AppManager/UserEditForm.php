<?php

namespace Juancrrn\Lyra\Domain\StaticForm\AppManager;

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Domain\StaticForm\StaticFormModel;
use Juancrrn\Lyra\Domain\User\UserRepository;

/**
 * User edit form
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class UserEditForm extends StaticFormModel
{

    private const FORM_ID = 'form-app-manager-user-edit';
    private const FORM_FIELDS_NAME_PREFIX = 'app-manager-user-edit-form-';

    private $user;

    public function __construct(string $action, int $userId)
    {
        parent::__construct(self::FORM_ID, [ 'action' => $action ]);

        $app = App::getSingleton();

        $userRepo = new UserRepository($app->getDbConn());

        $this->user = $userRepo->retrieveById($userId);
    }
    
    protected function generateFields(array & $preloadedData = []): string
    {
        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        return $viewManager->fillTemplate(
            'forms/app_manager/inputs_user_edit_form',
            [
                
            ]
        );
    }
    
    protected function process(array & $postedData): void
    {
        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        //...
    }
}