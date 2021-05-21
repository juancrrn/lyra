<?php 

namespace Juancrrn\Lyra\Common\View\Home;

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\View\ViewModel;

/**
 * Vista de página principal
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class HomeView extends ViewModel
{

    private const VIEW_RESOURCE_FILE    = 'views/home/view_home';
    public  const VIEW_NAME             = 'Inicio';
    public  const VIEW_ID               = 'home';
    public  const VIEW_ROUTE            = '/?';

    public function __construct()
    {
        $this->name = self::VIEW_NAME;
        $this->id = self::VIEW_ID;
    }

    public function processContent(): void
    {
        $app = App::getSingleton();

        $filling = array(
            'app-name' => $app->getName(),
            'app-url' => $app->getUrl()
        );

        $app->getViewManagerInstance()->renderTemplate(self::VIEW_RESOURCE_FILE, $filling);
    }
}