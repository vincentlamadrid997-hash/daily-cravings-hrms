<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";

$page_title = "Applicant View";


if (isset($_POST['application_id'])) {

    $_SESSION['selected_application'] = $_POST['application_id'];

}


if (!isset($_SESSION['selected_application'])) {

    header("Location: applicants.php");
    exit;

}


$application_id = $_SESSION['selected_application'];



$stmt = $conn->prepare("
    SELECT 
        a.*,
        j.job_title
    FROM applications a
    LEFT JOIN job_postings j 
        ON a.job_id = j.job_id
    WHERE a.application_id = ?
");

$stmt->execute([$application_id]);

$applicant = $stmt->fetch(PDO::FETCH_ASSOC);



if (!$applicant) {

    unset($_SESSION['selected_application']);

    header("Location: applicants.php");
    exit;

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Applicant View | Daily Cravings Foods Inc.
    </title>


    <link rel="stylesheet" href="../assets/css/global.css">

    <link rel="stylesheet" href="../assets/css/hr.css">

    <link rel="stylesheet" href="../assets/css/crud_hr.css">


    <link 
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    >

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
                            <i class="fa-solid fa-user"></i>
                            Applicant Details
                        </h1>

                        <p>View applicant information and recruitment details.</p>

                    </div>

                    <div class="crud-header-actions">

                        <a href="applicants.php" class="crud-back-btn">

                            <i class="fa-solid fa-arrow-left"></i>
                            Back to Applicants

                        </a>

                    </div>

                </div>

                <div class="crud-card">

                    <div class="crud-card-header">

                        <div class="crud-icon">

                            <i class="fa-solid fa-id-card"></i>

                        </div>

                        <div>

                            <h2>
                                <?= htmlspecialchars(
                                    $applicant['first_name'] . " " . $applicant['last_name']
                                ); ?>
                            </h2>

                            <p>Applicant Profile Information</p>

                        </div>

                    </div>

                    <div class="crud-info">

                        <div class="crud-info-box">

                            <span>
                                Position Applied
                            </span>

                            <strong>

                                <?= htmlspecialchars(
                                    $applicant['job_title'] ?? "N/A"
                                ); ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Email Address
                            </span>

                            <strong>

                                <?= htmlspecialchars(
                                    $applicant['email']
                                ); ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Phone Number
                            </span>

                            <strong>

                                <?= htmlspecialchars(
                                    $applicant['phone']
                                ); ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Birthdate
                            </span>

                            <strong>

                                <?= !empty($applicant['birthdate'])
                                    ? date(
                                        "F d, Y",
                                        strtotime($applicant['birthdate'])
                                    )
                                    : "N/A";
                                ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Gender
                            </span>

                            <strong>

                                <?= htmlspecialchars(
                                    $applicant['gender'] ?? "N/A"
                                ); ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Civil Status
                            </span>

                            <strong>

                                <?= htmlspecialchars(
                                    $applicant['civil_status'] ?? "N/A"
                                ); ?>

                            </strong>

                        </div>

                        <div class="crud-info-box crud-full">

                            <span>
                                Address
                            </span>

                            <strong>

                                <?= htmlspecialchars(
                                    $applicant['address'] ?? "N/A"
                                ); ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Expected Salary
                            </span>

                            <strong>

                                ₱<?= number_format(
                                    $applicant['expected_salary'] ?? 0,
                                    2
                                ); ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Available Date
                            </span>

                            <strong>

                                <?= !empty($applicant['available_date'])
                                    ? date(
                                        "F d, Y",
                                        strtotime($applicant['available_date'])
                                    )
                                    : "N/A";
                                ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Application Status
                            </span>

                            <strong>

                                <?= htmlspecialchars(
                                    $applicant['status']
                                ); ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Date Applied
                            </span>

                            <strong>

                                <?= date(
                                    "F d, Y",
                                    strtotime($applicant['created_at'])
                                ); ?>

                            </strong>

                        </div>

                    </div>

                    <div class="crud-actions">

                        <a href="applicant_update.php"
                           class="crud-btn crud-btn-warning">

                            <i class="fa-solid fa-pen"></i>
                            Update Status

                        </a>

                        <a href="applicant_interview.php"
                           class="crud-btn crud-btn-primary">

                            <i class="fa-solid fa-calendar-check"></i>
                            Schedule Interview

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