<?php

/*
 * Session inactivity timeout, now driven by the Security Policy
 * setting configured on the System Settings page (setting_name =
 * 'session_timeout_minutes') instead of a hardcoded value.
 *
 * Falls back to 15 minutes if the setting is missing, non-numeric,
 * or the table/DB isn't reachable for some reason — this file must
 * never fatal, since it runs on every authenticated page load.
 */

require_once __DIR__ . "/../config/db.php";

$timeoutMinutes = 15;

try {

    $stmt = $conn->prepare("
        SELECT setting_value
        FROM system_settings
        WHERE setting_name = 'session_timeout_minutes'
    ");

    $stmt->execute();

    $configuredValue = $stmt->fetchColumn();

    if (
        $configuredValue !== false
        && is_numeric($configuredValue)
        && (int) $configuredValue > 0
    ) {
        $timeoutMinutes = (int) $configuredValue;
    }

} catch (Exception $e) {
    // Table/setting not available — keep the default above.
}

$timeout = $timeoutMinutes * 60;


if (isset($_SESSION['last_activity'])) {

    $inactive_time =
    time() - $_SESSION['last_activity'];

    if ($inactive_time > $timeout) {

        session_unset();

        session_destroy();

        session_start();

        $_SESSION['error'] =
        "Session expired. Please login again.";

        header("Location: /daily_cravings_hrms/login.php");

        exit();

    }

}


$_SESSION['last_activity'] = time();


header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

header("Cache-Control: post-check=0, pre-check=0", false);

header("Pragma: no-cache");

?>