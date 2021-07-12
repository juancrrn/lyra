<?php

namespace Juancrrn\Lyra\Common\View\TimePlanner\Volunteer;

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\CommonUtils;
use Juancrrn\Lyra\Common\ValidationUtils;
use Juancrrn\Lyra\Common\View\AppManager\AppSettingsView;
use Juancrrn\Lyra\Common\View\AppManager\UserCreateView;
use Juancrrn\Lyra\Common\View\BookBank\Manager\StudentOverviewView;
use Juancrrn\Lyra\Common\View\BookBank\Volunteer\CheckInAssistantStudentOverviewView;
use Juancrrn\Lyra\Common\View\BookBank\Volunteer\CheckInAssistantStudentSearchView;
use Juancrrn\Lyra\Common\View\ViewModel;
use Juancrrn\Lyra\Domain\DomainUtils;
use Juancrrn\Lyra\Domain\TimePlanner\Appointment\AppointmentRepository;
use Juancrrn\Lyra\Domain\TimePlanner\Slot\SlotRepository;
use Juancrrn\Lyra\Domain\User\User;
use Juancrrn\Lyra\Domain\User\UserRepository;

/**
 * Time planner appointment list view
 *
 * @package lyra
 * 
 * @author juancrrn
 *
 * @version 0.0.1
 */

class AppointmentListView extends ViewModel
{
    private const VIEW_RESOURCE_FILE    = 'views/time_planner/volunteer/view_appointment_list';
    public  const VIEW_NAME             = 'Reservas de cita previa';
    public  const VIEW_ID               = 'time-planner-volunteer-appointment-list';
    public  const VIEW_ROUTE            = '/timeplanner/appointments/';

    public function __construct()
    {
        $app = App::getSingleton();

        $sessionManager = $app->getSessionManagerInstance();

        $sessionManager->requirePermissionGroups([ User::NPG_BOOKBANK_VOLUNTEER ]);

        $this->name = self::VIEW_NAME;
        $this->id = self::VIEW_ID;
    }

    public function processContent(): void
    {
        $app = App::getSingleton();

        $sessionManager = $app->getSessionManagerInstance();

        $viewManager = $app->getViewManagerInstance();

        $filling = [
            'app-url' => $app->getUrl(),
            'view-name' => $this->getName(),
            'accordion-id' => self::VIEW_ID . '-accordion',
            'accordion-content' => $this->generateAccordionContent()
        ];
        
        $viewManager->renderTemplate(self::VIEW_RESOURCE_FILE, $filling);
    }

