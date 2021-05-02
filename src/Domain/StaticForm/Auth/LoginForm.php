<?php

/**
 * Formulario de inicio de sesión
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

namespace Juancrrn\Lyra\Domain\StaticForm\Auth;

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Domain\StaticForm\StaticFormModel;
use Juancrrn\Lyra\Domain\User\UserRepository;

class LoginForm extends StaticFormModel
{

    private const FORM_ID = 'form-login';

    public function __construct(string $action)
    {
        parent::__construct(self::FORM_ID, array('action' => $action));
    }
    
    protected function generateFields(array & $preloadedData = array()): string
    {
        $govId = '';

        if (! empty($preloadedData)) {
            $govId = isset($preloadedData['gov_id']) ? $preloadedData['gov_id'] : $govId;
        }

        return App::getSingleton()->getViewManagerInstance()->generateTemplateRender(
            'forms/auth/inputs_login_form',
            array(
                'gov_id' => $govId
            )
        );
    }
    
    protected function process(array & $postedData): void
    {
        $app = App::getSingleton();
        $view = $app->getViewManagerInstance();

        $userRepository = new UserRepository($app->getDbConn());
        
        $govId = isset($postedData['gov_id']) ? $postedData['gov_id'] : null;

        if (empty($govId)) {
            $view->addErrorMessage('El NIF o NIE no puede estar vacío.');
        } elseif (! $userRepository->findByGovId($govId)) {
            $view->addErrorMessage('El NIF o NIE y la contraseña introducidos no coinciden.');
        }
        
        $password = isset($postedData['password']) ? $postedData['password'] : null;
        
        if (empty($password)) {
            $view->addErrorMessage('La contraseña no puede estar vacía.');
        }

        // Si no hay ningún error, continuar.
        if (! $view->anyErrorMessages()) {
            $userId = $userRepository->findByGovId($govId);

            // Comprobar si la contraseña es correcta.
            if (! password_verify($password, $userRepository->retrieveJustHashedPasswordById($userId))) {
                $view->addErrorMessage('El NIF o NIE y la contraseña introducidos no coinciden.');
            } else {
                $sessionManager = $app->getSessionManagerInstance();

                $sessionManager->doLogIn($userRepository->retrieveById($userId, true));

                header("Location: " . $app->getUrl());
                die();
            }

        }

        $this->initialize();
    }
}