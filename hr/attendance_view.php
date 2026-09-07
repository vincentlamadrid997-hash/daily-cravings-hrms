<?php
session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";

$page_title = "Attendance Details";

if (isset($_POST['attendance_id'])) {
    $_SESSION['selected_attendance'] = (int) $_POST['attendance_id'];
}

if (!isset($_SESSION['selected_attendance'])) {
    header("Location: attendance.php");
    exit;
}

$attendance_id = (int) $_SESSION['selected_attendance'];

$stmt = $conn->prepare("
    SELECT
        a.*,
        e.employee_code,
        e.first_name,
        e.middle_name,
        e.last_name,
        e.email,
        e.phone,
        d.department_name,
        p.position_name
    FROM attendance a
    INNER JOIN employees e
        ON a.employee_id = e.employee_id
    LEFT JOIN departments d
        ON e.department_id = d.department_id
    LEFT JOIN positions p
        ON e.position_id = p.position_id
    WHERE a.attendance_id = ?
");

$stmt->execute([$attendance_id]);
$attendance = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$attendance) {
    unset($_SESSION['selected_attendance']);
    header("Location: attendance.php");
    exit;
}

$logStmt = $conn->prepare("
    SELECT
        log_id,
        action,
        log_time
    FROM attendance_logs
    WHERE attendance_id = ?
    ORDER BY log_time ASC
");

$logStmt->execute([$attendance_id]);
$attendance_logs = $logStmt->fetchAll(PDO::FETCH_ASSOC);

$total_hours = "N/A";

if (!empty($attendance['time_in']) && !empty($attendance['time_out'])) {
    $time_in = strtotime($attendance['time_in']);
    $time_out = strtotime($attendance['time_out']);

    $difference = $time_out - $time_in;

    if ($difference > 0) {
        $hours = floor($difference / 3600);
        $minutes = floor(($difference % 3600) / 60);

        $total_hours = $hours . "h " . $minutes . "m";
    } else {
        $total_hours = "Invalid Time";
    }
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
                        <i class="fa-solid fa-clock"></i>
                        Attendance Details
                    </h1>

                    <p>View complete employee attendance information.</p>

                </div>

                <div class="crud-header-actions">

                    <a
                        href="attendance_logs.php"
                        class="crud-back-btn"
                    >
                        <i class="fa-solid fa-arrow-left"></i>
                        Back to Attendance History
                    </a>

                </div>

            </div>

            <div class="crud-card">

                <div class="crud-card-header">

                    <div class="crud-icon">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>

                    <div>

                        <h2>Attendance Record</h2>

                        <p>Daily attendance information</p>

                    </div>

                </div>

                <div class="crud-section-title">

                    <h3>
                        <i class="fa-solid fa-clock"></i>
                        Attendance Information
                    </h3>

                </div>

                <div class="crud-info">

                    <div class="crud-info-box">

                        <span>
                            Attendance Date
                        </span>

                        <strong>
                            <?= !empty($attendance['attendance_date'])

                                ? date(
                                    "F d, Y",
                                    strtotime($attendance['attendance_date'])
                                )

                                : "N/A";

                            ?>
                        </strong>

                    </div>

                    <div class="crud-info-box">

                        <span>
                            Status
                        </span>

                        <strong>

                            <span class="hr-attendance-status <?= strtolower($attendance['status']); ?>">

                                <?php if ($attendance['status'] == "Present"): ?>

                                    <i class="fa-solid fa-circle-check"></i>

                                <?php elseif ($attendance['status'] == "Late"): ?>

                                    <i class="fa-solid fa-clock"></i>

                                <?php elseif ($attendance['status'] == "Leave"): ?>

                                    <i class="fa-solid fa-calendar-xmark"></i>

                                <?php else: ?>

                                    <i class="fa-solid fa-circle-xmark"></i>

                                <?php endif; ?>


                                <?= htmlspecialchars($attendance['status']); ?>

                            </span>

                        </strong>

                    </div>

                    <div class="crud-info-box">

                        <span>
                            Time In
                        </span>

                        <strong>
                            <?= !empty($attendance['time_in'])

                                ? date(
                                    "h:i A",
                                    strtotime($attendance['time_in'])
                                )

                                : "No Time In";

                            ?>
                        </strong>

                    </div>

                    <div class="crud-info-box">

                        <span>
                            Time Out
                        </span>

                        <strong>
                            <?= !empty($attendance['time_out'])

                                ? date(
                                    "h:i A",
                                    strtotime($attendance['time_out'])
                                )

                                : "No Time Out";

                            ?>
                        </strong>

                    </div>

                    <div class="crud-info-box">

                        <span>
                            Total Hours
                        </span>

                        <strong>
                            <?= $total_hours; ?>
                        </strong>

                    </div>

                    <div class="crud-info-box">

                        <span>
                            Created Date
                        </span>

                        <strong>
                            <?= !empty($attendance['created_at'])

                                ? date(
                                    "F d, Y h:i A",
                                    strtotime($attendance['created_at'])
                                )

                                : "N/A";

                            ?>
                        </strong>

                    </div>

                    <div
                        class="crud-info-box"
                        style="grid-column:1/-1;"
                    >

                        <span>
                            Remarks
                        </span>

                        <strong>

                            <?= !empty($attendance['remarks'])

                                ? nl2br(
                                    htmlspecialchars($attendance['remarks'])
                                )

                                : "No remarks";

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
                                $attendance['employee_code'] ?? "N/A"
                            ); ?>
                        </strong>

                    </div>

                    <div class="crud-info-box">

                        <span>
                            Employee Name
                        </span>

                        <strong>

                            <?= htmlspecialchars(

                                trim(
                                    $attendance['first_name']
                                    . " "
                                    .
                                    (
                                        !empty($attendance['middle_name'])
                                            ? $attendance['middle_name'] . " "
                                            : ""
                                    )
                                    .
                                    $attendance['last_name']
                                )

                            ); ?>

                        </strong>

                    </div>


                    <div class="crud-info-box">

                        <span>
                            Department
                        </span>

                        <strong>
                            <?= htmlspecialchars(
                                $attendance['department_name'] ?? "N/A"
                            ); ?>
                        </strong>

                    </div>

                    <div class="crud-info-box">

                        <span>
                            Position
                        </span>

                        <strong>
                            <?= htmlspecialchars(
                                $attendance['position_name'] ?? "N/A"
                            ); ?>
                        </strong>

                    </div>

                    <div class="crud-info-box">

                        <span>
                            Email Address
                        </span>

                        <strong>
                            <?= htmlspecialchars(
                                $attendance['email'] ?? "N/A"
                            ); ?>
                        </strong>

                    </div>

                    <div class="crud-info-box">

                        <span>
                            Phone Number
                        </span>

                        <strong>

                            <?= !empty($attendance['phone'])

                                ? htmlspecialchars(
                                    $attendance['phone']
                                )

                                : "N/A";

                            ?>

                        </strong>

                    </div>

                </div>

                <div class="crud-actions">

                                        <form
                        action="attendance_edit.php"
                        method="POST"
                    >

                        <?php csrfField(); ?>

                        <input
                            type="hidden"
                            name="attendance_id"
                            value="<?= $attendance['attendance_id']; ?>"
                        >

                        <button
                            type="submit"
                            class="crud-btn crud-btn-primary"
                        >

                            <i class="fa-solid fa-pen"></i>
                            Update Attendance

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