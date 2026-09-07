<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";
require_once "../config/permissions.php";

requirePermission($conn, 'view_department_details', 'departments.php');

$page_title = "Department Details";


if (isset($_POST['department_id'])) {

    $_SESSION['selected_department'] = (int) $_POST['department_id'];

}


if (!isset($_SESSION['selected_department'])) {

    header("Location: departments.php");
    exit;

}


$department_id = (int) $_SESSION['selected_department'];


$stmt = $conn->prepare("
    SELECT 
        d.*,
        COUNT(e.employee_id) AS employee_count
    FROM departments d
    LEFT JOIN employees e 
        ON d.department_id = e.department_id
    WHERE d.department_id = ?
    GROUP BY d.department_id
");


$stmt->execute([
    $department_id
]);


$department = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$department) {

    unset($_SESSION['selected_department']);

    header("Location: departments.php");
    exit;

}

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars($page_title); ?> | Daily Cravings Foods Inc.
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
                            <i class="fa-solid fa-building"></i>
                            Department Details
                        </h1>

                        <p>View complete department information.</p>

                    </div>

                    <div class="crud-header-actions">

                        <a href="departments.php"
                           class="crud-back-btn">

                            <i class="fa-solid fa-arrow-left"></i>
                            Back to Departments

                        </a>

                    </div>

                </div>

                <div class="crud-card">

                    <div class="crud-card-header">

                        <div class="crud-icon">

                            <i class="fa-solid fa-building"></i>

                        </div>

                        <div>

                            <h2>
                                <?= htmlspecialchars($department['department_name']); ?>
                            </h2>

                            <p>Department Information</p>

                        </div>

                    </div>

                    <div class="crud-info">

                        <div class="crud-info-box">

                            <span>
                                Department Name
                            </span>

                            <strong>
                                <?= htmlspecialchars($department['department_name']); ?>
                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Status
                            </span>

                            <strong>

                                <span class="hr-emp-status <?= strtolower($department['status']); ?>">

                                    <?= htmlspecialchars($department['status']); ?>

                                </span>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Total Employees
                            </span>

                            <strong>

                                <?= $department['employee_count']; ?>

                                Employee<?= $department['employee_count'] != 1 ? "s" : ""; ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Created Date
                            </span>

                            <strong>

                                <?= date(
                                    "F d, Y",
                                    strtotime($department['created_at'])
                                ); ?>

                            </strong>

                        </div>

                        <div class="crud-info-box"
                             style="grid-column:1/-1;">

                            <span>
                                Description
                            </span>

                            <strong>

                                <?= !empty($department['description'])

                                    ? nl2br(htmlspecialchars($department['description']))

                                    : "N/A";
                                ?>

                            </strong>

                        </div>

                    </div>

                    <div class="crud-section-title">

                        <h3>

                            <i class="fa-solid fa-building-circle-check"></i>
                            Department Summary

                        </h3>

                    </div>

                    <div class="crud-info">

                        <div class="crud-info-box">

                            <span>
                                Department ID
                            </span>

                            <strong>
                                #<?= $department['department_id']; ?>
                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Record Created
                            </span>

                            <strong>

                                <?= date(
                                    "F d, Y h:i A",
                                    strtotime($department['created_at'])
                                ); ?>

                            </strong>

                        </div>

                    </div>

                    <div class="crud-actions">

                        <form action="department_edit.php"
                              method="POST">

                            <?php csrfField(); ?>

                            <input type="hidden"
                                   name="department_id"
                                   value="<?= $department['department_id']; ?>">

                            <button type="submit"
                                    class="crud-btn crud-btn-primary">

                                <i class="fa-solid fa-pen"></i>
                                Edit Department

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