<?php

session_start();

require_once "../auth/admin_auth.php";
require_once "../config/db.php";

$page_title = "Admin Dashboard";

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($page_title); ?> | Daily Cravings Foods Inc.</title>

    <link rel="stylesheet" href="../assets/css/global.css">

    <link rel="stylesheet" href="../assets/css/admin.css?v=2">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>

<body>

<div class="admin-wrapper">

    <?php require_once "includes/sidebar.php"; ?>

    <div class="admin-content-wrapper">

        <?php require_once "includes/header.php"; ?>

        <main class="admin-main">

            <section class="admin-dashboard">

                <div class="admin-dashboard-header">

                    <div class="admin-dashboard-title">

                        <h1>
                            <i class="fa-solid fa-chart-line"></i>
                            Admin Dashboard
                        </h1>

                        <p>Welcome back! Here's today's system overview.</p>

                    </div>

                </div>

                <?php require_once "includes/dashboard_cards.php"; ?>

                <?php require_once "includes/dashboard_activity.php"; ?>

            </section>

        </main>

    </div>

</div>


<script src="../assets/js/admin.js"></script>


</body>

</html>