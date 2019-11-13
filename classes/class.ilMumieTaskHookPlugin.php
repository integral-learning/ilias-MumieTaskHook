<?php
include_once ("./Services/EventHandling/classes/class.ilEventHookPlugin.php");
class ilMumieTaskHookPlugin extends ilEventHookPlugin {
    /**
     * Get Plugin Name. Must be same as in class name il<Name>Plugin
     * and must correspond to plugins subdirectory name.
     *
     * @return    string    Plugin Name
     */
    final function getPluginName() {
        return "MumieTaskHook";
    }

    /**
     * @param string $component
     * @param string $event
     * @param array  $parameters
     */
    public function handleEvent($component, $event, $parameters) {
        include_once ('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/debugToConsole.php');

        switch ($event) {
            case 'beforeLogout':
                $userId = $parameters["user_id"];
                include_once ('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskSSOToken.php');
                include_once ('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskIdHashingService.php');
                $hashedId = ilMumieTaskIdHashingService::getHashForUser($userId);
                if (ilMumieTaskSSOToken::tokenExistsForUser($hashedId)) {
                    ilMumieTaskSSOToken::invalidateTokenForUser($hashedId);
                    $this->logoutFromAllServers();
                }
        }
    }

    private function logoutFromAllServers() {
        include_once ('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskServer.php');
        $logoutUrls = array_map(function ($server) {
            return $server->getLogoutUrl();
        }, ilMumieTaskServer::getAllServers());

        $returnUrl = ILIAS_HTTP_PATH . '/' . "logout.php";
        $redirecturl = ILIAS_HTTP_PATH . "/Customizing/global/plugins/Services/EventHandling/EventHook/MumieTaskHook/prelogout.php?logoutUrl="
        . json_encode($logoutUrls)
            . "&redirect=" . $returnUrl;
        $this->redirect($redirecturl);
    }

    private function redirect($url, $statusCode = 303) {
        header('Location: ' . $url, true, $statusCode);
        die();
    }

    /**
     * Object initialization. Can be overwritten by plugin class
     * (and should be made private final)
     */
    protected function init() {
        // nothing to do
    }
}
?>