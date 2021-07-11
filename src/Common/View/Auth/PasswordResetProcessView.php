<?php

namespace Juancrrn\Lyra\Common\View\Auth;

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\ValidationUtils;
use Juancrrn\Lyra\Common\View\ViewModel;
use Juancrrn\Lyra\Domain\StaticForm\Auth\LoginForm;
use Juancrrn\Lyra\Domain\StaticForm\Auth\PasswordResetProcessForm;
use Juancrrn\Lyra\Domain\StaticForm\Auth\PasswordResetRequestForm;
use Juancrrn\Lyra\Domain\User\UserRepository;

/**
 * Vista de proceso de restablecimiento de contraseña
 *
 * @package lyra
 * 
 * @author juancrrn
 *
 * @version 0.0.1
 */

class PasswordResetProcessView extends ViewModel
{
    private const VIEW_RESOURCE_FILE    = 'views/auth/view_password_reset_process';
    public  const VIEW_NOMBRE           = 'Establecer contraseña';
    public  const VIEW_ID               = 'auth-password-reset-process';
    public  const VIEW_ROUTE            = '/auth/reset/process/([0-9a-zA-Z]*)';
    public  const VIEW_ROUTE_BASE       = '/auth/reset/process/';

    private $form;

    public function __construct(mixed $token)
    {
        $app = App::getSingleton();
        
        $app->getSessionManagerInstance()->requireNotLoggedIn();

        if (! ValidationUtils::validateToken($token)) {
            $app->getViewManagerInstance()
                ->addErrorMessage('El token introducido no es válido. Por favor, revísalo.');
            $app->getViewManagerInstance()
                ->addErrorMessage('Si sigues teniendo problemas, puedes contactar con nosotros.', '');
        }

        if (! (new UserRepository($app->getDbConn()))->findByToken($token)) {
            $app->getViewManagerInstance()
                ->addErrorMessage('El token introducido no es válido. Por favor, revísalo.');
            $app->getViewManagerInstance()
                ->addErrorMessage('Si sigues teniendo problemas, puedes contactar con nosotros.', '');
        }

        $this->name = self::VIEW_NOMBRE;
        $this->id = self::VIEW_ID;

        $this->form = new PasswordResetProcessForm('/auth/reset/process/' . $token, $token); 

        $this->form->handle();

        $preloadedData = [ 'token' => $token ];

        $this->form->initialize($preloadedData);
    }

    public function processContent(): void
    {
        $app = App::getSingleton();

        $filling = [
            'form-html' => $this->form->getHtml(),
            'login-url' => $app->getUrl() . '/auth/login/'
        ];

        $app->getViewManagerInstance()->renderTemplate(self::VIEW_RESOURCE_FILE, $filling);
    }
}