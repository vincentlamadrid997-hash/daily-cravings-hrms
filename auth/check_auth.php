<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");


if (!isset($_SESSION['user_id'])) {

    header("Location: /daily_cravings_hrms/login.php");
    exit();

}


$required_sessions = [

    'user_id',
    'full_name',
    'email',
    'role',
    'gender'

];


foreach ($required_sessions as $session_key) {

    if (!isset($_SESSION[$session_key]) || $_SESSION[$session_key] === '') {

        session_unset();
        session_destroy();

        header("Location: /daily_cravings_hrms/login.php");
        exit();

    }

}

?>