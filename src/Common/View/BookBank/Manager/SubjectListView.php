<?php

namespace Juancrrn\Lyra\Common\View\BookBank\Manager;

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\View\AppManager\AppSettingsView;
use Juancrrn\Lyra\Common\View\ViewModel;
use Juancrrn\Lyra\Domain\AjaxForm\BookBank\Manager\SubjectEditForm;
use Juancrrn\Lyra\Domain\BookBank\Subject\SubjectRepository;
use Juancrrn\Lyra\Domain\DomainUtils;
use Juancrrn\Lyra\Domain\User\User;

/**
 * Subject list view
 *
 * @package lyra
 * 
 * @author juancrrn
 *
 * @version 0.0.1
 */

class SubjectListView extends ViewModel
{
    private const VIEW_RESOURCE_FILE    = 'views/bookbank/manager/view_subject_list';
    public  const VIEW_NAME             = 'Asignaturas';
    public  const VIEW_ID               = 'bookbank-manage-subject-list';
    public  const VIEW_ROUTE            = '/bookbank/manage/subjects/';

    public function __construct()
    {
        $app = App::getSingleton();

        $sessionManager = $app->getSessionManagerInstance();

        $sessionManager->requirePermissionGroups([ User::NPG_BOOKBANK_MANAGER ]);

        $this->name = self::VIEW_NAME;
        $this->id = self::VIEW_ID;
    }

    public function processContent(): void
    {
        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        $filling = [
            'app-url' => $app->getUrl(),
            'view-name' => $this->getName(),
            'current-school-year' => DomainUtils::schoolYearToHuman($app->getSetting('school-year')),
            'settings-url' => $app->getUrl() . AppSettingsView::VIEW_ROUTE,
            'accordion-id' => self::VIEW_ID . '-accordion',
            'accordion-content' => $this->generateAccordionContent()
        ];

        $viewManager->renderTemplate(self::VIEW_RESOURCE_FILE, $filling);
    }

    private function generateAccordionContent(): string
    {
        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        $html = '';

        foreach (DomainUtils::EDU_LEVELS as $eduLevel) {
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

            $html .= $viewManager->fillTemplate(
                'views/bookbank/manager/view_subject_list_part_accordion_item',
                [
                    'id' => $eduLevel,
                    'title' => $human->getDescription() . ' (' . $human->getTitle() . ')',
                    'accordion-id' => self::VIEW_ID . '-accordion',
                    'accordion-item-prefix' => self::VIEW_ID . '-accordion-item-',
                    'subject-edit-form-modal-html' => $currentSubjectEditForm->generateModal(),
                    'content' => $content
                ]
            );
        }

        return $html;
    }
}