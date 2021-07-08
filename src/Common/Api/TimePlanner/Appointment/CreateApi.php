<?php

namespace Juancrrn\Lyra\Common\Api\TimePlanner\Appointment;

use DateTime;
use Juancrrn\Lyra\Common\Api\ApiModel;
use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\CommonUtils;
use Juancrrn\Lyra\Common\ValidationUtils;
use Juancrrn\Lyra\Domain\Email\EmailUtils;
use Juancrrn\Lyra\Domain\TimePlanner\Appointment\Appointment;
use Juancrrn\Lyra\Domain\TimePlanner\Appointment\AppointmentRepository;
use Juancrrn\Lyra\Domain\TimePlanner\Slot\SlotRepository;

class CreateApi extends ApiModel
{

    public const API_ROUTE = '/api/timeplanner/appointments/create/';

    public function consume(?object $requestContent): void
    {
        $app = App::getSingleton();

        // No session requirements

        // Input validation

        $messages = [];

        $studentGovId = $requestContent->{'student-gov-id'};
        $studentGovId = $studentGovId != '' ? $studentGovId : null;
        
        if (! empty($studentGovId) && ! ValidationUtils::validateGovId($studentGovId))
            $messages[] = 'El campo de NIF o NIE del estudiante no contiene un NIF o NIE válido.';

        $studentPhoneNumber = $requestContent->{'student-phone-number'} ?? null;

        if (empty($studentPhoneNumber))
            $messages[] = 'El campo de número de teléfono del estudiante no puede estar vacío.';

        $studentFirstName = $requestContent->{'student-first-name'} ?? null;

        if (empty($studentFirstName))
            $messages[] = 'El campo de nombre del estudiante no puede estar vacío.';

        $studentLastName = $requestContent->{'student-last-name'} ?? null;

        if (empty($studentLastName))
            $messages[] = 'El campo de apellidos del estudiante no puede estar vacío.';

        $studentBirthDate = $requestContent->{'student-birth-date'} ?? null;

        if (empty($studentBirthDate)) {
            $messages[] = 'El campo de fecha de nacimiento del estudiante no puede estar vacío.';
        } else {
            $studentBirthDate = DateTime::createFromFormat(
                CommonUtils::MYSQL_DATE_FORMAT,
                $studentBirthDate
            );

            if ($studentBirthDate == false)
                $messages[] = 'El campo de fecha de nacimiento del estudiante no contiene una fecha válida.';
        }

        $studentEmailAddress = $requestContent->{'student-email-address'} ?? null;

        if (empty($studentEmailAddress))
            $messages[] = 'El campo de dirección de correo electrónico del estudiante no puede estar vacío.';
        
        $requestSpecification = $requestContent->{'request-specification'};

        $legalRepGovId = $requestContent->{'legal-rep-gov-id'};

        if (! empty($legalRepGovId) && ! ValidationUtils::validateGovId($legalRepGovId))
            $messages[] = 'El campo de NIF o NIE del representante legal no contiene un NIF o NIE válido.';

        $legalRepPhoneNumber = $requestContent->{'legal-rep-phone-number'};
        $legalRepPhoneNumber = $legalRepPhoneNumber != '' ? $legalRepPhoneNumber : null;
        $legalRepFirstName = $requestContent->{'legal-rep-first-name'};
        $legalRepFirstName = $legalRepFirstName != '' ? $legalRepFirstName : null;
        $legalRepLastName = $requestContent->{'legal-rep-last-name'};
        $legalRepLastName = $legalRepLastName != '' ? $legalRepLastName : null;
        $legalRepEmailAddress = $requestContent->{'legal-rep-email-address'};
        $legalRepEmailAddress = $legalRepEmailAddress != '' ? $legalRepEmailAddress : null;

        $legalRepBirthDate = $requestContent->{'legal-rep-birth-date'};
        $legalRepBirthDate = $legalRepBirthDate != '' ? $legalRepBirthDate : null;

        if (! empty($legalRepBirthDate)) {
            $legalRepBirthDate = DateTime::createFromFormat(
                CommonUtils::MYSQL_DATE_FORMAT,
                $legalRepBirthDate
            );

            if ($legalRepBirthDate == false)
                $messages[] = 'El campo de fecha de nacimiento del representante legal no contiene una fecha válida.';
        }

        $appointmentDate = $requestContent->{'appointment-date'} ?? null;
        $appointmentTime = $requestContent->{'appointment-time'} ?? null;

        $slotRepo = new SlotRepository($app->getDbConn());
        $appointmentRepo = new AppointmentRepository($app->getDbConn());

        $selectedDate = null;
        $selectedTime = null;

        if (
            ! ValidationUtils::validateDate($appointmentDate) ||
            ! ValidationUtils::validateTime($appointmentTime)
        )
        {
            $messages[] = 'Hubo un error al procesar la reserva. Por favor, comprueba la fecha y hora seleccionadas';
        } else {
            $selectedDate = DateTime::createFromFormat(
                CommonUtils::MYSQL_DATE_FORMAT,
                $appointmentDate
            );
    
            $selectedTime = DateTime::createFromFormat(
                CommonUtils::MYSQL_TIME_FORMAT,
                $appointmentTime
            );

            if (! $slotRepo->validateDateAndTimeAvailability($selectedDate, $selectedTime)) {
                $messages[] = 'Hubo un error al procesar la reserva. Por favor, vuelve a intentarlo más tarde.';
            } elseif ($studentGovId != null && $appointmentRepo->findByStudentGovIdAndSlotDate($studentGovId, $selectedDate)) {
                $messages[] = 'El estudiante ya tiene una cita para el día indicado y no puede reservar más';
            }
        }

        $apiManager = $app->getApiManagerInstance();

        if (! empty($messages)) {
            $apiManager->apiRespond(
                400,
                null,
                $messages
            );
        } else {
            $slotId = $slotRepo->findByDateAndTime($selectedDate, $selectedTime)[0];

            $appointment = new Appointment(
                null,
                $slotId,
                null,
                $studentGovId,
                $studentFirstName,
                $studentLastName,
                $studentBirthDate,
                $studentEmailAddress,
                $studentPhoneNumber,
                $legalRepGovId,
                $legalRepFirstName,
                $legalRepLastName,
                $legalRepBirthDate,
                $legalRepEmailAddress,
                $legalRepPhoneNumber,
                $requestSpecification
            );

            $appointmentRepo->insert($appointment);
        }

        $emailDateTime =
            $selectedDate->format(CommonUtils::HUMAN_DATE_FORMAT) .
            ' a las ' .
            $selectedTime->format(CommonUtils::HUMAN_TIME_FORMAT);

        EmailUtils::sendTimePlannerAppointmentReservedMessage(
            $studentFirstName,
            $studentEmailAddress,
            $emailDateTime,
            'https://soporte.iax.es',
            'soporte.iax.es'
        );

        $reminderMessage =
            'Por favor, anote su cita para el próximo día <strong>' .
            $selectedDate->format(CommonUtils::HUMAN_DATE_FORMAT) .
            '</strong> a las <strong>' .
            $selectedTime->format(CommonUtils::HUMAN_TIME_FORMAT) .
            '</strong>.';

        $apiManager->apiRespond(
            200,
            null,
            [
                $reminderMessage,
                'La reserva se realizó correctamente. Le hemos enviado un mensaje de confirmación por correo electrónico.'
            ]
        );
    }
}