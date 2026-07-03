<?php
/**
 * MumieTaskHook plugin.
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
const JSON_ENCODE_FOR_SCRIPT = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP;

$decoded_logout_urls = json_decode($_GET['logoutUrl'] ?? '', true);
$logout_urls = is_array($decoded_logout_urls) ? array_values(array_filter($decoded_logout_urls, 'is_string')) : [];

$redirect = json_encode((string) ($_GET['redirect'] ?? ''), JSON_ENCODE_FOR_SCRIPT);
?>

<script>
    const logouturls = Object.values(<?php echo json_encode($logout_urls, JSON_ENCODE_FOR_SCRIPT); ?>);
    const promises = [];
    logouturls.forEach(function (url) {
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
