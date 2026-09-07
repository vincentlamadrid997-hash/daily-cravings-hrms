<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";
require_once "../config/permissions.php";

requirePermission($conn, 'view_position_details', 'positions.php');

$page_title = "Position Details";

if (isset($_POST['position_id'])) {

    $_SESSION['selected_position'] = (int) $_POST['position_id'];

}

if (!isset($_SESSION['selected_position'])) {

    header("Location: positions.php");
    exit;

}

$position_id = (int) $_SESSION['selected_position'];


$stmt = $conn->prepare("

    SELECT

        p.*,

        d.department_name,

        COUNT(e.employee_id) AS employee_count

    FROM positions p

    LEFT JOIN departments d

        ON p.department_id = d.department_id

    LEFT JOIN employees e

        ON p.position_id = e.position_id

    WHERE p.position_id = ?

    GROUP BY p.position_id

");

$stmt->execute([
    $position_id
]);

$position = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$position) {

    unset($_SESSION['selected_position']);

    header("Location: positions.php");
    exit;

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($page_title); ?> | Daily Cravings Foods Inc.</title>

    <link rel="stylesheet" href="../assets/css/global.css">

    <link rel="stylesheet" href="../assets/css/hr.css">

    <link rel="stylesheet" href="../assets/css/crud_hr.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

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

                            <i class="fa-solid fa-briefcase"></i>
                            Position Details

                        </h1>

                        <p>View complete position information.</p>

                    </div>

                    <div class="crud-header-actions">

                        
                            href="positions.php"
                            class="crud-back-btn"
                        >

                            <i class="fa-solid fa-arrow-left"></i>
                            Back to Positions

                        </a>

                    </div>

                </div>

                <div class="crud-card">

                    <div class="crud-card-header">

                        <div class="crud-icon">

                            <i class="fa-solid fa-briefcase"></i>

                        </div>

                        <div>

                            <h2>

                                <?= htmlspecialchars($position['position_name']); ?>

                            </h2>

                            <p>Position Information</p>

                        </div>

                    </div>

                    <div class="crud-info">

                        <div class="crud-info-box">

                            <span>
                                Position Name
                            </span>

                            <strong>

                                <?= htmlspecialchars($position['position_name']); ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Department
                            </span>

                            <strong>

                                <?= htmlspecialchars(
                                    $position['department_name'] ?? "N/A"
                                ); ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Status
                            </span>

                            <strong>

                                <span class="hr-emp-status <?= strtolower($position['status']); ?>">

                                    <?= htmlspecialchars($position['status']); ?>

                                </span>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Total Employees
                            </span>

                            <strong>

                                <?= $position['employee_count']; ?>

                                Employee<?= $position['employee_count'] != 1 ? "s" : ""; ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Created Date
                            </span>

                            <strong>

                                <?= date(
                                    "F d, Y",
                                    strtotime($position['created_at'])
                                ); ?>

                            </strong>

                        </div>

                        <div
                            class="crud-info-box"
                            style="grid-column:1/-1;"
                        >

                            <span>
                                Description
                            </span>

                            <strong>

                                <?= !empty($position['description'])

                                    ? nl2br(htmlspecialchars($position['description']))

                                    : "N/A"; ?>

                            </strong>

                        </div>

                    </div>

                    <div class="crud-section-title">

                        <h3>

                            <i class="fa-solid fa-briefcase"></i>
                            Position Summary

                        </h3>

                    </div>

                    <div class="crud-info">

                        <div class="crud-info-box">

                            <span>
                                Position ID
                            </span>

                            <strong>

                                #<?= $position['position_id']; ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Record Created
                            </span>

                            <strong>

                                <?= date(
                                    "F d, Y h:i A",
                                    strtotime($position['created_at'])
                                ); ?>

                            </strong>

                        </div>

                    </div>

                    <div class="crud-actions">

                        <form
                            action="position_edit.php"
                            method="POST"
                        >

                            <?php csrfField(); ?>

                            <input
                                type="hidden"
                                name="position_id"
                                value="<?= $position['position_id']; ?>"
                            >

                            <button
                                type="submit"
                                class="crud-btn crud-btn-primary"
                            >

                                <i class="fa-solid fa-pen"></i>
                                Edit Position

                            </button>

                        </form>

                    </div>

                </div>

            </section>

        </main>

    </div>

</div>

<script src="../assets/js/hr.js"></script>

</body>

</html>