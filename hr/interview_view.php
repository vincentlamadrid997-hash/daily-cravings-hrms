<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";

$page_title = "Interview Details";


if (isset($_POST['interview_id'])) {

    $_SESSION['selected_interview'] = $_POST['interview_id'];

}


if (!isset($_SESSION['selected_interview'])) {

    header("Location: interviews.php");
    exit;

}


$interview_id = $_SESSION['selected_interview'];


$stmt = $conn->prepare("
    SELECT 
        i.*,

        a.first_name,
        a.middle_name,
        a.last_name,
        a.email,
        a.phone,

        jp.job_title

    FROM interviews i

    LEFT JOIN applications a
        ON i.application_id = a.application_id

    LEFT JOIN job_postings jp
        ON a.job_id = jp.job_id

    WHERE i.interview_id = ?
");


$stmt->execute([
    $interview_id
]);


$interview = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$interview) {

    unset($_SESSION['selected_interview']);

    header("Location: interviews.php");
    exit;

}

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars($page_title); ?> 
        | Daily Cravings Foods Inc.
    </title>


    <link rel="stylesheet" href="../assets/css/global.css">

    <link rel="stylesheet" href="../assets/css/hr.css">

    <link rel="stylesheet" href="../assets/css/crud_hr.css">

    <link rel="stylesheet" 
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>


<body>

<div class="hr-wrapper">

    <?php require_once "includes/sidebar.php"; ?>

    <div class="hr-content-wrapper">

        <?php require_once "includes/header.php"; ?>

        <main class="hr-main">

            <section class="crud-page">

                <div class="crud-header">

                    <div class="crud-title">

                        <h1>
                            <i class="fa-solid fa-calendar-check"></i>
                            Interview Details
                        </h1>

                        <p>View applicant interview information.</p>

                    </div>

                    <div class="crud-header-actions">

                        <a href="interviews.php" class="crud-back-btn">

                            <i class="fa-solid fa-arrow-left"></i>
                            Back to Interviews

                        </a>

                    </div>

                </div>

                <div class="crud-card">

                    <div class="crud-card-header">

                        <div class="crud-icon">

                            <i class="fa-solid fa-calendar-days"></i>

                        </div>

                        <div>

                            <h2>

                                <?= htmlspecialchars(
                                    $interview['first_name'] . " " .
                                    $interview['last_name']
                                ); ?>

                            </h2>

                            <p>Interview Schedule Information</p>

                        </div>

                    </div>

                    <div class="crud-info">

                        <div class="crud-info-box">

                            <span>
                                Position Applied
                            </span>

                            <strong>
                                <?= htmlspecialchars(
                                    $interview['job_title'] ?? "N/A"
                                ); ?>
                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Email Address
                            </span>

                            <strong>
                                <?= htmlspecialchars(
                                    $interview['email']
                                ); ?>
                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Phone Number
                            </span>

                            <strong>
                                <?= htmlspecialchars(
                                    $interview['phone']
                                ); ?>
                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Interview Date
                            </span>

                            <strong>
                                <?= date(
                                    "F d, Y",
                                    strtotime($interview['interview_date'])
                                ); ?>
                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Interview Time
                            </span>

                            <strong>
                                <?= date(
                                    "h:i A",
                                    strtotime($interview['interview_time'])
                                ); ?>
                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Interview Stage
                            </span>

                            <strong>
                                <?= htmlspecialchars(
                                    $interview['stage']
                                ); ?>
                                Interview
                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Interviewer
                            </span>

                            <strong>
                                <?= htmlspecialchars(
                                    $interview['interviewer']
                                ); ?>
                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Interview Status
                            </span>

                            <strong>
                                <?= htmlspecialchars(
                                    $interview['status']
                                ); ?>
                            </strong>

                        </div>

                        <div class="crud-info-box crud-full">

                            <span>
                                Created Date
                            </span>

                            <strong>

                                <?= date(
                                    "F d, Y",
                                    strtotime($interview['created_at'])
                                ); ?>

                            </strong>

                        </div>

                    </div>

                    <div class="crud-actions">

                        <a href="interview_update.php" 
                           class="crud-btn crud-btn-primary">

                            <i class="fa-solid fa-pen"></i>
                            Update Interview

                        </a>

                    </div>

                </div>

            </section>

        </main>

    </div>

</div>


<script src="../assets/js/hr.js"></script>


</body>

</html>