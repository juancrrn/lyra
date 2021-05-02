<?php 

namespace Juancrrn\Lyra\Common\View\Common;

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\View\ViewModel;

/**
 * Clase especial para la parte del pie de página
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class FooterPartView extends ViewModel
{

    private const VIEW_RESOURCE_FILE = 'common/view_part_footer';

    public function __construct()
    {
    }

    public function processContent(): void
    {
        $app = App::getSingleton();

        $appUrl = $app->getUrl();

        $v = (! $app->isDevMode()) ? '' : '?v=0.0.0' . time();

        $appUrl = $app->getUrl();
        $appName = $app->getName();
        $appProduction = (! $app->isDevMode()) ? 'true' : 'false';

        $jsAutoconf = <<< JS
        var autoconf = {
            APP_URL : "$appUrl",
            APP_NAME : "$appName",
            APP_PRODUCTION : $appProduction
        }
        JS;

        $filling = array(
            'app-name' => $app->getName(),
            'home-url' => $app->getUrl(),
            'js-autoconf' => $jsAutoconf,
            'cache-version' => (! $app->isDevMode()) ? '' : '?v=0.0.0' . time(),
        );

        $app->getViewManagerInstance()->renderTemplate(self::VIEW_RESOURCE_FILE, $filling);
    }
}