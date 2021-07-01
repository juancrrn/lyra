<?php 

namespace Juancrrn\Lyra\Common\View\Common;

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\View\AppManager\AppSettingsView;
use Juancrrn\Lyra\Common\View\Auth\LoginView;
use Juancrrn\Lyra\Common\View\BookBank\Manager\StudentSearchView;
use Juancrrn\Lyra\Common\View\BookBank\Manager\SubjectListView;
use Juancrrn\Lyra\Common\View\BookBank\Student\OverviewView;
use Juancrrn\Lyra\Common\View\Home\DashboardView;
use Juancrrn\Lyra\Common\View\ViewModel;
use Juancrrn\Lyra\Common\View\Home\HomeView;
use Juancrrn\Lyra\Common\View\Self\ProfileView;
use Juancrrn\Lyra\Domain\StaticForm\Auth\LogoutForm;
use Juancrrn\Lyra\Domain\User\User;

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

    private const VIEW_RESOURCE_FILE = 'views/common/view_part_header';

    public function __construct()
    {
    }

    public function processContent(): void
    {
        $app = App::getSingleton();

        $sessionManager = $app->getSessionManagerInstance();
        $viewManager = $app->getViewManagerInstance();

        $mainMenuBuffer = '';

        // Generar elementos de navegación del menú lateral.

        if ($sessionManager->isLoggedIn()) {
            $user = $sessionManager->getLoggedInUser();

            $mainMenuBuffer .= $viewManager->generateMainMenuLink(DashboardView::class);

            if ($user->hasPermission(User::NPG_STUDENT)) {
                $mainMenuBuffer .= $viewManager->generateMainMenuLink(OverviewView::class);
            }

            if ($user->hasPermission(User::NPG_BOOKBANK_MANAGER)) {
                $mainMenuBuffer .= $viewManager->generateMainMenuLink(StudentSearchView::class);

                $mainMenuBuffer .= $viewManager->generateMainMenuLink(SubjectListView::class);
            }
        } else {
            $mainMenuBuffer .= $viewManager->generateMainMenuLink(HomeView::class);
        }

        // Generar elementos de la navegación del menú de sesión de usuario.

        $userMenuBuffer = ''; 

        if ($sessionManager->isLoggedIn()) {
            /* App settings view link */
            if ($user->hasPermission(User::NPG_BOOKBANK_MANAGER)) {
                $appSettingsLinkActive =
                    $viewManager->getCurrentRenderingView() instanceof AppSettingsView ?
                    'active' : '';
                $appSettingsUrl = $app->getUrl() . AppSettingsView::VIEW_ROUTE;
                $userMenuBuffer .= $viewManager->generateUserMenuItem(
                    '<a class="nav-link ' . $appSettingsLinkActive . '" href="' . $appSettingsUrl . '">' . AppSettingsView::VIEW_NAME . '</a>'
                );
            }

            /* User profile view link */
            $user = $sessionManager->getLoggedInUser();
            
            $fullName = $user->getFullName();

            $profileLinkActive =
                $viewManager->getCurrentRenderingView() instanceof ProfileView ?
                'active' : '';
            $profileUrl = $app->getUrl() . ProfileView::VIEW_ROUTE;
            $userMenuBuffer .= $viewManager->generateUserMenuItem(
                '<a class="nav-link ' . $profileLinkActive . '" href="' . $profileUrl . '">' . $fullName . '</a>'
            );

            /* Logout form */
            $logoutForm = new LogoutForm('/auth/logout/');
            $logoutForm->handle();
            $logoutForm->initialize();
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