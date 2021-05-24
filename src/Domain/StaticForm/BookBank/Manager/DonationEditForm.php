<?php

namespace Juancrrn\Lyra\Domain\StaticForm\BookBank\Manager;

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\Http;
use Juancrrn\Lyra\Common\TemplateUtils;
use Juancrrn\Lyra\Common\View\BookBank\Manager\StudentOverviewView;
use Juancrrn\Lyra\Domain\DomainUtils;
use Juancrrn\Lyra\Domain\StaticForm\StaticFormModel;
use Juancrrn\Lyra\Domain\User\User;
use Juancrrn\Lyra\Domain\User\UserRepository;

/**
 * Formulario de edición de donación
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class DonationEditForm extends StaticFormModel
{

    private const FORM_ID = 'form-bookbank-manager-donation-edit';

    private $itemId;

    public function __construct(string $action, int $itemId)
    {
        parent::__construct(self::FORM_ID, [ 'action' => $action ]);

        $this->itemId = $itemId;
    }
    
    protected function generateFields(array & $preloadedData = []): string
    {
        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        $educationLevelSelectOptionsHtml = TemplateUtils::generateSelectOptions(
            DomainUtils::getEducationLevelsForSelectOptions(),
            $preloadedData['educationLevel']
        );

        $initialContentsListHtml = '';

        if (empty($preloadedData['contents'])) {
            $initialContentsListHtml = $viewManager->fillTemplate(
                'html_templates/bookbank/common/template_subject_list_empty_item', []
            );
        } else {
            foreach ($preloadedData['contents'] as $subject) {
                $bookImageUrl = $subject->getBookImageUrl() ??
                    $app->getUrl() . '/img/graphic-default-book-image.svg';

                $bookName = $subject->getBookName() ??
                    'Sin libro o libro no definido';
                    
                $initialContentsListHtml .= $viewManager->fillTemplate(
                    'html_templates/bookbank/common/template_subject_list_editable_item',
                    [
                        'item-book-image-url' => $bookImageUrl,
                        'item-title-human' =>
                            $subject->getName() . ' de ' .
                            DomainUtils::educationLevelToHuman($subject->getEducationLevel())->getTitle(),
                        'item-book-isbn' => $subject->getBookIsbn(),
                        'item-book-name' => $bookName
                    ]
                );
            }
        }

        $filling = [
            'student-full-name' => $preloadedData['studentFullName'],
            'education-level-select-options-html' => $educationLevelSelectOptionsHtml,
            'school-year-human' => $preloadedData['schoolYear'],
            'initial-contents-list-html' => $initialContentsListHtml,
            'creator-name' => $preloadedData['creatorName'],
            'creation-date-human' => $preloadedData['creationDate'],
        ];

        return App::getSingleton()->getViewManagerInstance()->fillTemplate(
            'forms/bookbank/manager/inputs_donation_edit_form',
            $filling
        );
    }
    
    protected function process(array & $postedData): void
    {
        var_dump($postedData);

        // Añadir posibilidad de eliminar
        // No se puede editar el estudiante
        // Apartado de "otros datos"

        /*$app = App::getSingleton();
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
            Http::redirect($app->getUrl() . StudentOverviewView::VIEW_ROUTE_BASIC . $user->getId() . '/overview/');
        }*/

        $this->initialize();
    }
}