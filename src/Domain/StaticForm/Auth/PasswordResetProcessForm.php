<?php

namespace Juancrrn\Lyra\Domain\StaticForm\Auth;

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\ValidationUtils;
use Juancrrn\Lyra\Domain\Email\EmailUtils;
use Juancrrn\Lyra\Domain\StaticForm\StaticFormModel;
use Juancrrn\Lyra\Domain\User\User;
use Juancrrn\Lyra\Domain\User\UserRepository;
use RuntimeException;

/**
 * Formulario de proceso de restablecimiento de la contraseña
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class PasswordResetProcessForm extends StaticFormModel
{

    private const FORM_ID = 'form-password-reset-process';

    private $token;

    public function __construct(string $action, string $token)
    {
        parent::__construct(self::FORM_ID, [ 'action' => $action ]);

        $this->token = $token;
    }
    
    protected function generateFields(array & $preloadedData = []): string
    {
        $token = $preloadedData['token'];

        $app = App::getSingleton();

        return $app->getViewManagerInstance()->fillTemplate(
            'forms/auth/inputs_password_reset_process_form',
            [
                'token' => $token,
                'app-name' => $app->getName()
            ]
        );
    }
    
    protected function process(array & $postedData): void
    {
        $app = App::getSingleton();
        $view = $app->getViewManagerInstance();

        $urlToken = $this->token; // Ya validado en la vista
        $formToken = isset($postedData['token']) ? $postedData['token'] : null;

        // Errores de consistencia o integridad
        if (empty($formToken)) {
            $view->addErrorMessage('Hubo un error al validar el token. Por favor, vuelve a intentarlo.');
        } elseif ($formToken != $urlToken) {
            $view->addErrorMessage('Hubo un error al validar el token. Por favor, vuelve a intentarlo.');
        }

        $newPassword = isset($postedData['new_password']) ? $postedData['new_password'] : null;

        if (empty($newPassword)) {
            $view->addErrorMessage('El campo de Nueva contraseña no puede estar vacío.');
        }

        $newPasswordRepeat = isset($postedData['new_password_repeat']) ? $postedData['new_password_repeat'] : null;

        if (empty($newPasswordRepeat)) {
            $view->addErrorMessage('El campo de Repetir nueva contraseña no puede estar vacío.');
        }

        if (! empty($newPassword) && ! empty($newPasswordRepeat)) {
            if ($newPassword != $newPasswordRepeat) {
                $view->addErrorMessage('El campo de Repetir nueva contraseña debe coincidir con el de Nueva contraseña.');
            } else {
                if (! ValidationUtils::validatePassword($newPassword)) {
                    $view->addErrorMessage('La contraseña introducida no es válida.');
                    $view->addErrorMessage('Escoge una contraseña de al menos 12 caracteres y utiliza, como mínimo, una mayúscula, una minúscula y un número.');
                }
            }
        }

        // Si no hay ningún error, continuar.
        if (! $view->anyErrorMessages()) {
            $userRepository = new UserRepository($app->getDbConn());

            $userId = $userRepository->findByToken($urlToken);
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            $result = $userRepository->finalizeTokenProcessById($userId, $hashedPassword);

            if ($result) {
                $view->addSuccessMessage('Tu nueva contraseña se estableció correctamente.');
                $view->addSuccessMessage('Ya puedes acceder a tu cuenta con tu NIF o NIE y tu contraseña.', '/auth/login/');
            } else {
                $view->addErrorMessage('Ocurrió un error al procesar la petición. Por favor, vuelve a intentarlo o contacta con nosotros.', '');
            }
        }

        //$this->initialize();
    }
}