<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";

$page_title = "HR Dashboard";

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($page_title); ?> | Daily Cravings Foods Inc.</title>

    <link rel="stylesheet" href="../assets/css/global.css">

    <link rel="stylesheet" href="../assets/css/hr.css?v=3"> 

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>

<body>

<div class="hr-wrapper">

    <?php require_once "includes/sidebar.php"; ?>

    <div class="hr-content-wrapper">

        <?php require_once "includes/header.php"; ?>

        <main class="hr-main">

            <section class="hr-dashboard">

                <div class="hr-dashboard-header">

                    <div class="hr-dashboard-title">

                        <h1>

                            <i class="fa-solid fa-chart-line"></i>

                            HR Dashboard

                        </h1>

                        <p>

                            Welcome! Here's today's Human Resource overview.

                        </p>

                    </div>

                </div>

                <?php require_once "includes/dashboard_cards.php"; ?>

                <?php require_once "includes/dashboard_activity.php"; ?>

                <?php require_once "includes/dashboard_attendance.php"; ?>

                <?php require_once "includes/dashboard_interviews.php"; ?>

            </section>

        </main>

    </div>

</div>

<script src="../assets/js/hr.js"></script>

</body>

</html>

