<?php

namespace Juancrrn\Lyra\Domain\Email;

use DateTime;
use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\TemplateUtils;
use Juancrrn\Lyra\Common\ValidationUtils;
use Juancrrn\Lyra\Common\View\Auth\PasswordResetProcessView;
use Juancrrn\Lyra\Domain\User\User;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

/**
 * Utilidades de correo electrónico
 *
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class EmailUtils
{

    /**
     * Ruta relativa del directorio de plantillas HTML de los mensajes de email.
     */
    private const EMAIL_RESOURCES_PATH = 'resources/email';

	/**
	 * Valor de la cabecera X-Mailer.
	 */
	private const X_MAILER_HEADER_VALUE = 'Lyra by Juan Carrión (juancrrn/lyra)';

	/**
	 * Initializes the PHPMailer configuration with the application instance's 
	 * settings.
	 * 
	 * @return \PHPMailer
	 */
	public static function initialize()
	{
		$app = App::getSingleton();
        $emailSettings = $app->getEmailSettings();

		$mail = new PHPMailer();

        $mail->CharSet = 'UTF-8';

		if ($app->isDevMode()) {
			$mail->SMTPDebug  = SMTP::DEBUG_CONNECTION;
		} else {
			$mail->SMTPDebug  = SMTP::DEBUG_OFF;
		}

        $mail->CharSet = 'utf-8';
        $mail->XMailer = self::X_MAILER_HEADER_VALUE;
		
		$mail->isSMTP();

		$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
		$mail->Host = $emailSettings['smtp_host'];
		$mail->Port = $emailSettings['smtp_port'];
		$mail->SMTPAuth = true;
		$mail->Username = $emailSettings['smtp_user'];
		$mail->Password = $emailSettings['smtp_password'];

		$mail->setFrom($emailSettings['no_reply'], ValidationUtils::ensureUtf8($app->getName()));
		$mail->addReplyTo($emailSettings['reply_to']);

        $mail->DKIM_domain = $emailSettings['dkim_domain'];
        $mail->DKIM_selector = $emailSettings['dkim_selector'];
        $mail->DKIM_private = $emailSettings['dkim_private_key'];
        $mail->DKIM_passphrase = $emailSettings['dkim_private_key_passphrase'];
		$mail->DKIM_identity = $mail->From;
		$mail->DKIM_copyHeaderFields = false;

		return $mail;
	}

	/**
	 * Envía un mensaje genérico, con los valores utilizados habitualmente.
	 * 
	 * @param User $recipient
	 * @param string $subject
	 * @param string $templateFileName
	 * @param array $templateFilling
	 * 
	 * @return bool
	 */
	private static function sendGenericMessage(
		User 	$recipient,
		string 	$subject,
		string 	$templateFileName,
		array 	$templateFilling
	): bool
	{
		$app = App::getSingleton();

		$mail = self::initialize();

		$mail->addAddress($recipient->getEmailAddress());
		$mail->isHTML(true);
		$mail->Subject = ValidationUtils::ensureUtf8($subject);

		$basicFilling = array(
			'app-name' => $app->getName(),
			'app-url' => $app->getUrl(),
			'user-first-name' => $recipient->getFirstName()
		);

		$mail->Body = self::generateMailTemplateRender(
			$templateFileName,
			array_merge($basicFilling, $templateFilling)
		);

		$mail->AltBody = self::generateMailTemplateRender(
			$templateFileName . '_plain',
			array_merge($basicFilling, $templateFilling)
		);

		if (! $app->isDevMode()) {
			return $mail->send();
		} else {
			echo 'Requested sending email but app instance is in developement mode.';
			$app->getViewManagerInstance()->addErrorMessage('Se solicitó el envío de un mensaje de correo electrónico, pero la instancia de la aplicación tiene activado el modo de depuración.');

			var_dump($mail);

			return true;
		}
	}

	/**
	 * Renderiza el contenido de un mensaje a partir de una plantilla y un
	 * relleno.
	 * 
	 * @param string $fileName
	 * @param string $filling
	 * 
	 * @return string
	 */
	private static function generateMailTemplateRender(
		string $fileName,
		array $filling
	): string
	{
		return TemplateUtils::fillTemplate(
			$fileName,
			$filling,
			realpath(App::getSingleton()->getRoot() . self::EMAIL_RESOURCES_PATH)
		);
	}

	/**
	 * Envía un mensaje con información sobre privacidad y protección de datos.
	 * 
	 * @param User $user
	 * 
	 * @return bool
	 */
	public static function sendUserPrivacyMessage(User $user): bool
	{
		return self::sendGenericMessage(
			$user,
			'Información sobre privacidad',
			'auth/email_privacy',
			array()
		);
	}

	/**
	 * Envía un mensaje de activación.
	 * 
	 * @param User $user
	 * 
	 * @return bool
	 */
	public static function sendUserActivationMessage(User $user): bool
	{
		return self::sendGenericMessage(
			$user,
			'Activar usuario',
			'auth/email_activation',
			array(
				'activation-url' => App::getSingleton()->getUrl() . PasswordResetProcessView::VIEW_ROUTE_BASE . $user->getToken()
			)
		);
	}

	/**
	 * Envía un mensaje de aviso de activación correcta.
	 * 
	 * @param User $user
	 * 
	 * @return bool
	 */
	public static function sendUserActivatedMessage(User $user): bool
	{
		return self::sendGenericMessage(
			$user,
			'Usuario activado correctamente',
			'auth/email_activated',
			array()
		);
	}

	/**
	 * Envía un mensaje de restablecimiento de contraseña para un usuario
	 * 
	 * @param User $user
	 * 
	 * @return bool
	 */
	public static function sendUserPasswordResetMessage(User $user): bool
	{
		return self::sendGenericMessage(
			$user,
			'Restablecer contraseña',
			'auth/email_password_reset',
			array(
				'reset-url' => App::getSingleton()->getUrl() . PasswordResetProcessView::VIEW_ROUTE_BASE . $user->getToken()
			)
		);
	}



	public static function sendTimePlannerAppointmentReservedMessage(
		string $userFirstName,
		string $userEmailAddress,
		string $dateTime,
		string $supportUrl,
		string $supportUrlNice
	): bool
	{
		$user = new User(
			0,
			null,
			$userFirstName,
			'',
			new DateTime,
			$userEmailAddress,
			'',
			null,
			new DateTime,
			null,
			null,
			'',
			null
		);

		return self::sendGenericMessage(
			$user,
			'Cita previa reservada',
			'time_planner/email_appointment_reserved',
			[
				'date-time' => $dateTime,
				'support-url' => $supportUrl,
				'support-url-nice' => $supportUrlNice
			]
		);
	}

	public static function sendLotFillingAssistantRequestUpdatedMessage(
		User $user,
		string $supportUrl,
		string $supportUrlNice
	): bool
	{
		return self::sendGenericMessage(
			$user,
			'Actualización de tu solicitud en el banco de libros',
			'bookbank/volunteer/lot_filling_assistant_request_updated',
			[
				'support-url' => $supportUrl,
				'support-url-nice' => $supportUrlNice
			]
		);
	}
}