<?php

namespace Juancrrn\Lyra\Domain\Email;

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\ValidationUtils;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

/**
 * Email utils
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
    private const VIEW_RESOURCES_PATH = 'resources/email';

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
}