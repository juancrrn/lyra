<?php

namespace Juancrrn\Lyra\Domain\StaticForm\BookBank\Manager;

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\Http;
use Juancrrn\Lyra\Common\View\BookBank\Manager\StudentOverviewView;
use Juancrrn\Lyra\Domain\StaticForm\StaticFormModel;
use Juancrrn\Lyra\Domain\User\User;
use Juancrrn\Lyra\Domain\User\UserRepository;

/**
 * @deprecated
 * 
 * Formulario de búsqueda de estudiantes para gestión en el banco de libros
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class StudentSearchForm extends StaticFormModel
{

    private const FORM_ID = 'form-bookbank-manager-student-search';

    public function __construct(string $action)
    {
        parent::__construct(self::FORM_ID, [ 'action' => $action ]);
    }
    
    protected function generateFields(array & $preloadedData = []): string
    {
        return App::getSingleton()->getViewManagerInstance()->fillTemplate(
            'forms/bookbank/manager/inputs_student_search_form', []
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
            Http::redirect($app->getUrl() . StudentOverviewView::VIEW_ROUTE_BASE . $user->getId() . '/overview/');
        }

        $this->initialize();
    }
}