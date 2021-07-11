<?php

namespace Juancrrn\Lyra\Domain\StaticForm\BookBank\Manager;

use DateTime;
use Juancrrn\Lyra\Common\Api\BookBank\Common\SubjectSearchApi;
use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\CommonUtils;
use Juancrrn\Lyra\Common\TemplateUtils;
use Juancrrn\Lyra\Domain\BookBank\Lot\Lot;
use Juancrrn\Lyra\Domain\BookBank\Lot\LotRepository;
use Juancrrn\Lyra\Domain\BookBank\Request\Request;
use Juancrrn\Lyra\Domain\BookBank\Request\RequestRepository;
use Juancrrn\Lyra\Domain\BookBank\Subject\SubjectRepository;
use Juancrrn\Lyra\Domain\DomainUtils;
use Juancrrn\Lyra\Domain\StaticForm\StaticFormModel;
use Juancrrn\Lyra\Domain\User\UserRepository;

/**
 * Request and lot edition form
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class RequestAndLotEditForm extends StaticFormModel
{

    private const FORM_ID = 'form-bookbank-manager-request-and-lot-edit';
    private const FORM_FIELDS_NAME_PREFIX = 'bookbank-manager-request-and-lot-edit-form-';

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

        $request = $preloadedData['request'];

        $lot = $preloadedData['lot'];
        
        $userRepository = new UserRepository($app->getDbConn());

        $statusSelectOptionsHtml = TemplateUtils::generateSelectOptions(
            Request::getStatusesForSelectOptions(),
            $request->getStatus()
        );

        $educationLevelSelectOptionsHtml = TemplateUtils::generateSelectOptions(
            DomainUtils::getEducationLevelsForSelectOptions(),
            $request->getEducationLevel()
        );

        $filling = [
            'student-full-name' => $userRepository->retrieveById($request->getStudentId())->getFullName(),
            'education-level-select-options-html' => $educationLevelSelectOptionsHtml,
            'school-year-human' => DomainUtils::schoolYearToHuman($request->getSchoolYear()),
            'query-url' => $app->getUrl() . SubjectSearchApi::API_ROUTE,
            'creator-name' => $userRepository->retrieveById($request->getCreatorId())->getFullName(),
            'creation-date-human' => strftime(
                CommonUtils::HUMAN_DATETIME_FORMAT_STRF,
                $request->getCreationDate()->getTimestamp()
            ),
            'form-fields-name-prefix' => self::FORM_FIELDS_NAME_PREFIX,
            //'checkbox-name' => self::FORM_FIELDS_NAME_PREFIX . 'lot-contents',
            'specification' => $request->getSpecification(),
            'status-select-options-html' => $statusSelectOptionsHtml,
            'associated-lot-html' => $this->generateAssociatedLotFieldsAndTemplates($lot)
        ];

        return $viewManager->fillTemplate(
            'forms/bookbank/manager/inputs_request_and_lot_edit_form',
            $filling
        );
    }
    
    protected function process(array & $postedData): void
    {
        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        $newStatus = $this->processNewStatus($postedData[self::FORM_FIELDS_NAME_PREFIX . 'status']);

        $newSpecification = $this->processNewSpecification($postedData[self::FORM_FIELDS_NAME_PREFIX . 'specification']);

        $newEducationLevel = $this->processNewEducationLevel($postedData[self::FORM_FIELDS_NAME_PREFIX . 'education-level']);

        if (! isset($postedData[self::FORM_FIELDS_NAME_PREFIX . 'lot-contents'])) {
            $viewManager->addErrorMessage('El paquete asociado no estar vacío. Si necesitas dejarlo así, marca la solicitud con un estado distinto a procesada.');
        } else {

            if ($newStatus == Request::STATUS_PROCESSED) {
                $newLotStatus = $this->processNewLotStatus($postedData[self::FORM_FIELDS_NAME_PREFIX . 'lot-status']);
                $newLotContents = $this->processNewLotContents($postedData[self::FORM_FIELDS_NAME_PREFIX . 'lot-contents'], $newEducationLevel);
            } else {
                $newLotStatus = null;
                $newLotContents = null;
            }

        }

        if (! $viewManager->anyErrorMessages()) {
            $requestRepository = new RequestRepository($app->getDbConn());

            $formerRequest = $requestRepository->retrieveById($this->itemId);

            // Update associated lot

            $lotRepository = new LotRepository($app->getDbConn());

            if ($newStatus == Request::STATUS_PROCESSED) {
                // Ensure there is a lot
                if ($formerRequest->getStatus() == Request::STATUS_PROCESSED) {
                    // Update
                    $lotRepository->smartUpdate(
                        $lotRepository->findByRequestId($this->itemId),
                        $newLotStatus,
                        $newLotContents
                    );

                    $viewManager->addSuccessMessage('El paquete asociado se actualizó correctamente.');
                } else {
                    // Create

                    // Process pickup date
    
                    if ($newLotStatus == Lot::STATUS_PICKED_UP ||
                        $newLotStatus == Lot::STATUS_RETURNED) {
                        $pickupDate = new DateTime;
                    } else {
                        $pickupDate = null;
                    }

                    // Process return date

                    if ($newLotStatus == Lot::STATUS_RETURNED) {
                        $returnDate = new DateTime;
                    } else {
                        $returnDate = null;
                    }

                    $newLot = new Lot(
                        null,
                        $this->itemId,
                        $newLotStatus,
                        new DateTime,
                        $app->getSessionManagerInstance()->getLoggedInUser()->getId(),
                        $pickupDate,
                        $returnDate,
                        null
                    );

                    $newLot->setId($lotRepository->insert($newLot));

                    $lotRepository->insertContentsById($newLot->getId(), $newLotContents);

                    $viewManager->addSuccessMessage('Se creó correctamente un paquete asociado con el identificador # ' . $newLot->getId() . '.');
                }
            } else {
                // Ensure there is not a lot
                if ($formerRequest->getStatus() == Request::STATUS_PROCESSED) {
                    // Delete
                    $formerLotId = $lotRepository->findByRequestId($this->itemId);

                    $lotRepository->deleteById($formerLotId, true);

                    $viewManager->addSuccessMessage('Se eliminó correctamente el paquete asociado antiguo por cambio de estado de la solicitud.');
                } else {
                    // Do nothing
                }
            }

            $requestRepository->updateStatusSpecificationAndEducationLevelById(
                $this->itemId,
                $newStatus,
                $newSpecification,
                $newEducationLevel
            );

            $viewManager->addSuccessMessage('La solicitud se actualizó correctamente.');
        } else {
            $viewManager->addErrorMessage('Por favor, vuelve a intentarlo.');
        }
    }

    private function generateAssociatedLotFieldsAndTemplates($lot): string
    {
        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();
        
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
                'checkbox-name' => self::FORM_FIELDS_NAME_PREFIX . 'lot-contents'
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

        // Generate initial lot HTML
        
        if (isset($lot)) {
            $userRepository = new UserRepository($app->getDbConn());

            $lotStatusSelectOptionsHtml = TemplateUtils::generateSelectOptions(
                Lot::getStatusesForSelectOptions(),
                $lot->getStatus()
            );
            
            $initialContentsListHtml = '';

            if (empty($lot->getContents())) {
                $initialContentsListHtml = $viewManager->fillTemplate(
                    'html_templates/bookbank/common/template_subject_list_empty_item', []
                );
            } else {
                foreach ($lot->getContents() as $subject) {
                    $bookImageUrl = $subject->getBookImageUrl() ??
                        $app->getUrl() . '/img/graphic-default-book-image.svg';

                    $bookName = $subject->getBookName() ??
                        'Sin libro o libro no definido';
                        
                    $initialContentsListHtml .= $viewManager->fillTemplate(
                        'html_templates/bookbank/common/template_subject_list_editable_item',
                        [
                            'book-image-url' => $bookImageUrl,
                            'title-human' =>
                                $subject->getName() . ' de ' .
                                DomainUtils::educationLevelToHuman($subject->getEducationLevel())->getTitle(),
                            'book-isbn' => $subject->getBookIsbn(),
                            'book-name' => $bookName,
                            'id' => $subject->getId(),
                            'checkbox-name' => self::FORM_FIELDS_NAME_PREFIX . 'lot-contents'
                        ]
                    );
                }
            }

            $pickupDateHuman =
                $lot->getStatus() == Lot::STATUS_PICKED_UP ||
                $lot->getStatus() == Lot::STATUS_RETURNED ?
                strftime(
                    CommonUtils::HUMAN_DATETIME_FORMAT_STRF,
                    $lot->getPickupDate()->getTimestamp()
                ) :
                'No definida'
                ;

            $returnDateHuman =
                $lot->getStatus() == Lot::STATUS_RETURNED ?
                strftime(
                    CommonUtils::HUMAN_DATETIME_FORMAT_STRF,
                    $lot->getReturnDate()->getTimestamp()
                ) :
                'No definida'
                ;

            $associatedLotHtml = $viewManager->fillTemplate(
                'views/bookbank/manager/view_request_and_lot_create_edit_part_associated_lot_editable',
                [
                    'id' => $lot->getId(),
                    'status-human' => Lot::statusToHuman($lot->getStatus())->getTitle(),
                    'creation-date-human' => strftime(
                        CommonUtils::HUMAN_DATETIME_FORMAT_STRF,
                        $lot->getCreationDate()->getTimestamp()
                    ),
                    'status-select-options-html' => $lotStatusSelectOptionsHtml,
                    'query-url' => $app->getUrl() . SubjectSearchApi::API_ROUTE,
                    'form-fields-name-prefix' => self::FORM_FIELDS_NAME_PREFIX . 'lot-',
                    'checkbox-name' => self::FORM_FIELDS_NAME_PREFIX . 'lot-contents',
                    'contents' => $initialContentsListHtml,
                    'pickup-date-human' => $pickupDateHuman,
                    'return-date-human' => $returnDateHuman,
                    'creator-name' => $userRepository->retrieveById($lot->getCreatorId())->getFullName(),
                    'creation-date-human' => strftime(
                        CommonUtils::HUMAN_DATETIME_FORMAT_STRF,
                        $lot->getCreationDate()->getTimestamp()
                    ),

                    // Enabled

                    'button-collapsed-class' => '',
                    'attr-bs-target' => 'bookbank-manager-associated-lot-accordion-body',
                    'attr-aria-controls' => 'bookbank-manager-associated-lot-accordion-body',
                    'attr-aria-expanded' => true,
                    'button-disabled-attr' => '',
                    'collapse-show-class' => 'show'
                ]
            );
        } else {
            $lotStatusSelectOptionsHtml = TemplateUtils::generateSelectOptions(
                Lot::getStatusesForSelectOptions(),
                Lot::STATUS_INITIAL
            );

            $initialContentsListHtml = $viewManager->fillTemplate(
                'html_templates/bookbank/common/template_subject_list_empty_item', []
            );

            $associatedLotHtml = $viewManager->fillTemplate(
                'views/bookbank/manager/view_request_and_lot_create_edit_part_associated_lot_editable',
                [
                    'id' => '-',
                    'status-human' => 'Plantilla de paquete asociado',
                    'creation-date-human' => 'ahora',
                    'status-select-options-html' => $lotStatusSelectOptionsHtml,
                    'query-url' => $app->getUrl() . SubjectSearchApi::API_ROUTE,
                    'form-fields-name-prefix' => self::FORM_FIELDS_NAME_PREFIX . 'lot-',
                    'checkbox-name' => self::FORM_FIELDS_NAME_PREFIX . 'lot-contents',
                    'contents' => $initialContentsListHtml,
                    'pickup-date-human' => 'No definida',
                    'return-date-human' => 'No definida',
                    'creator-name' => $app->getSessionManagerInstance()->getLoggedInUser()->getFullName(),
                    'creation-date-human' => 'Ahora',

                    // Disabled
                    
                    'button-collapsed-class' => 'collapsed',
                    'attr-bs-target' => '',
                    'attr-aria-controls' => '',
                    'attr-aria-expanded' => false,
                    'button-disabled-attr' => 'disabled="disabled"',
                    'collapse-show-class' => '',
                ]
            );
        }

        return $associatedLotHtml;
    }

    private function processNewStatus($newStatus = null): mixed
    {
        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        if (! $newStatus) {
            $viewManager->addErrorMessage('Hubo un error al procesar el estado de la solicitud.');
        } elseif (! Request::validStatus($newStatus)) {
            $viewManager->addErrorMessage('Hubo un error al procesar el estado de la solicitud.');
        }

        return $newStatus;
    }

    private function processNewSpecification($newSpecification = null): mixed
    {
        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        if (empty($newSpecification)) {
            $viewManager->addErrorMessage('El campo especificación no puede estar vacío.');
        }

        return $newSpecification;
    }
    
    private function processNewEducationLevel($newEducationLevel = null): mixed
    {
        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        if (! $newEducationLevel) {
            $viewManager->addErrorMessage('Hubo un error al procesar el nivel educativo.');
        } elseif (! DomainUtils::validEducationLevel($newEducationLevel)) {
            $viewManager->addErrorMessage('Hubo un error al procesar el nivel educativo.');
        }

        return $newEducationLevel;
    }

    private function processNewLotStatus($newLotStatus = null): mixed
    {
        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        if (! $newLotStatus) {
            $viewManager->addErrorMessage('Hubo un error al procesar el estado del paquete asociado.');
        } elseif (! Lot::validStatus($newLotStatus)) {
            $viewManager->addErrorMessage('Hubo un error al procesar el estado del paquete asociado.');
        }

        return $newLotStatus;
    }

    private function processNewLotContents(mixed $newLotContents, string $newEducationLevel): mixed
    {
        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        if (! is_array($newLotContents)) {
            $viewManager->addErrorMessage('Hubo un error al procesar los contenidos del paquete asociado.');
        } else {
            $subjectRepository = new SubjectRepository($app->getDbConn());

            foreach ($newLotContents as $newSubjectId) {
                if (! $subjectRepository->findById($newSubjectId)) {
                    $viewManager->addErrorMessage('Hubo un error al procesar los contenidos del paquete asociado.');
                } else {
                    $newSubject = $subjectRepository->retrieveById($newSubjectId);

                    if ($newSubject->getEducationLevel() != $newEducationLevel) {
                        $newLotContents = array_diff($newLotContents, [ $newSubjectId ]);

                        $viewManager->addWarningMessage('Se ignoró un contenido cuyo nivel educativo no coincidía con el de la solicitud.');
                    }
                }
            }
        }

        if (empty($newLotContents)) {
            $viewManager->addErrorMessage('La lista de contenidos no puede quedar vacía.');
        }

        return $newLotContents;
    }
}