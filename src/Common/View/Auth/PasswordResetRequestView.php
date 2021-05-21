<?php

namespace Juancrrn\Lyra\Common\View\Auth;

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\View\ViewModel;
use Juancrrn\Lyra\Domain\StaticForm\Auth\LoginForm;
use Juancrrn\Lyra\Domain\StaticForm\Auth\PasswordResetRequestForm;

/**
 * Vista de solicitud de restablecimiento de contraseña
 *
 * @package lyra
 * 
 * @author juancrrn
 *
 * @version 0.0.1
 */

class PasswordResetRequestView extends ViewModel
{
    private const VIEW_RESOURCE_FILE    = 'auth/view_password_reset_request';
    public  const VIEW_NOMBRE           = 'Solicitar restablecimiento de contraseña';
    public  const VIEW_ID               = 'auth-password-reset-request';
    public  const VIEW_ROUTE            = '/auth/reset/request/';

    private $form;

    public function __construct()
    {
        App::getSingleton()->getSessionManagerInstance()->requireNotLoggedIn();

        $this->name = self::VIEW_NOMBRE;
        $this->id = self::VIEW_ID;

        $this->form = new PasswordResetRequestForm('/auth/reset/request/'); 

        $this->form->handle();
        $this->form->initialize();
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