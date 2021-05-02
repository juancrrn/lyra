<?php 

namespace Juancrrn\Lyra\Common\View\Common;

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\View\Auth\LoginView;
use Juancrrn\Lyra\Common\View\ViewModel;
use Juancrrn\Lyra\Common\View\Home\HomeView;
use Juancrrn\Lyra\Domain\StaticForm\Auth\LogoutForm;

/**
 * Clase especial para la parte de la cabecera de página
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class HeaderPartView extends ViewModel
{

    private const VIEW_RESOURCE_FILE = 'common/view_part_header';

    public function __construct()
    {
    }

    public function processContent(): void
    {
        $app = App::getSingleton();

        $sessionManager = $app->getSessionManagerInstance();
        $viewManager = $app->getViewManagerInstance();

        $logoutForm = new LogoutForm('/auth/logout/');
        $logoutForm->handle();
        $logoutForm->initialize();

        $mainMenuBuffer = '';

        // Generar elementos de navegación del menú lateral.
        $mainMenuBuffer .= $viewManager->generateMainMenuLink(HomeView::class);

        if ($sessionManager->isLoggedIn()) {
            $user = $sessionManager->getLoggedInUser();

            // TODO
        }

        // Generar elementos de la navegación del menú de sesión de usuario.

        $userMenuBuffer = ''; 

        if ($sessionManager->isLoggedIn()) {
            $user = $sessionManager->getLoggedInUser();
            
            $fullName = $user->getFullName();

            $profileUrl = $app->getUrl() . '/self/profile/';
            
            // TODO

            $userMenuBuffer .= $viewManager->generateUserMenuItem('<a class="nav-link" href="' . $profileUrl . '">' . $fullName . '</a>');
            //$userMenuBuffer .= $viewManager->generateUserMenuItem('<span class="badge bg-secondary lyra-user-type-badge">' . $userTypeTitle . '</span>');
            $userMenuBuffer .= $viewManager->generateUserMenuItem($logoutForm->getHtml());
        } else {
            $loginUrl = $app->getUrl() . '/auth/login/';

            $userMenuBuffer .= $viewManager->generateUserMenuItem("<a class=\"nav-link\" href=\"$loginUrl\">Iniciar sesión</a>", LoginView::class);
        }

        $filling = array(
            'app-name' => $app->getName(),
            'current-page-name' => $viewManager->getCurrentPageName(),
            'current-page-id' => $viewManager->getCurrentPageId(),
            'app-url' => $app->getUrl(),
            'cache-version' => (! $app->isDevMode()) ? '' : '?v=0.0.0' . time(),
            'main-menu-items' => $mainMenuBuffer,
            'user-menu-items' => $userMenuBuffer
        );

        $app->getViewManagerInstance()->renderTemplate(self::VIEW_RESOURCE_FILE, $filling);
    }
}