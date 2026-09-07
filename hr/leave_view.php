<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";


$page_title = "Leave Request Details";


if (isset($_POST['leave_id'])) {

    $_SESSION['selected_leave'] = (int) $_POST['leave_id'];

}

if (!isset($_SESSION['selected_leave'])) {

    header("Location: leaves.php");
    exit;

}

$leave_id = (int) $_SESSION['selected_leave'];


$stmt = $conn->prepare("

    SELECT

        lr.*,

        lt.leave_type_name,

        e.employee_code,
        e.first_name,
        e.middle_name,
        e.last_name,
        e.email,
        e.phone,

        d.department_name,

        p.position_name,

        u.full_name AS approved_by_name


    FROM leave_requests lr


    LEFT JOIN leave_types lt
        ON lr.leave_type_id = lt.leave_type_id


    INNER JOIN employees e
        ON lr.employee_id = e.employee_id


    LEFT JOIN departments d
        ON e.department_id = d.department_id


    LEFT JOIN positions p
        ON e.position_id = p.position_id


    LEFT JOIN users u
        ON lr.approved_by = u.user_id


    WHERE lr.leave_id = ?

");


$stmt->execute([$leave_id]);


$leave = $stmt->fetch(PDO::FETCH_ASSOC);



if (!$leave) {

    unset($_SESSION['selected_leave']);

    header("Location: leaves.php");
    exit;

}


$total_days = 0;


if (!empty($leave['start_date']) && !empty($leave['end_date'])) {

    $start = new DateTime($leave['start_date']);
    $end   = new DateTime($leave['end_date']);

    $total_days = $start->diff($end)->days + 1;

}

?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars($page_title); ?> |
        Daily Cravings Foods Inc.
    </title>

    <link
        rel="stylesheet" href="../assets/css/global.css">

    <link
        rel="stylesheet" href="../assets/css/hr.css">

    <link
        rel="stylesheet" href="../assets/css/crud_hr.css">

    <link
        rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

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
                            Leave Request Details
                        </h1>


                        <p>View complete leave request information.</p>

                    </div>

                    <div class="crud-header-actions">

                        <a href="leaves.php" class="crud-back-btn">

                            <i class="fa-solid fa-arrow-left"></i>
                            Back to Leave Requests

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
                                    $leave['leave_type_name'] ?? "Leave Request"
                                ); ?>

                            </h2>

                            <p>Leave Request Information</p>

                        </div>

                    </div>

                    <div class="crud-info">

                        <div class="crud-info-box">

                            <span>
                                Leave Type
                            </span>

                            <strong>

                                <?= htmlspecialchars(
                                    $leave['leave_type_name'] ?? "N/A"
                                ); ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Status
                            </span>

                            <strong>

                                <span class="hr-leave-status <?= strtolower($leave['status']); ?>">

                                    <?php if ($leave['status'] === "Pending"): ?>

                                        <i class="fa-solid fa-clock"></i>

                                    <?php elseif ($leave['status'] === "Approved"): ?>

                                        <i class="fa-solid fa-circle-check"></i>

                                    <?php else: ?>

                                        <i class="fa-solid fa-circle-xmark"></i>

                                    <?php endif; ?>

                                    <?= htmlspecialchars($leave['status']); ?>

                                </span>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Start Date
                            </span>

                            <strong>

                                <?= !empty($leave['start_date'])

                                    ? date(
                                        "F d, Y",
                                        strtotime($leave['start_date'])
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

                                <?= !empty($leave['end_date'])

                                    ? date(
                                        "F d, Y",
                                        strtotime($leave['end_date'])
                                    )

                                    : "N/A";

                                ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Total Days
                            </span>

                            <strong>

                                <?= $total_days; ?>
                                Day(s)

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Date Requested
                            </span>

                            <strong>

                                <?= !empty($leave['created_at'])

                                    ? date(
                                        "F d, Y h:i A",
                                        strtotime($leave['created_at'])
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
                                Reason
                            </span>

                            <strong>

                                <?= !empty($leave['reason'])

                                    ? nl2br(
                                        htmlspecialchars($leave['reason'])
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
                                    $leave['employee_code'] ?? "N/A"
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

                                        $leave['first_name']
                                        . " "
                                        .
                                        (
                                            !empty($leave['middle_name'])
                                            ? $leave['middle_name'] . " "
                                            : ""
                                        )
                                        .
                                        $leave['last_name']

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
                                    $leave['department_name'] ?? "N/A"
                                ); ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Position
                            </span>

                            <strong>

                                <?= htmlspecialchars(
                                    $leave['position_name'] ?? "N/A"
                                ); ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Email Address
                            </span>

                            <strong>

                                <?= htmlspecialchars(
                                    $leave['email'] ?? "N/A"
                                ); ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Phone Number
                            </span>

                            <strong>

                                <?= !empty($leave['phone'])

                                    ? htmlspecialchars($leave['phone'])

                                    : "N/A";

                                ?>

                            </strong>

                        </div>

                    </div>

                    <div class="crud-section-title">

                        <h3>

                            <i class="fa-solid fa-user-check"></i>
                            Approval Information

                        </h3>

                    </div>

                    <div class="crud-info">

                        <div class="crud-info-box">

                            <span>
                                Approved By
                            </span>

                            <strong>

                                <?= !empty($leave['approved_by_name'])

                                    ? htmlspecialchars(
                                        $leave['approved_by_name']
                                    )

                                    : "Not yet approved";

                                ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Approval Date
                            </span>

                            <strong>

                                <?= !empty($leave['approved_at'])

                                    ? date(
                                        "F d, Y h:i A",
                                        strtotime($leave['approved_at'])
                                    )

                                    : "Pending";

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

                                <?= !empty($leave['remarks'])

                                    ? nl2br(
                                        htmlspecialchars($leave['remarks'])
                                    )

                                    : "No remarks";

                                ?>

                            </strong>

                        </div>

                    </div>

                    <div class="crud-actions">

                                                <form 
                            action="leave_edit.php" 
                            method="POST"
                        >

                            <?php csrfField(); ?>

                            <input
                                type="hidden"
                                name="leave_id"
                                value="<?= $leave['leave_id']; ?>"
                            >

                            <button
                                type="submit"
                                class="crud-btn crud-btn-primary"
                            >

                                <i class="fa-solid fa-pen"></i>
                                Update Leave

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