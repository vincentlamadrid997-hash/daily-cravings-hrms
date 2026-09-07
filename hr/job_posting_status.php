<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";

requireCSRFToken("job_postings.php", "job_error");

if (!isset($_POST['job_id'])) {

    $_SESSION['job_error'] = "Invalid job posting selected.";

    header("Location: job_postings.php");
    exit;
}


$job_id = (int) $_POST['job_id'];


$stmt = $conn->prepare("
    SELECT 
        job_title,
        status,
        closing_date
    FROM job_postings
    WHERE job_id = ?
");

$stmt->execute([$job_id]);

$job = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$job) {

    $_SESSION['job_error'] = "Job posting not found.";

    header("Location: job_postings.php");
    exit;
}


$current_status = $job['status'];


if ($current_status === "Open") {

    $new_status = "Closed";

    $message = "Job posting has been closed successfully.";

} else {


    $new_closing_date = trim($_POST['new_closing_date'] ?? "");

    if (
        empty($new_closing_date) ||
        $new_closing_date < date("Y-m-d")
    ) {

        $_SESSION['job_error'] =
            "Please select a valid closing date (today or later) to reopen this job posting.";

        header("Location: job_postings.php");
        exit;
    }


    $new_status = "Open";

    $message = "Job posting has been reopened successfully.";
}


if ($new_status === "Open") {

    $update = $conn->prepare("
        UPDATE job_postings
        SET status = ?, closing_date = ?
        WHERE job_id = ?
    ");

    $update->execute([
        $new_status,
        $new_closing_date,
        $job_id
    ]);

} else {

    $update = $conn->prepare("
        UPDATE job_postings
        SET status = ?
        WHERE job_id = ?
    ");

    $update->execute([
        $new_status,
        $job_id
    ]);

}


$_SESSION['job_success'] = $message;


header("Location: job_postings.php");
exit;