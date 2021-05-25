<?php

namespace Juancrrn\Lyra\Domain\StaticForm\BookBank\Manager;

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\TemplateUtils;
use Juancrrn\Lyra\Domain\BookBank\Donation\DonationRepository;
use Juancrrn\Lyra\Domain\BookBank\Subject\SubjectRepository;
use Juancrrn\Lyra\Domain\DomainUtils;
use Juancrrn\Lyra\Domain\StaticForm\StaticFormModel;

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
    private const FORM_FIELDS_NAME_PREFIX = 'bookbank-manager-donation-edit-form-';

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
                        'item-book-name' => $bookName,
                        'item-id' => $subject->getId(),
                        'checkbox-name' => 'bookbank-manager-donation-edit-form-contents'
                    ]
                );
            }
        }

        // Add content list item template to HTML (available to AJAX)

        $viewManager->addTemplateElement(
            'bookbank-common-subject-list-editable-item',
            'bookbank\common\template_subject_list_editable_item',
            [
                'item-book-image-url' => '',
                'item-title-human' => '',
                'item-book-isbn' => '',
                'item-book-name' => '',
                'item-id' => '',
                'checkbox-name' => 'bookbank-manager-donation-edit-form-contents'
            ]
        );
        
        $viewManager->addTemplateElement(
            'bookbank-common-subject-list-empty-item',
            'bookbank\common\template_subject_list_empty_item',
            []
        );

        // Add search list item template to HTML (available to AJAX)

        $viewManager->addTemplateElement(
            'bookbank-common-subject-search-item',
            'bookbank\common\template_subject_search_item',
            [
                'item-book-image-url' => '',
                'item-title-human' => '',
                'item-book-isbn' => '',
                'item-book-name' => '',
                'item-id' => ''
            ]
        );
        
        $viewManager->addTemplateElement(
            'bookbank-common-subject-search-empty-item',
            'bookbank\common\template_subject_search_empty_item',
            []
        );

        $filling = [
            'student-full-name' => $preloadedData['studentFullName'],
            'education-level-select-options-html' => $educationLevelSelectOptionsHtml,
            'school-year-human' => $preloadedData['schoolYear'],
            'query-url' => $app->getUrl() . '/bookbank/manage/subjects/search/',
            'initial-contents-list-html' => $initialContentsListHtml,
            'creator-name' => $preloadedData['creatorName'],
            'creation-date-human' => $preloadedData['creationDate'],
            'form-fields-name-prefix' => self::FORM_FIELDS_NAME_PREFIX,
            'checkbox-name' => self::FORM_FIELDS_NAME_PREFIX . 'contents'
        ];

        return App::getSingleton()->getViewManagerInstance()->fillTemplate(
            'forms/bookbank/manager/inputs_donation_edit_form',
            $filling
        );
    }
    
    protected function process(array & $postedData): void
    {
        $app = App::getSingleton();
        $viewManager = $app->getViewManagerInstance();

        $newEducationLevel = $postedData[self::FORM_FIELDS_NAME_PREFIX . 'education-level'] ?? null;

        if (! $newEducationLevel) {
            $viewManager->addErrorMessage('Hubo un error al procesar el nivel educativo.');
        } elseif (! DomainUtils::validEducationLevel($newEducationLevel)) {
            $viewManager->addErrorMessage('Hubo un error al procesar el nivel educativo.');
        }

        $newContents = $postedData[self::FORM_FIELDS_NAME_PREFIX . 'contents'] ?? null;

        if (! is_array($newContents)) {
            $viewManager->addErrorMessage('Hubo un error al procesar los nuevos contenidos.');
        } else {
            $subjectRepository = new SubjectRepository($app->getDbConn());

            foreach ($newContents as $newSubjectId) {
                if (! $subjectRepository->findById($newSubjectId)) {
                    $viewManager->addErrorMessage('Hubo un error al procesar los nuevos contenidos.');
                }
            }
        }

        if (! $viewManager->anyErrorMessages()) {
            $donationRepository = new DonationRepository($app->getDbConn());

            $donationRepository->updateEducationLevelAndContentsById($this->itemId, $newEducationLevel, $newContents);

            $viewManager->addSuccessMessage('La donación fue actualizada correctamente.');
        } else {
            $viewManager->addErrorMessage('Por favor, vuelve a intentarlo.');
        }
    }
}