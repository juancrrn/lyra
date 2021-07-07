<?php

namespace Juancrrn\Lyra\Common\View\TimePlanner;

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\View\ViewModel;

/**
 * Time planner landing view
 *
 * @package lyra
 * 
 * @author juancrrn
 *
 * @version 0.0.1
 */

class LandingView extends ViewModel
{
    private const VIEW_RESOURCE_FILE    = 'views/time_planner/view_landing';
    public  const VIEW_NAME             = 'Cita previa';
    public  const VIEW_ID               = 'time-planner-landing';
    public  const VIEW_ROUTE            = '/timeplanner/landing/';

    private $form;

    public function __construct()
    {
        $app = App::getSingleton();

        $this->name = self::VIEW_NAME;
        $this->id = self::VIEW_ID;
    }

    public function processContent(): void
    {
        $app = App::getSingleton();

        $viewManager = $app->getViewManagerInstance();

        $filling = [
            'app-url' => $app->getUrl(),
            'view-name' => $this->getName()
        ];
        
        $viewManager->renderTemplate(self::VIEW_RESOURCE_FILE, $filling);
    }
}