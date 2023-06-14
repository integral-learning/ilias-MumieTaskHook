<?php
/**
 * MumieTaskHook plugin
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

include_once("./Services/EventHandling/classes/class.ilEventHookPlugin.php");
class ilMumieTaskHookPlugin extends ilEventHookPlugin
{
    /**
     * Get Plugin Name. Must be same as in class name il<Name>Plugin
     * and must correspond to plugins subdirectory name.
     *
     * @return    string    Plugin Name
     */
    final public function getPluginName() : string
    {
        return "MumieTaskHook";
    }

    /**
     * @param string $a_component
     * @param string $event
     * @param array  $parameters
     */
    public function handleEvent($a_component, $a_event, $a_parameter) : void
{
        global $ilPluginAdmin;
        if (!$ilPluginAdmin->isActive(ilComponentInfo::TYPE_SERVICES, "Repository", "robj", "MumieTask")) {
            return;
        }

        switch ($a_event) {
            case 'beforeLogout':
                $userId = $a_parameter["user_id"];
                include_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskSSOToken.php');
                if (ilMumieTaskSSOToken::tokenExistsForIliasUser($userId)) {
                    ilMumieTaskSSOToken::invalidateAllTokensForUser($userId);
                    $this->logoutFromAllServers();
                }
        }
    }

    /**
     * Send a logout request for the current user to all configured MUMIE servers
     */
    private function logoutFromAllServers()
    {
        include_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskServer.php');
        $logoutUrls = array_map(function ($server) {
            return $server->getLogoutUrl();
        }, ilMumieTaskServer::getAllServers());

        $returnUrl = ILIAS_HTTP_PATH . '/' . "logout.php";
        $redirecturl = ILIAS_HTTP_PATH . '/Customizing/global/plugins/Services/EventHandling/EventHook/MumieTaskHook/prelogout.php?logoutUrl='
        . json_encode($logoutUrls)
            . '&redirect=' . $returnUrl;
        $this->redirect($redirecturl);
    }

    private function redirect($url, $statusCode = 303)
    {
        header('Location: ' . $url, true, $statusCode);
        die();
    }

    /**
     * Object initialization. Can be overwritten by plugin class
     * (and should be made private final)
     */
    protected function init() : void
    {
        // nothing to do
    }
}
