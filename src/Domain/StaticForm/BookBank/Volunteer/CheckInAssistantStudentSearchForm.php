<?php

namespace Juancrrn\Lyra\Domain\StaticForm\BookBank\Volunteer;

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\Http;
use Juancrrn\Lyra\Common\View\BookBank\Manager\StudentOverviewView;
use Juancrrn\Lyra\Common\View\BookBank\Volunteer\CheckInAssistantStudentOverviewView;
use Juancrrn\Lyra\Domain\StaticForm\StaticFormModel;
use Juancrrn\Lyra\Domain\User\User;
use Juancrrn\Lyra\Domain\User\UserRepository;

/**
 * @deprecated
 * 
 * Check-in assistant student search form
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class CheckInAssistantStudentSearchForm extends StaticFormModel
{

    private const FORM_ID = 'form-bookbank-volunteer-check-in-assistant-student-search';

    public function __construct(string $action)
    {
        parent::__construct(self::FORM_ID, [ 'action' => $action ]);
    }
    
    protected function generateFields(array & $preloadedData = []): string
    {
        return App::getSingleton()->getViewManagerInstance()->fillTemplate(
            'forms/bookbank/volunteer/inputs_check_in_assistant_student_search_form', []
        );
    }
    
    protected function process(array & $postedData): void
    {
        $app = App::getSingleton();
        $view = $app->getViewManagerInstance();

        $userRepository = new UserRepository($app->getDbConn());

        $govId = isset($postedData['gov_id']) ? $postedData['gov_id'] : null;

        $user = null;

        if (empty($govId)) {
            $view->addErrorMessage('El NIF o NIE no puede estar vacío.');
        } else {
            $userId = $userRepository->findByGovId($govId);

            if ($userId) {
                $user = $userRepository->retrieveById($userId, true);

                if (! $user->hasPermission(User::NPG_STUDENT)) {
                    $view->addErrorMessage('El usuario con el NIF o NIE introducido no tiene permisos de estudiante.');
                }
            } else {
                $view->addErrorMessage('El NIF o NIE introducido no pertenece a ningún usuario.');
            }
        }

        // Si no hay ningún error, continuar.
        if (! $view->anyErrorMessages()) {
            Http::redirect($app->getUrl() . CheckInAssistantStudentOverviewView::VIEW_ROUTE_BASE . $user->getId() . '/overview/');
        }

        $this->initialize();
    }
}