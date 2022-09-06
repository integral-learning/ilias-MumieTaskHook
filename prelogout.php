<?php
/**
 * MumieTaskHook plugin
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$logouturls = urldecode($_GET["logoutUrl"]);
$redirect = json_encode($_GET["redirect"]);
?>

<script>
    var logouturls = Object.values(<?php echo $logouturls ?>);
    var promises = [];
    logouturls.forEach(function(url) {
        promises.push(logoutFromServer(url));
    });
    Promise.all(promises)
    .then(function (values) {
        window.location.href =<?php echo $redirect ?>;
    });
    function logoutFromServer(url) {
        var promise = new Promise(function (resolve, reject) {
            var request = new XMLHttpRequest();
            request.open("GET", url);
            request.withCredentials = true;
            request.timeout = 10000;
            request.send();
            request.onreadystatechange = function () {
                resolve();
            }
            request.ontimeout = function (e) {
                resolve();
            }
        })
        return promise;
    }
</script>
