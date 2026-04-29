<?php

/**
 * MumieTaskHook plugin.
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use ILIAS\HTTP\Response\Sender\ResponseSendingException;
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
     * @throws ResponseSendingException
     */
    public function handleEvent(string $a_component, string $a_event, $a_parameter): void
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
     * @throws ResponseSendingException
     */
    #[NoReturn]
    private function logoutFromAllServers(): void
    {
        $logoutUrls = array_map(function ($server) {
            return $server->getLogoutUrl();
        }, ilMumieTaskServer::getAllServers());

        $returnUrl = ILIAS_HTTP_PATH.'/logout.php';
        $redirecturl = ILIAS_HTTP_PATH.'/Customizing/global/plugins/Services/EventHandling/EventHook/MumieTaskHook/prelogout.php?logoutUrl='
        .json_encode($logoutUrls)
            .'&redirect='.$returnUrl;
        $this->redirect($redirecturl);
    }

    /**
     * @throws ResponseSendingException
     */
    #[NoReturn]
    private function redirect(string $url): void
    {
        global $DIC;
        $response = $DIC->http()->{$this}->wrapper()
            ->withAddedHeader('Location', $url)
            ->withStatus(303)
        ;
        $DIC->http()->saveResponse($response);
        $DIC->http()->sendResponse();
        $DIC->http()->close();
    }
}
