<?php

session_start();

require_once "../auth/employee_auth.php";
require_once "../config/db.php";

$page_title = "Employee Dashboard";

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($page_title); ?> | Daily Cravings Foods Inc.</title>

    <link rel="stylesheet" href="../assets/css/global.css">

    <link rel="stylesheet" href="../assets/css/employee.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>

<body>

<div class="employee-wrapper">

    <?php require_once "includes/sidebar.php"; ?>

    <div class="employee-content-wrapper">

        <?php require_once "includes/header.php"; ?>

        <main class="employee-main">

            <section class="employee-dashboard">

                <!-- DASHBOARD HEADER -->

                <div class="employee-dashboard-header">

                    <div class="employee-dashboard-title">

                        <h1>

                            <i class="fa-solid fa-chart-line"></i>

                            Employee Dashboard

                        </h1>

                        <p>

                            Welcome back! Here's your employee overview.

                        </p>

                    </div>

                </div>

                <!-- DASHBOARD CARDS -->

                <?php require_once "includes/dashboard_cards.php"; ?>


                <!-- RECENT ACTIVITY -->

                <?php require_once "includes/dashboard_activity.php"; ?>


                <!-- MY ATTENDANCE -->

                <?php require_once "includes/dashboard_attendance.php"; ?>


                <!-- MY PAYROLL -->

                <?php require_once "includes/dashboard_payroll.php"; ?>

            </section>

        </main>

    </div>

</div>

<script src="../assets/js/employee.js"></script>

</body>

</html>