<?php

namespace Juancrrn\Lyra\Domain\StaticForm\BookBank\Volunteer;

use DateTime;
use Exception;
use Juancrrn\Lyra\Common\Api\BookBank\Common\SubjectSearchApi;
use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\Http;
use Juancrrn\Lyra\Common\TemplateUtils;
use Juancrrn\Lyra\Common\View\BookBank\Manager\StudentOverviewView;
use Juancrrn\Lyra\Common\View\BookBank\Volunteer\CheckInAssistantStudentOverviewView;
use Juancrrn\Lyra\Domain\BookBank\Donation\Donation;
use Juancrrn\Lyra\Domain\BookBank\Donation\DonationRepository;
use Juancrrn\Lyra\Domain\BookBank\Request\RequestRepository;
use Juancrrn\Lyra\Domain\BookBank\Subject\SubjectRepository;
use Juancrrn\Lyra\Domain\DomainUtils;
use Juancrrn\Lyra\Domain\StaticForm\StaticFormModel;
use Juancrrn\Lyra\Domain\User\User;
use Juancrrn\Lyra\Domain\User\UserRepository;

/**
 * Check-in assistant donation lite form
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class LotFillingAssistantRankAndFillForm extends StaticFormModel
{

    private const FORM_ID = 'form-bookbank-volunteer-lot-filling-assistant-rank-and-fill';
    private const FORM_FIELDS_NAME_PREFIX = self::FORM_ID . '-';

    private $topPendingRequest;

    public function __construct(string $action)
    {
        parent::__construct(self::FORM_ID, [ 'action' => $action ]);

        $app = App::getSingleton();
        
        $requestRepo = new RequestRepository($app->getDbConn());

        dd($requestRepo->retrieveAll());

        $this->topPendingRequest = null;
    }
    
    protected function generateFields(array & $preloadedData = []): string
    {
        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        dd($preloadedData);

        $educationLevelSelectOptionsHtml = TemplateUtils::generateSelectOptions(
            DomainUtils::getEducationLevelsForSelectOptions()
        );

        // Add content list item template to HTML (available to AJAX)

        $viewManager->addTemplateElement(
            'bookbank-common-subject-list-editable-item',
            'bookbank/common/template_subject_list_editable_item',
            [
                'book-image-url' => '',
                'title-human' => '',
                'book-isbn' => '',
                'book-name' => '',
                'id' => '',
                'checkbox-name' => self::FORM_FIELDS_NAME_PREFIX . 'contents'
            ]
        );
        
        $viewManager->addTemplateElement(
            'bookbank-common-subject-list-empty-item',
            'bookbank/common/template_subject_list_empty_item',
            []
        );

        // Add search list item template to HTML (available to AJAX)

        $viewManager->addTemplateElement(
            'bookbank-common-subject-search-item',
            'bookbank/common/template_subject_search_item',
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
            'bookbank/common/template_subject_search_empty_item',
            []
        );

        return App::getSingleton()->getViewManagerInstance()->fillTemplate(
            'forms/bookbank/volunteer/inputs_check_in_assistant_donation_lite_form',
            [
                'education-level-select-options' => $educationLevelSelectOptionsHtml,
                'school-year' => DomainUtils::schoolYearToHuman($app->getSetting('school-year')),
                'query-url' => $app->getUrl() . SubjectSearchApi::API_ROUTE,
                'initial-contents-list-html' => $viewManager->fillTemplate(
                    'html_templates/bookbank/common/template_subject_list_empty_item', []
                ),
                'form-fields-name-prefix' => self::FORM_FIELDS_NAME_PREFIX,
                'checkbox-name' => self::FORM_FIELDS_NAME_PREFIX . 'contents'
                
            ]
        );
    }
    
    protected function process(array & $postedData): void
    {
        $app = App::getSingleton();
        
        $viewManager = $app->getViewManagerInstance();

        dd($postedData);
    }
}