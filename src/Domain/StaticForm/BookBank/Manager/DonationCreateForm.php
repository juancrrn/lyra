<?php

namespace Juancrrn\Lyra\Domain\StaticForm\BookBank\Manager;

use DateTime;
use Juancrrn\Lyra\Common\Api\BookBank\Common\SubjectSearchApi;
use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\CommonUtils;
use Juancrrn\Lyra\Common\TemplateUtils;
use Juancrrn\Lyra\Domain\BookBank\Donation\Donation;
use Juancrrn\Lyra\Domain\BookBank\Donation\DonationRepository;
use Juancrrn\Lyra\Domain\BookBank\Subject\SubjectRepository;
use Juancrrn\Lyra\Domain\DomainUtils;
use Juancrrn\Lyra\Domain\StaticForm\StaticFormModel;
use Juancrrn\Lyra\Domain\User\UserRepository;

/**
 * Formulario de creación de donación
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class DonationCreateForm extends StaticFormModel
{

    private const FORM_ID = 'form-bookbank-manager-donation-create';
    private const FORM_FIELDS_NAME_PREFIX = 'bookbank-manager-donation-create-form-';

    private $student;

    public function __construct(string $action, int $studentId)
    {
        parent::__construct(self::FORM_ID, [ 'action' => $action ]);

        $app = App::getSingleton();
        $userRepository = new UserRepository($app->getDbConn());

        $this->student = $userRepository->retrieveById($studentId);
    }
    
    protected function generateFields(array & $preloadedData = []): string
    {
        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        $educationLevelSelectOptionsHtml = TemplateUtils::generateSelectOptions(
            DomainUtils::getEducationLevelsForSelectOptions()
        );

        $initialContentsListHtml = $viewManager->fillTemplate(
            'html_templates/bookbank/common/template_subject_list_empty_item', []
        );

        // Add content list item template to HTML (available to AJAX)

        $viewManager->addTemplateElement(
            'bookbank-common-subject-list-editable-item',
            'bookbank\common\template_subject_list_editable_item',
            [
                'book-image-url' => '',
                'title-human' => '',
                'book-isbn' => '',
                'book-name' => '',
                'id' => '',
                'checkbox-name' => 'bookbank-manager-donation-create-form-contents'
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
                'book-image-url' => '',
                'title-human' => '',
                'book-isbn' => '',
                'book-name' => '',
                'id' => ''
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
            'query-url' => $app->getUrl() . SubjectSearchApi::API_ROUTE,
            'initial-contents-list-html' => $initialContentsListHtml,
            'creator-name' => $preloadedData['creatorName'],
            'creation-date-human' => $preloadedData['creationDate'],
            'form-fields-name-prefix' => self::FORM_FIELDS_NAME_PREFIX,
            'checkbox-name' => self::FORM_FIELDS_NAME_PREFIX . 'contents'
        ];

        return App::getSingleton()->getViewManagerInstance()->fillTemplate(
            'forms/bookbank/manager/inputs_donation_create_form',
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
                } else {
                    $newSubject = $subjectRepository->retrieveById($newSubjectId);

                    if ($newSubject->getEducationLevel() != $newEducationLevel) {
                        $newContents = array_diff($newContents, [ $newSubjectId ]);

                        $viewManager->addWarningMessage('Se ignoró un contenido cuyo nivel educativo no coincidía con el de la donación.');
                    }
                }
            }
        }

        if (! $viewManager->anyErrorMessages()) {
            $donationRepository = new DonationRepository($app->getDbConn());

            $donation = new Donation(
                null,
                $this->student->getId(),
                new DateTime,
                $app->getSessionManagerInstance()->getLoggedInUser()->getId(),
                $newEducationLevel,
                $app->getSetting('school-year'),
                false,
                null
            );

            $insertedId = $donationRepository->insert($donation);

            $donationRepository->insertContentsWithIds($insertedId, $newContents);

            $viewManager->addSuccessMessage('La donación fue creada correctamente.');
        } else {
            $viewManager->addErrorMessage('Por favor, vuelve a intentarlo.');
        }
    }
}