    private function generateAccordionContent(): string
    {
        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        $appointmentRepo = new AppointmentRepository($app->getDbConn());

        $slotRepo = new SlotRepository($app->getDbConn());

        $userRepo = new UserRepository($app->getDbConn());

        $appointments = $appointmentRepo->retrieveByIds($appointmentRepo->findFuture());

        $html = '';

        foreach ($appointments as $appointment) {
            $slot = $slotRepo->retrieveById($appointment->getSlotId());

            if (ValidationUtils::validateGovId($appointment->getStudentGovId())) {
                $studentId = $userRepo->findByGovId($appointment->getStudentGovId());

                if ($studentId) {
                    $student = $userRepo->retrieveById($studentId, true);

                    $govIdNotFoundNotice = '';

                    $checkInAssistantUrl = $app->getUrl() . CheckInAssistantStudentOverviewView::VIEW_ROUTE_BASE . $studentId . '/overview/';

                    if (! $student->hasPermission(User::NPG_STUDENT)) {
                        $actionButtons = <<< HTML
                        <p class="border rounded border-danger p-2">Este usuario no tiene permisos de estudiante. Un gestor de la aplicación debe añadírselos para poder atenderlo.</p>
                        HTML;
                    } else {
                        $actionButtons = <<< HTML
                        <p class="text-end"><a target="_blank" href="$checkInAssistantUrl" class="d-block btn btn-sm btn-primary mb-2">Atender</a></p>
                        HTML;

                        if ($app->getSessionManagerInstance()->getLoggedInUser()->hasPermission(User::NPG_BOOKBANK_MANAGER)) {
                            $studentOverviewUrl = $app->getUrl() . StudentOverviewView::VIEW_ROUTE_BASE . $studentId . '/overview/';

                            $actionButtons .= <<< HTML
                            <p class="text-end"><a target="_blank" href="$studentOverviewUrl" class="d-block btn btn-sm btn-secondary mb-2">Gestionar</a></p>
                            HTML;
                        }
                    }
                } else {
                    $govIdNotFoundNotice = '<p class="mt-1"><small class="text-muted">No se ha encontrado ningún usuario estudiante con este NIF o NIE.</small></p>';
                    
                    if ($app->getSessionManagerInstance()->getLoggedInUser()->hasPermission(User::NPG_APP_MANAGER)) {
                        $createUserUrl = $app->getUrl() . UserCreateView::VIEW_ROUTE;

                        $actionButtons = <<< HTML
                        <p class="text-end"><a target="_blank" href="$createUserUrl" class="d-block btn btn-sm btn-primary mb-2">Crear usuario</a></p>
                        HTML;
                    }
                }
            } else {
                $govIdNotFoundNotice = '<p class="mt-1"><small class="text-muted">Verificar NIF o NIE no especificado. Si no tiene, es necesario realizar una búsqueda por nombre, apellidos, número de teléfono o dirección de correo electrónico.</small></p>';

                $searchUrl = $app->getUrl() . CheckInAssistantStudentSearchView::VIEW_ROUTE;

                if ($app->getSessionManagerInstance()->getLoggedInUser()->hasPermission(User::NPG_APP_MANAGER)) {
                    $createUserUrl = $app->getUrl() . UserCreateView::VIEW_ROUTE;

                    $createUserA = <<< HTML
                    <p class="text-end"><a target="_blank" href="$createUserUrl" class="d-block btn btn-sm btn-secondary mb-2">Crear usuario</a></p>
                    HTML;
                } else {
                    $createUserA = '';
                }

                $actionButtons = <<< HTML
                <p class="text-end"><a target="_blank" href="$searchUrl" class="d-block btn btn-sm btn-primary mb-2">Buscar</a></p>
                $createUserA
                HTML;
            }

            $html .= $viewManager->fillTemplate(
                'views/time_planner/volunteer/view_appointment_list_part_accordion_item',
                [
                    'id' => $appointment->getId(),
                    'student-full-name' => $appointment->getStudentFirstName() . ' ' . $appointment->getStudentLastName(),
                    'student-gov-id' => mb_strtoupper($appointment->getStudentGovId()),
                    'gov-id-not-found-notice' => $govIdNotFoundNotice,
                    'student-email-address' => $appointment->getStudentEmailAddress(),
                    'student-phone-number' => $appointment->getStudentPhoneNumber(),
                    'date' => $slot->getDate()->format(CommonUtils::MYSQL_DATE_FORMAT),
                    'time' => $slot->getTime()->format(CommonUtils::MYSQL_TIME_FORMAT),
                    'action-buttons' => $actionButtons,

                    'student-birth-date' => $appointment->getStudentBirthDate()->format(CommonUtils::MYSQL_DATE_FORMAT),

                    'request-specification' => $appointment->getRequestSpecification(),
                    'legal-rep-full-name' => $appointment->getLegalRepFirstName() . ' ' . $appointment->getLegalRepLastName(),
                    'legal-rep-gov-id' => $appointment->getLegalRepGovId(),
                    'legal-rep-birth-date' =>
                        $appointment->getLegalRepBirthDate() == null ? '' :
                        $appointment->getLegalRepBirthDate()->format(CommonUtils::MYSQL_DATE_FORMAT),
                    'legal-rep-email-address' => $appointment->getLegalRepEmailAddress(),
                    'legal-rep-phone-number' => $appointment->getLegalRepPhoneNumber(),


                    'accordion-id' => self::VIEW_ID . '-accordion',
                    'accordion-item-prefix' => self::VIEW_ID . '-accordion-item-',
                    'content' => ''
                ]
            );
        }




        /*foreach (DomainUtils::EDU_LEVELS as $eduLevel) {
            $human = DomainUtils::educationLevelToHuman($eduLevel);

            $currentSubjectEditForm = new SubjectEditForm($eduLevel);

            $subjectRepo = new SubjectRepository($app->getDbConn());

            $subjectIds = $subjectRepo->findByEducationLevel($eduLevel);

            $content = '';

            foreach ($subjectIds as $subjectId) {
                $subject = $subjectRepo->retrieveById($subjectId);

                $bookImageUrl = $subject->getBookImageUrl() ??
                    $app->getUrl() . '/img/graphic-default-book-image.svg';

                $bookName = $subject->getBookName() ??
                    'Sin libro o libro no definido';

                $content .= $viewManager->fillTemplate(
                    'views/bookbank/manager/view_subject_list_part_accordion_item_part_subject_item_editable',
                    [
                        'book-image-url' => $bookImageUrl,
                        'title-human' =>
                            $subject->getName() . ' de ' .
                            DomainUtils::educationLevelToHuman($subject->getEducationLevel())->getTitle(),
                        'book-isbn' => $subject->getBookIsbn(),
                        'book-name' => $bookName,
                        'edit-button' => $currentSubjectEditForm->generateButton('Editar', $subject->getId(), true)
                    ]
                );
            }
        }*/

        return $html;
    }
}