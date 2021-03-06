<?php 

namespace Juancrrn\Lyra\Common\View\Common;

use Juancrrn\Lyra\Common\App;
use Juancrrn\Lyra\Common\View\AppManager\AppSettingsView;
use Juancrrn\Lyra\Common\View\AppManager\UserSearchView;
use Juancrrn\Lyra\Common\View\Auth\LoginView;
use Juancrrn\Lyra\Common\View\LegalRep\RepresentedOverviewView;
use Juancrrn\Lyra\Common\View\BookBank\Manager\StudentSearchView;
use Juancrrn\Lyra\Common\View\BookBank\Manager\SubjectListView;
use Juancrrn\Lyra\Common\View\BookBank\Student\OverviewView;
use Juancrrn\Lyra\Common\View\BookBank\Volunteer\CheckInAssistantStudentSearchView;
use Juancrrn\Lyra\Common\View\BookBank\Volunteer\LotFillingAssistantHomeView;
use Juancrrn\Lyra\Common\View\Home\DashboardView;
use Juancrrn\Lyra\Common\View\ViewModel;
use Juancrrn\Lyra\Common\View\Home\HomeView;
use Juancrrn\Lyra\Common\View\Self\ProfileView;
use Juancrrn\Lyra\Common\View\TimePlanner\LandingView;
use Juancrrn\Lyra\Common\View\TimePlanner\Volunteer\AppointmentListView;
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
                $mainMenuBuffer .= $viewManager->generateNavBarItemDropdown(
                    null,
                    'Estudiante',
                    [
                        OverviewView::class
                    ]
                );
            }

            if ($user->hasPermission(User::NPG_LEGALREP)) {
                $mainMenuBuffer .= $viewManager->generateNavBarItemDropdown(
                    null,
                    'Representante legal',
                    [
                        RepresentedOverviewView::class
                    ]
                );
            }

            if ($user->hasPermission(User::NPG_BOOKBANK_VOLUNTEER)) {
                $mainMenuBuffer .= $viewManager->generateNavBarItemDropdown(
                    null,
                    'Voluntario BDL',
                    [
                        CheckInAssistantStudentSearchView::class,
                        AppointmentListView::class,
                        LotFillingAssistantHomeView::class
                    ]
                );
            }

            if ($user->hasPermission(User::NPG_BOOKBANK_MANAGER)) {
                $mainMenuBuffer .= $viewManager->generateNavBarItemDropdown(
                    null,
                    'Gestor BDL',
                    [
                        StudentSearchView::class,
                        SubjectListView::class
                    ]
                );
            }
            
            if ($user->hasPermission(User::NPG_APP_MANAGER)) {
                $mainMenuBuffer .= $viewManager->generateNavBarItemDropdown(
                    null,
                    'Gestor app',
                    [
                        AppSettingsView::class,
                        UserSearchView::class
                    ]
                );
            }
        } else {
            $mainMenuBuffer .= $viewManager->generateMainMenuLink(HomeView::class);
        }

        $mainMenuBuffer .= $viewManager->generateMainMenuLink(LandingView::class);

        // Generar elementos de la navegación del menú de sesión de usuario.

        $userMenuBuffer = ''; 

        if ($sessionManager->isLoggedIn()) {
            /* User profile view link */
            $user = $sessionManager->getLoggedInUser();

            $profileLinkActive =
                $viewManager->getCurrentRenderingView() instanceof ProfileView ?
                'active' : '';
            $profileUrl = $app->getUrl() . ProfileView::VIEW_ROUTE;
            $userMenuBuffer .= $viewManager->generateUserMenuItem(
                '<a class="nav-link ' . $profileLinkActive . '" href="' . $profileUrl . '">Mi perfil</a>'
            );

            /* Logout form */
            $logoutForm = new LogoutForm(LogoutForm::FORM_LOGOUT_GLOBAL_ROUTE);
            $logoutForm->handle();
            $logoutForm->initialize();
            $userMenuBuffer .= $viewManager->generateUserMenuItem($logoutForm->getHtml());
        } else {
            $loginUrl = $app->getUrl() . LoginView::VIEW_ROUTE;

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