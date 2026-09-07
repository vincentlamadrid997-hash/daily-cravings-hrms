<?php
session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";

$page_title = "Resignation Details";


if (isset($_POST['resignation_id'])) {
    $_SESSION['selected_resignation'] = (int) $_POST['resignation_id'];
}

if (!isset($_SESSION['selected_resignation'])) {
    header("Location: resignations.php");
    exit;
}

$resignation_id = (int) $_SESSION['selected_resignation'];


$stmt = $conn->prepare("
    SELECT
        r.resignation_id,
        r.reason,
        r.resignation_date,
        r.last_working_day,
        r.status,
        r.remarks,
        r.approved_by,
        r.approved_date,
        r.created_at,

        e.employee_code,
        e.first_name,
        e.middle_name,
        e.last_name,
        e.email,
        e.phone,

        d.department_name,
        p.position_name,

        approver.full_name AS approved_by_name

    FROM resignations r

    INNER JOIN employees e
        ON r.employee_id = e.employee_id

    LEFT JOIN departments d
        ON e.department_id = d.department_id

    LEFT JOIN positions p
        ON e.position_id = p.position_id

    LEFT JOIN users approver
        ON r.approved_by = approver.user_id

    WHERE r.resignation_id = ?
");

$stmt->execute([$resignation_id]);
$resignation = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$resignation) {
    unset($_SESSION['selected_resignation']);

    header("Location: resignations.php");
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
                            <i class="fa-solid fa-person-walking-arrow-right"></i>
                            Resignation Details
                        </h1>

                        <p>View complete employee resignation information.</p>

                    </div>

                    <div class="crud-header-actions">

                        <a
                            href="resignations.php"
                            class="crud-back-btn"
                        >
                            <i class="fa-solid fa-arrow-left"></i>
                            Back to Resignations
                        </a>

                    </div>

                </div>

                <div class="crud-card">

                    <div class="crud-card-header">

                        <div class="crud-icon">
                            <i class="fa-solid fa-person-circle-exclamation"></i>
                        </div>

                        <div>

                            <h2>
                                <?= htmlspecialchars(
                                    trim(
                                        $resignation['first_name'] . " " .
                                        (!empty($resignation['middle_name'])
                                            ? $resignation['middle_name'] . " "
                                            : "") .
                                        $resignation['last_name']
                                    )
                                ); ?>
                            </h2>

                            <p>Employee Resignation Information</p>

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
                                <?= htmlspecialchars($resignation['employee_code']); ?>
                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Full Name
                            </span>

                            <strong>
                                <?= htmlspecialchars(
                                    trim(
                                        $resignation['first_name'] . " " .
                                        (!empty($resignation['middle_name'])
                                            ? $resignation['middle_name'] . " "
                                            : "") .
                                        $resignation['last_name']
                                    )
                                ); ?>
                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Department
                            </span>

                            <strong>
                                <?= htmlspecialchars($resignation['department_name'] ?? "N/A"); ?>
                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Position
                            </span>

                            <strong>
                                <?= htmlspecialchars($resignation['position_name'] ?? "N/A"); ?>
                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Email Address
                            </span>

                            <strong>
                                <?= htmlspecialchars($resignation['email']); ?>
                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Phone Number
                            </span>

                            <strong>
                                <?= htmlspecialchars($resignation['phone'] ?: "N/A"); ?>
                            </strong>

                        </div>

                    </div>

                    <div class="crud-section-title">

                        <h3>
                            <i class="fa-solid fa-file-signature"></i>
                            Resignation Information
                        </h3>

                    </div>

                    <div class="crud-info">

                        <div class="crud-info-box">

                            <span>
                                Resignation Date
                            </span>

                            <strong>
                                <?= !empty($resignation['resignation_date'])
                                    ? date("F d, Y", strtotime($resignation['resignation_date']))
                                    : "N/A"; ?>
                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Last Working Day
                            </span>

                            <strong>
                                <?= !empty($resignation['last_working_day'])
                                    ? date("F d, Y", strtotime($resignation['last_working_day']))
                                    : "N/A"; ?>
                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Current Status
                            </span>

                            <strong>

                                <span class="hr-res-status <?= strtolower($resignation['status']); ?>">

                                    <?php if ($resignation['status'] === "Pending"): ?>

                                        <i class="fa-solid fa-clock"></i>

                                    <?php elseif ($resignation['status'] === "Approved"): ?>

                                        <i class="fa-solid fa-circle-check"></i>

                                    <?php else: ?>

                                        <i class="fa-solid fa-circle-xmark"></i>

                                    <?php endif; ?>

                                    <?= htmlspecialchars($resignation['status']); ?>

                                </span>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Approved By
                            </span>

                            <strong>
                                <?= htmlspecialchars($resignation['approved_by_name'] ?? "Not Yet Assigned"); ?>
                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Approval Date
                            </span>

                            <strong>
                                <?= !empty($resignation['approved_date'])
                                    ? date("F d, Y h:i A", strtotime($resignation['approved_date']))
                                    : "Not Yet Approved"; ?>
                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Submitted On
                            </span>

                            <strong>
                                <?= !empty($resignation['created_at'])
                                    ? date("F d, Y h:i A", strtotime($resignation['created_at']))
                                    : "N/A"; ?>
                            </strong>

                        </div>

                        <div class="crud-info-box full">

                            <span>
                                Reason
                            </span>

                            <strong>
                                <?= !empty($resignation['reason'])
                                    ? nl2br(htmlspecialchars($resignation['reason']))
                                    : "No reason provided."; ?>
                            </strong>

                        </div>

                        <div class="crud-info-box full">

                            <span>
                                HR Remarks
                            </span>

                            <strong>
                                <?= !empty($resignation['remarks'])
                                    ? nl2br(htmlspecialchars($resignation['remarks']))
                                    : "No remarks available."; ?>
                            </strong>

                        </div>

                    </div>

                    <div class="crud-actions">

                                                <form
                            action="resignation_edit.php"
                            method="POST"
                        >

                            <?php csrfField(); ?>

                            <input
                                type="hidden"
                                name="resignation_id"
                                value="<?= $resignation['resignation_id']; ?>"
                            >

                            <button
                                type="submit"
                                class="crud-btn crud-btn-primary"
                            >
                                <i class="fa-solid fa-pen"></i>
                                Edit Resignation
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