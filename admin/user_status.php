<?php

session_start();

require_once "../auth/admin_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";


if ($_SERVER["REQUEST_METHOD"] != "POST") {

    header("Location: users.php");
    exit;

}

requireCSRFToken("users.php", "user_error");


$user_id = (int)($_POST["user_id"] ?? 0);


$stmt = $conn->prepare("

    SELECT status
    FROM users
    WHERE user_id = ?

");


$stmt->execute([$user_id]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$user) {

    $_SESSION["user_error"] = "User not found.";
    header("Location: users.php");
    exit;

}


$newStatus = $user["status"] == "active"
    ? "inactive"
    : "active";


$stmt = $conn->prepare("

    UPDATE users
    SET status = ?
    WHERE user_id = ?

");


$stmt->execute([

    $newStatus,
    $user_id

]);


$_SESSION["user_success"] =

    $newStatus == "active"
    ? "User activated successfully."
    : "User deactivated successfully.";


header("Location: users.php");

exit;