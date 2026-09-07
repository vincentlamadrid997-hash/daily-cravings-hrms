<?php

require_once __DIR__ . '/check_auth.php';
require_once __DIR__ . '/session_security.php';


if(!isset($_SESSION['user_id'])){

    header("Location: ../login.php");

    exit();

}


if(!isset($_SESSION['role']) || $_SESSION['role'] !== "employee"){

    session_unset();
    session_destroy();

    header("Location: ../login.php");

    exit();

}

?>