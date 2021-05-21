<?php

/**
 * Formulario de solicitud de restablecimiento de la contraseña
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

namespace Juancrrn\Lyra\Domain\StaticForm\Auth;

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Domain\Email\EmailUtils;
use Juancrrn\Lyra\Domain\StaticForm\StaticFormModel;
use Juancrrn\Lyra\Domain\User\User;
use Juancrrn\Lyra\Domain\User\UserRepository;
use RuntimeException;

class PasswordResetRequestForm extends StaticFormModel
{

    private const FORM_ID = 'form-password-reset-request';

    public function __construct(string $action)
    {
        parent::__construct(self::FORM_ID, [ 'action' => $action ]);
    }
    
    protected function generateFields(array & $preloadedData = []): string
    {
        $govId = '';

        if (! empty($preloadedData)) {
            $govId = isset($preloadedData['gov_id']) ? $preloadedData['gov_id'] : $govId;
        }

        return App::getSingleton()->getViewManagerInstance()->fillTemplate(
            'forms/auth/inputs_password_reset_request_form',
            [
                'gov_id' => $govId
            ]
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
            $view->addSuccessMessage('Si el NIF o NIE introducido está registrado en nuestros sistemas, recibirás un mensaje de correo electrónico con un mensaje para restablecer tu contraseña.');
            $view->addSuccessMessage('Por favor, comprueba tu bandeja de correo no deseado o spam. Si no encuentras el mensaje, puedes contactar con nosotros.', '/auth/reset/request');
        }

        // Si no hay ningún error, continuar.
        if (! $view->anyErrorMessages()) {
            $userId = $userRepository->findByGovId($govId);

            $token = $userRepository->generateAndUpdateTokenAndStatusById($userId);

            if ($token) {
                $user = $userRepository->retrieveById($userId);

                $emailResult = false;

                if ($user->getStatus() == User::STATUS_RESET) {
                    // Enviar email de restablecimiento.
                    $emailResult = EmailUtils::sendUserPasswordResetMessage($user);
                } elseif ($user->getStatus() == User::STATUS_INACTIVE) {
                    // Enviar email de activcación.
                    $emailResult = EmailUtils::sendUserActivationMessage($user);
                } else {
                    throw new RuntimeException('Invalid user status.');
                }

                if ($emailResult) {
                    $view->addSuccessMessage('Si el NIF o NIE introducido está registrado en nuestros sistemas, recibirás un mensaje de correo electrónico con un mensaje para restablecer tu contraseña.');
                    $view->addSuccessMessage('Por favor, comprueba tu bandeja de correo no deseado o spam. Si no encuentras el mensaje, puedes contactar con nosotros.');
                }
            } else {
                $view->addErrorMessage('Hubo un error al generar el enlace de recuperación. Por favor, vuelve a intentarlo.');
            }
        }

        $this->initialize();
    }
}