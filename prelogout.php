<?php
/**
 * MumieTaskHook plugin.
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$logouturls = urldecode($_GET['logoutUrl']);
$redirect = json_encode($_GET['redirect']);
?>

<script>
    const logouturls = Object.values(<?php echo $logouturls; ?>);
    const promises = [];
    logouturls.forEach(function(url) {
        promises.push(logoutFromServer(url));
    });
    Promise.all(promises)
    .then(function () {
        window.location.href =<?php echo $redirect; ?>;
    });
    function logoutFromServer(url) {
        return new Promise(function (resolve) {
            const request = new XMLHttpRequest();
            request.open("GET", url);
            request.withCredentials = true;
            request.timeout = 10000;
            request.send();
            request.onreadystatechange = function () {
                resolve();
            }
            request.ontimeout = function () {
                resolve();
            }
        });
    }
</script>
