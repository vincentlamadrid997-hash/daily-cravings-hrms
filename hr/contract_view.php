<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";

$page_title = "Contract Details";


if (isset($_POST['contract_id'])) {

    $_SESSION['selected_contract'] = (int) $_POST['contract_id'];

}


if (!isset($_SESSION['selected_contract'])) {

    header("Location: contracts.php");
    exit;

}


$contract_id = (int) $_SESSION['selected_contract'];


$stmt = $conn->prepare("

    SELECT

        c.*,

        e.employee_code,
        e.first_name,
        e.middle_name,
        e.last_name,
        e.email,
        e.phone,
        e.hire_date,

        d.department_name,

        p.position_name


    FROM contracts c


    LEFT JOIN employees e
        ON c.employee_id = e.employee_id


    LEFT JOIN departments d
        ON e.department_id = d.department_id


    LEFT JOIN positions p
        ON e.position_id = p.position_id


    WHERE c.contract_id = ?

");


$stmt->execute([$contract_id]);


$contract = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$contract) {

    unset($_SESSION['selected_contract']);

    header("Location: contracts.php");
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
                            <i class="fa-solid fa-file-contract"></i>
                            Contract Details
                        </h1>

                        <p>View complete employee contract information.</p>

                    </div>

                    <div class="crud-header-actions">

                        <a href="contracts.php"
                           class="crud-back-btn">

                            <i class="fa-solid fa-arrow-left"></i>
                            Back to Contracts

                        </a>

                    </div>

                </div>

                <div class="crud-card">

                    <div class="crud-card-header">

                        <div class="crud-icon">

                            <i class="fa-solid fa-file-signature"></i>

                        </div>

                        <div>

                            <h2>

                                <?= htmlspecialchars(
                                    $contract['first_name'] . " " . $contract['last_name']
                                ); ?>

                            </h2>

                            <p>Employee Contract Information</p>

                        </div>

                    </div>

                    <div class="crud-section-title">

                        <h3>

                            <i class="fa-solid fa-file-contract"></i>
                            Contract Information

                        </h3>

                    </div>

                    <div class="crud-info">

                        <div class="crud-info-box">

                            <span>
                                Contract Type
                            </span>

                            <strong>

                                <?= htmlspecialchars(
                                    $contract['contract_type']
                                ); ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Contract Status
                            </span>

                            <strong>

                                <span class="hr-emp-status <?= strtolower($contract['status']); ?>">

                                    <?php if ($contract['status'] === "Active"): ?>

                                        <i class="fa-solid fa-circle-check"></i>

                                    <?php else: ?>

                                        <i class="fa-solid fa-circle-xmark"></i>

                                    <?php endif; ?>

                                    <?= htmlspecialchars($contract['status']); ?>

                                </span>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Start Date
                            </span>

                            <strong>

                                <?= !empty($contract['start_date'])
                                    ? date(
                                        "F d, Y",
                                        strtotime($contract['start_date'])
                                    )
                                    : "N/A";
                                ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                End Date
                            </span>

                            <strong>

                                <?= !empty($contract['end_date'])
                                    ? date(
                                        "F d, Y",
                                        strtotime($contract['end_date'])
                                    )
                                    : "N/A";
                                ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Monthly Salary
                            </span>

                            <strong>

                                ₱<?= number_format(
                                    $contract['salary'],
                                    2
                                ); ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Created Date
                            </span>

                            <strong>

                                <?= !empty($contract['created_at'])
                                    ? date(
                                        "F d, Y h:i A",
                                        strtotime($contract['created_at'])
                                    )
                                    : "N/A";
                                ?>

                            </strong>

                        </div>


                    </div>

                    <div class="crud-section-title">

                        <h3>

                            <i class="fa-solid fa-user"></i>
                            Employee Information

                        </h3>

                    </div>

                    <div class="crud-info">

                        <div class="crud-info-box">

                            <span>
                                Employee Code
                            </span>

                            <strong>

                                <?= htmlspecialchars(
                                    $contract['employee_code']
                                ); ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Full Name
                            </span>

                            <strong>

                                <?= htmlspecialchars(
                                    $contract['first_name'] . " " .
                                    $contract['middle_name'] . " " .
                                    $contract['last_name']
                                ); ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Email Address
                            </span>

                            <strong>

                                <?= htmlspecialchars(
                                    $contract['email']
                                ); ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Phone Number
                            </span>

                            <strong>

                                <?= htmlspecialchars(
                                    $contract['phone'] ?: "N/A"
                                ); ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Department
                            </span>

                            <strong>

                                <?= htmlspecialchars(
                                    $contract['department_name'] ?? "N/A"
                                ); ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Position
                            </span>

                            <strong>

                                <?= htmlspecialchars(
                                    $contract['position_name'] ?? "N/A"
                                ); ?>

                            </strong>

                        </div>

                    </div>

                    <div class="crud-section-title">

                        <h3>

                            <i class="fa-solid fa-paperclip"></i>
                            Contract Document

                        </h3>

                    </div>

                    <div class="crud-info">

                        <div class="crud-info-box"
                             style="grid-column:1/-1;">

                            <span>
                                Uploaded File
                            </span>

                            <strong>

                                <?php if (!empty($contract['file'])): ?>

                                    <a href="../uploads/contracts/<?= htmlspecialchars($contract['file']); ?>"
                                       target="_blank"
                                       class="crud-file-btn">

                                        <i class="fa-solid fa-file-pdf"></i>
                                        View Contract File
                                    </a>

                                <?php else: ?>

                                    No file uploaded.

                                <?php endif; ?>

                            </strong>

                        </div>

                    </div>

                    <div class="crud-actions">

                                                <form action="contract_edit.php"
                              method="POST">

                            <?php csrfField(); ?>

                            <input type="hidden"
                                   name="contract_id"
                                   value="<?= $contract['contract_id']; ?>">

                            <button type="submit"
                                    class="crud-btn crud-btn-primary">

                                <i class="fa-solid fa-pen"></i>
                                Edit Contract

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