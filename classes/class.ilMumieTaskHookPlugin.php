<?php

/**
 * MumieTaskHook plugin.
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use JetBrains\PhpStorm\NoReturn;

class ilMumieTaskHookPlugin extends ilEventHookPlugin
{
    /**
     * Get Plugin Name. Must be same as in class name il<Name>Plugin
     * and must correspond to plugins subdirectory name.
     *
     * @return string Plugin Name
     */
    final public function getPluginName(): string
    {
        return 'MumieTaskHook';
    }

    /**
     * Handle the event.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @throws ilCtrlException
     */
    public function handleEvent(string $a_component, string $a_event, array $a_parameter): void
    {
        global $DIC;
        if (!$DIC['component.repository']->hasActivatedPlugin('xmum')) {
            return;
        }

        if ('beforeLogout' == $a_event) {
            $userId = $a_parameter['user_id'];
            if (ilMumieTaskSSOToken::tokenExistsForIliasUser($userId)) {
                ilMumieTaskSSOToken::invalidateAllTokensForUser($userId);
                $this->logoutFromAllServers();
            }
        }
    }

    /**
     * Object initialization. Can be overwritten by plugin class
     * (and should be made private final).
     */
    protected function init(): void
    {
        // nothing to do
    }

    /**
     * Send a logout request for the current user to all configured MUMIE servers.
     *
     * @throws ilCtrlException
     */
    #[NoReturn]
    private function logoutFromAllServers(): void
    {
        global $DIC;

        $logoutUrls = array_map(function ($server) {
            return $server->getLogoutUrl();
        }, ilMumieTaskServer::getAllServers());

        $ctrl = $DIC->ctrl();
        $ctrl->setTargetScript('logout.php');
        $returnUrl = ILIAS_HTTP_PATH . '/' . $ctrl->getLinkTargetByClass([ilStartUpGUI::class], 'doLogout');
        $ctrl->setTargetScript('ilias.php');

        $redirectUrl = ILIAS_HTTP_PATH . '/Customizing/global/plugins/Services/EventHandling/EventHook/MumieTaskHook/prelogout.php'
            . '?logoutUrl=' . urlencode(json_encode($logoutUrls))
            . '&redirect=' . urlencode($returnUrl);

        $this->redirect($redirectUrl);
    }

    #[NoReturn]
    private function redirect(string $url): void
    {
        global $DIC;

        $DIC->ctrl()->redirectToURL($url);
    }
}
