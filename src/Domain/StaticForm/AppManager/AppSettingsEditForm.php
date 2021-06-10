<?php

namespace Juancrrn\Lyra\Domain\StaticForm\AppManager;

use Juancrrn\Lyra\Common\Api\BookBank\Common\SubjectSearchApi;
use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\TemplateUtils;
use Juancrrn\Lyra\Domain\AppSetting\AppSettingRepository;
use Juancrrn\Lyra\Domain\BookBank\Donation\DonationRepository;
use Juancrrn\Lyra\Domain\BookBank\Subject\SubjectRepository;
use Juancrrn\Lyra\Domain\DomainUtils;
use Juancrrn\Lyra\Domain\StaticForm\StaticFormModel;

/**
 * App settings edit form
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class AppSettingsEditForm extends StaticFormModel
{

    private const FORM_ID = 'form-app-manager-app-settings-edit';
    private const FORM_FIELDS_NAME_PREFIX = 'app-manager-app-settings-edit-form-';

    public function __construct(string $action)
    {
        parent::__construct(self::FORM_ID, [ 'action' => $action ]);
    }
    
    protected function generateFields(array & $preloadedData = []): string
    {
        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        $itemsHtml = '';

        foreach ($preloadedData as $appSetting) {
            $itemsHtml .= $viewManager->fillTemplate(
                'forms/app_manager/part_inputs_app_settings_edit_form_item',
                [
                    'input-id' => self::FORM_FIELDS_NAME_PREFIX . $appSetting->getShortName(),
                    'short-name' => $appSetting->getShortName(),
                    'full-name' => $appSetting->getFullName(),
                    'description-id' => self::FORM_FIELDS_NAME_PREFIX . $appSetting->getShortName() . '-desc',
                    'value' => $appSetting->getValue(),
                    'description' => $appSetting->getDescription(),
                ]
            );
        }

        return $viewManager->fillTemplate(
            'forms/app_manager/inputs_app_settings_edit_form',
            [
                'items-html' => $itemsHtml
            ]
        );
    }
    
    protected function process(array & $postedData): void
    {
        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        $appSettingRepository = new AppSettingRepository($app->getDbConn());

        $appSettings = $appSettingRepository->retrieveAll();

        foreach ($appSettings as $appSetting) {
            if (! array_key_exists(
                self::FORM_FIELDS_NAME_PREFIX . $appSetting->getShortName(),
                $postedData
            )) {
                $viewManager->addErrorMessage('Hubo un error al procesar un parámetro.');
            }
        }

        if (! $viewManager->anyErrorMessages()) {
            $anyUpdated = false;

            foreach ($appSettings as $appSetting) {
                $newValue = $postedData[self::FORM_FIELDS_NAME_PREFIX . $appSetting->getShortName()];

                if ($appSetting->getValue() != $newValue) {
                    $appSettingRepository->updateValueByShortName(
                        $appSetting->getShortName(),
                        $newValue
                    );

                    $viewManager->addSuccessMessage('Parámetro ' . $appSetting->getShortName() . ' actualizado correctamente.');

                    $anyUpdated = true;
                }
            }

            if (! $anyUpdated) {
                $viewManager->addSuccessMessage('No hubo ningún cambio.');
            }
        } else {
            $viewManager->addErrorMessage('Por favor, vuelve a intentarlo.');
        }
    }
}