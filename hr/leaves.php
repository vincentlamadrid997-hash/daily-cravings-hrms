<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";


$page_title = "Leave Requests";


$success = $_SESSION['leave_success'] ?? "";
$error   = $_SESSION['leave_error'] ?? "";

unset($_SESSION['leave_success']);
unset($_SESSION['leave_error']);


$totalLeaves = $conn->query("
    SELECT COUNT(*)
    FROM leave_requests
")->fetchColumn();


$pendingLeaves = $conn->query("
    SELECT COUNT(*)
    FROM leave_requests
    WHERE status = 'Pending'
")->fetchColumn();


$approvedLeaves = $conn->query("
    SELECT COUNT(*)
    FROM leave_requests
    WHERE status = 'Approved'
")->fetchColumn();


$rejectedLeaves = $conn->query("
    SELECT COUNT(*)
    FROM leave_requests
    WHERE status = 'Rejected'
")->fetchColumn();


$search = trim($_GET['search'] ?? "");


$sql = "

SELECT

    lr.leave_id,
    lr.employee_id,
    lr.leave_type_id,
    lr.start_date,
    lr.end_date,
    lr.reason,
    lr.status,
    lr.approved_by,
    lr.approved_at,
    lr.remarks,
    lr.created_at,

    e.employee_code,
    e.first_name,
    e.middle_name,
    e.last_name,

    lt.leave_type_name

FROM leave_requests lr

LEFT JOIN employees e
    ON lr.employee_id = e.employee_id

LEFT JOIN leave_types lt
    ON lr.leave_type_id = lt.leave_type_id

WHERE 1

";


$params = [];


if (!empty($search)) {

    $sql .= "

    AND (

        e.employee_code LIKE ?
        OR e.first_name LIKE ?
        OR e.last_name LIKE ?
        OR lt.leave_type_name LIKE ?
        OR lr.status LIKE ?

    )

    ";


    $keyword = "%" . $search . "%";


    $params = [
        $keyword,
        $keyword,
        $keyword,
        $keyword,
        $keyword
    ];

}


$sql .= "

ORDER BY lr.created_at DESC

";


$stmt = $conn->prepare($sql);

$stmt->execute($params);

$leaves = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

    <link 
        rel="stylesheet" 
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


</head>


<body>

<div class="hr-wrapper">

    <?php require_once "includes/sidebar.php"; ?>

    <div class="hr-content-wrapper">

        <?php require_once "includes/header.php"; ?>

        <main class="hr-main">

            <section class="hr-leave-page">

    <div class="hr-leave-summary-grid">

        <div class="hr-leave-summary-card blue">

            <div class="hr-leave-summary-icon">
                <i class="fa-solid fa-calendar-days"></i>
            </div>

            <div class="hr-leave-summary-content">

                <span>Total Leaves</span>

                <h2>
                    <?= $totalLeaves; ?>
                </h2>

                <p>All leave requests</p>

            </div>

        </div>

        <div class="hr-leave-summary-card orange">

            <div class="hr-leave-summary-icon">
                <i class="fa-solid fa-clock"></i>
            </div>

            <div class="hr-leave-summary-content">

                <span>Pending</span>

                <h2>
                    <?= $pendingLeaves; ?>
                </h2>

                <p>Waiting approval</p>

            </div>

        </div>

        <div class="hr-leave-summary-card green">

            <div class="hr-leave-summary-icon">
                <i class="fa-solid fa-circle-check"></i>
            </div>


            <div class="hr-leave-summary-content">

                <span>Approved</span>

                <h2>
                    <?= $approvedLeaves; ?>
                </h2>

                <p>Approved requests</p>

            </div>

        </div>

        <div class="hr-leave-summary-card red">

            <div class="hr-leave-summary-icon">
                <i class="fa-solid fa-circle-xmark"></i>
            </div>

            <div class="hr-leave-summary-content">

                <span>Rejected</span>

                <h2>
                    <?= $rejectedLeaves; ?>
                </h2>

                <p>Rejected requests</p>

            </div>

        </div>

    </div>

    <div class="hr-leave-header">

        <div class="hr-leave-title">

            <h1>

                <i class="fa-solid fa-calendar-days"></i>
                Leave Requests

            </h1>

            <p>Manage employee leave applications and approvals.</p>

        </div>

    </div>

    <div class="hr-leave-table-card">

        <div class="hr-leave-search">

            <form method="GET">

                <div class="hr-leave-search-box">

                    <i class="fa-solid fa-magnifying-glass"></i>

                    <input
                        type="text"
                        name="search"
                        id="leaveSearch"
                        placeholder="Search employee or leave type..."
                        value="<?= htmlspecialchars($search); ?>"
                    >

                </div>

            </form>

        </div>

        <div class="hr-leave-table-wrapper">

            <table class="hr-leave-table">

                <thead>

                    <tr>

                        <th>Employee</th>
                        <th>Leave Type</th>
                        <th>Date Range</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Action</th>

                    </tr>

                </thead>

                <tbody id="leaveTableBody">

                <?php if (!empty($leaves)): ?>

                    <?php foreach ($leaves as $leave): ?>

                        <tr>

                            <td>

                                <div class="hr-leave-name">

                                    <div class="hr-leave-avatar">

                                        <i class="fa-solid fa-user"></i>

                                    </div>

                                    <div>

                                        <strong>

                                            <?= htmlspecialchars(
                                                $leave['first_name'] . " " . $leave['last_name']
                                            ); ?>

                                        </strong>

                                        <small>

                                            <?= htmlspecialchars(
                                                $leave['employee_code'] ?? "N/A"
                                            ); ?>

                                        </small>

                                    </div>

                                </div>

                            </td>

                            <td>

                                <?= htmlspecialchars(
                                    $leave['leave_type_name'] ?? "N/A"
                                ); ?>

                            </td>

                            <td>

                                <?= date(
                                    "F d, Y",
                                    strtotime($leave['start_date'])
                                ); ?>

                                <br>

                                <span class="leave-date-separator">
                                    to
                                </span>

                                <br>

                                <?= date(
                                    "F d, Y",
                                    strtotime($leave['end_date'])
                                ); ?>

                            </td>

                            <td>

                                <?= htmlspecialchars(
                                    $leave['reason'] ?? "No reason provided"
                                ); ?>

                            </td>

                            <td>

                                <?php $status = $leave['status']; ?>

                                <span class="hr-leave-status <?= strtolower($status); ?>">

                                    <?php if ($status === "Approved"): ?>

                                        <i class="fa-solid fa-check"></i>

                                    <?php elseif ($status === "Rejected"): ?>

                                        <i class="fa-solid fa-xmark"></i>

                                    <?php else: ?>

                                        <i class="fa-solid fa-clock"></i>

                                    <?php endif; ?>

                                    <?= htmlspecialchars($status); ?>

                                </span>

                            </td>

                            <td>

                                <?= date(
                                    "F d, Y",
                                    strtotime($leave['created_at'])
                                ); ?>

                            </td>

                            <td>

                                <div class="hr-leave-actions">

                                    <form action="leave_view.php" method="POST">

                                        <input
                                            type="hidden"
                                            name="leave_id"
                                            value="<?= $leave['leave_id']; ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="hr-leave-action view"
                                            title="View Leave Details"
                                        >

                                            <i class="fa-solid fa-eye"></i>

                                        </button>

                                    </form>

                                    <form action="leave_edit.php" method="POST">

                                        <input
                                            type="hidden"
                                            name="leave_id"
                                            value="<?= $leave['leave_id']; ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="hr-leave-action edit"
                                            title="Edit Leave"
                                        >

                                            <i class="fa-solid fa-pen"></i>

                                        </button>

                                    </form>

                                    <?php if ($leave['status'] === "Pending"): ?>

    <button
        type="button"
        class="hr-leave-action approve"
        title="Approve Leave"
        onclick="confirmLeaveAction(
            <?= $leave['leave_id']; ?>,
            'Approved'
        )"
    >

        <i class="fa-solid fa-check"></i>

    </button>

    <button
        type="button"
        class="hr-leave-action reject"
        title="Reject Leave"
        onclick="confirmLeaveAction(
            <?= $leave['leave_id']; ?>,
            'Rejected'
        )"
    >

        <i class="fa-solid fa-xmark"></i>

    </button>

<?php endif; ?>

                </div>

            </td>

        </tr>

    <?php endforeach; ?>

<?php else: ?>

<tr>

    <td colspan="7">

        <div class="hr-leave-empty">

            <i class="fa-solid fa-folder-open"></i>

            <h3>No Leave Records Found</h3>

            <p>There are currently no leave requests available.</p>

        </div>

    </td>

</tr>

<?php endif; ?>

<tr id="noLeaveResult" style="display:none;">

    <td colspan="7">

        <div class="hr-leave-empty">

            <i class="fa-solid fa-magnifying-glass"></i>

            <h3>No Matching Leave Found</h3>

            <p>Try searching another keyword.</p>

        </div>
        
    </td>

</tr>

                </tbody>

            </table>

        </div>

    </div>

</section>

        </main>

    </div>

</div>


<?php if (!empty($success)): ?>


<script>

Swal.fire({

    icon: "success",

    title: "Updated!",

    text: <?= json_encode($success); ?>,

    confirmButtonColor: "#003DA5"

});

</script>


<?php endif; ?>

<?php if (!empty($error)): ?>


<script>

Swal.fire({

    icon: "error",

    title: "Error",

    text: <?= json_encode($error); ?>,

    confirmButtonColor: "#003DA5"

});

</script>


<?php endif; ?>


<form id="leaveStatusForm" method="POST" action="leave_status.php" style="display:none;">
    <?php csrfField(); ?>
    <input type="hidden" name="leave_id" id="leaveStatusId">
    <input type="hidden" name="status" id="leaveStatusValue">
</form>

<script>

function confirmLeaveAction(id, status)
{

    let action = status === "Approved"
        ? "approve"
        : "reject";

    Swal.fire({

        title: "Are you sure?",

        text: "You want to " + action + " this leave request?",

        icon: "warning",

        showCancelButton: true,

        confirmButtonColor: "#003DA5",

        cancelButtonColor: "#d33",

        confirmButtonText: "Yes, " + action

        }).then((result) => {

        if (result.isConfirmed) {

            document.getElementById("leaveStatusId").value = id;
            document.getElementById("leaveStatusValue").value = status;
            document.getElementById("leaveStatusForm").submit();

        }

    });

}


const searchInput = document.getElementById(
    "leaveSearch"
);

if (searchInput)
{

    searchInput.addEventListener(
        "keyup",
        function()
        {

            let value = this.value.toLowerCase();

            let rows = document.querySelectorAll(
                "#leaveTableBody tr"
            );

            let hasResult = false;

            rows.forEach(row => {

                if (row.id === "noLeaveResult")
                {

                    return;

                }

                let text = row.textContent.toLowerCase();

                if (text.includes(value))
                {

                    row.style.display = "";

                    hasResult = true;

                }
                else
                {

                    row.style.display = "none";

                }

            });

            let noResult = document.getElementById(
                "noLeaveResult"
            );

            if (noResult)
            {

                noResult.style.display = hasResult
                    ? "none"
                    : "";

            }

        }

    );

}
    
</script>


<script src="../assets/js/hr.js"></script>


</body>


</html>