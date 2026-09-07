<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";

$page_title = "Resignations Management";


$success = $_SESSION['resignation_success'] ?? "";
$error   = $_SESSION['resignation_error'] ?? "";

unset($_SESSION['resignation_success']);
unset($_SESSION['resignation_error']);


$totalResignations = $conn->query("
    SELECT COUNT(*)
    FROM resignations
")->fetchColumn();

$pendingResignations = $conn->query("
    SELECT COUNT(*)
    FROM resignations
    WHERE status = 'Pending'
")->fetchColumn();

$approvedResignations = $conn->query("
    SELECT COUNT(*)
    FROM resignations
    WHERE status = 'Approved'
")->fetchColumn();

$rejectedResignations = $conn->query("
    SELECT COUNT(*)
    FROM resignations
    WHERE status = 'Rejected'
")->fetchColumn();


$search = trim($_GET['search'] ?? "");


$sql = "
    SELECT
        r.resignation_id,
        r.reason,
        r.remarks,
        r.resignation_date,
        r.status,
        r.created_at,

        e.employee_id,
        e.employee_code,
        e.first_name,
        e.middle_name,
        e.last_name,

        d.department_name,
        pos.position_name

    FROM resignations r

    INNER JOIN employees e
        ON r.employee_id = e.employee_id

    LEFT JOIN departments d
        ON e.department_id = d.department_id

    LEFT JOIN positions pos
        ON e.position_id = pos.position_id
";

$params = [];

if ($search !== "") {

    $sql .= "
        WHERE (
            e.employee_code LIKE ?
            OR e.first_name LIKE ?
            OR e.middle_name LIKE ?
            OR e.last_name LIKE ?
            OR d.department_name LIKE ?
            OR pos.position_name LIKE ?
            OR r.status LIKE ?
            OR r.reason LIKE ?
            OR r.remarks LIKE ?
        )
    ";

    $keyword = "%" . $search . "%";

    $params = [
        $keyword,
        $keyword,
        $keyword,
        $keyword,
        $keyword,
        $keyword,
        $keyword,
        $keyword,
        $keyword
    ];
}

$sql .= "
    ORDER BY r.created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->execute($params);

$resignations = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($page_title); ?> | Daily Cravings Foods Inc.</title>

    <link rel="stylesheet" href="../assets/css/global.css">
    
    <link rel="stylesheet" href="../assets/css/hr.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>

<div class="hr-wrapper">

    <?php require_once "includes/sidebar.php"; ?>

    <div class="hr-content-wrapper">

        <?php require_once "includes/header.php"; ?>

        <main class="hr-main">

            <section class="hr-res-page">

                <div class="hr-res-summary-grid">

                    <div class="hr-res-summary-card blue">

                        <div class="hr-res-summary-icon">
                            <i class="fa-solid fa-file-circle-exclamation"></i>
                        </div>

                        <div class="hr-res-summary-content">
                            <span>Total Resignations</span>

                            <h2><?= $totalResignations; ?></h2>

                            <p>All resignation requests</p>
                        </div>

                    </div>

                    <div class="hr-res-summary-card orange">

                        <div class="hr-res-summary-icon">
                            <i class="fa-solid fa-clock"></i>
                        </div>

                        <div class="hr-res-summary-content">
                            <span>Pending Requests</span>

                            <h2><?= $pendingResignations; ?></h2>

                            <p>Waiting for approval</p>
                        </div>

                    </div>

                    <div class="hr-res-summary-card green">

                        <div class="hr-res-summary-icon">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>

                        <div class="hr-res-summary-content">
                            <span>Approved</span>

                            <h2><?= $approvedResignations; ?></h2>

                            <p>Approved resignations</p>
                        </div>

                    </div>

                    <div class="hr-res-summary-card red">

                        <div class="hr-res-summary-icon">
                            <i class="fa-solid fa-circle-xmark"></i>
                        </div>

                        <div class="hr-res-summary-content">
                            <span>Rejected</span>

                            <h2><?= $rejectedResignations; ?></h2>

                            <p>Rejected requests</p>
                        </div>

                    </div>

                </div>

                <div class="hr-res-header">

                    <div class="hr-res-title">

                        <h1>
                            <i class="fa-solid fa-person-walking-arrow-right"></i>
                            Resignations Management
                        </h1>

                        <p>Review, approve, edit and manage employee resignation requests.</p>

                    </div>

                </div>

                <div class="hr-res-table-card">

                    <div class="hr-res-search">

                        <div class="hr-res-search-box">

                            <i class="fa-solid fa-magnifying-glass"></i>

                            <input
                                type="text"
                                id="resignationSearch"
                                placeholder="Search employee, department, reason, remarks or status..."
                                autocomplete="off"
                            >

                        </div>

                    </div>

                    <div class="hr-res-table-wrapper">

                        <table class="hr-res-table">

                            <thead>

                                <tr>
                                    <th>Employee</th>
                                    <th>Department</th>
                                    <th>Position</th>
                                    <th>Reason</th>
                                    <th>Remarks</th>
                                    <th>Resignation Date</th>
                                    <th>Status</th>
                                    <th width="220">Action</th>
                                </tr>

                            </thead>

                            <tbody id="resignationTableBody">

                                <?php if (!empty($resignations)): ?>

    <?php foreach ($resignations as $res): ?>

        <tr class="resignation-row">

            <td>

                <div class="hr-res-name">

                    <div class="hr-res-avatar">
                        <i class="fa-solid fa-user"></i>
                    </div>

                    <div>

                        <strong>
                            <?= htmlspecialchars(
                                trim(
                                    $res['first_name'] . " " .
                                    (!empty($res['middle_name'])
                                        ? $res['middle_name'] . " "
                                        : "") .
                                    $res['last_name']
                                )
                            ); ?>
                        </strong>

                        <br>

                        <small>
                            <?= htmlspecialchars($res['employee_code']); ?>
                        </small>

                    </div>

                </div>

            </td>

            <td>
                <?= htmlspecialchars($res['department_name'] ?? "N/A"); ?>
            </td>

            <td>
                <?= htmlspecialchars($res['position_name'] ?? "N/A"); ?>
            </td>

            <td>
                <?= htmlspecialchars($res['reason']); ?>
            </td>

            <td>

                <?php if (!empty($res['remarks'])): ?>

                    <span class="hr-res-remarks">
                        <?= htmlspecialchars($res['remarks']); ?>
                    </span>

                <?php else: ?>

                    <span class="hr-res-no-remarks">
                        No remarks
                    </span>

                <?php endif; ?>

            </td>

            <td>
                <?= htmlspecialchars(
                    date(
                        "F d, Y",
                        strtotime($res['resignation_date'])
                    )
                ); ?>
            </td>

            <td>

                <span class="hr-res-status <?= strtolower($res['status']); ?>">

                    <?php if ($res['status'] == "Pending"): ?>

                        <i class="fa-solid fa-clock"></i>

                    <?php elseif ($res['status'] == "Approved"): ?>

                        <i class="fa-solid fa-circle-check"></i>

                    <?php else: ?>

                        <i class="fa-solid fa-circle-xmark"></i>

                    <?php endif; ?>

                    <?= htmlspecialchars($res['status']); ?>

                </span>

            </td>

            <td>

                <div class="hr-res-actions">

                                        <form action="resignation_view.php" method="POST">

                        <?php csrfField(); ?>

                        <input
                            type="hidden"
                            name="resignation_id"
                            value="<?= $res['resignation_id']; ?>"
                        >

                        <button
                            type="submit"
                            class="hr-res-action view"
                            title="View Resignation"
                        >
                            <i class="fa-solid fa-eye"></i>
                        </button>

                    </form>

                                        <form action="resignation_edit.php" method="POST">

                        <?php csrfField(); ?>

                        <input
                            type="hidden"
                            name="resignation_id"
                            value="<?= $res['resignation_id']; ?>"
                        >

                        <button
                            type="submit"
                            class="hr-res-action edit"
                            title="Edit Resignation"
                        >
                            <i class="fa-solid fa-pen"></i>
                        </button>

                    </form>

                    <?php if ($res['status'] == "Pending"): ?>

                        <form action="resignation_status.php" method="POST" class="hr-res-status-form">

                            <?php csrfField(); ?>

                            <input
                                type="hidden"
                                name="resignation_id"
                                value="<?= $res['resignation_id']; ?>"
                            >

                            <input
                                type="hidden"
                                name="status"
                                value="Approved"
                            >

                            <button
                                type="submit"
                                class="hr-res-action approve"
                                title="Approve Resignation"
                            >
                                <i class="fa-solid fa-check"></i>
                            </button>

                        </form>

                            <form action="resignation_status.php" method="POST" class="hr-res-status-form">

                            <?php csrfField(); ?>

                            <input
                                type="hidden"
                                name="resignation_id"
                                value="<?= $res['resignation_id']; ?>"
                            >

                            <input
                                type="hidden"
                                name="status"
                                value="Rejected"
                            >

                            <button
                                type="submit"
                                class="hr-res-action reject"
                                title="Reject Resignation"
                            >
                                <i class="fa-solid fa-xmark"></i>
                            </button>

                        </form>

                    <?php endif; ?>

                </div>

            </td>

        </tr>

    <?php endforeach; ?>

<?php else: ?>

    <tr>

        <td colspan="8">

            <div class="hr-res-empty">

                <i class="fa-solid fa-person-walking-arrow-right"></i>

                <h3>No Resignation Request</h3>

                <p>No employee resignation records available.</p>

            </div>

        </td>

    </tr>

<?php endif; ?>

<tr id="noResignationResult" style="display: none;">

    <td colspan="8">

        <div class="hr-res-empty">

            <i class="fa-solid fa-magnifying-glass"></i>

            <h3>No Result Found</h3>

            <p>No resignation matched your search.</p>

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
document.addEventListener("DOMContentLoaded", function () {

    Swal.fire({
        icon: "success",
        title: "Success!",
        text: <?= json_encode($success); ?>,
        confirmButtonColor: "#003DA5"
    });

});
</script>

<?php endif; ?>

<?php if (!empty($error)): ?>

<script>
document.addEventListener("DOMContentLoaded", function () {

    Swal.fire({
        icon: "error",
        title: "Unable to Continue",
        text: <?= json_encode($error); ?>,
        confirmButtonColor: "#003DA5"
    });

});
</script>

<?php endif; ?>


<script>
document.addEventListener("DOMContentLoaded", function () {

    const searchInput = document.getElementById("resignationSearch");
    const rows = document.querySelectorAll(".resignation-row");
    const noResult = document.getElementById("noResignationResult");

    if (!searchInput) {
        return;
    }

    searchInput.addEventListener("keyup", function () {

        const keyword = this.value.toLowerCase().trim();

        let visibleCount = 0;

        rows.forEach(function (row) {

            const text = row.textContent.toLowerCase();

            if (text.includes(keyword)) {
                row.style.display = "";
                visibleCount++;
            } else {
                row.style.display = "none";
            }

        });

        if (noResult) {
            noResult.style.display = visibleCount === 0 ? "" : "none";
        }

    });

});
</script>


<script>
document.querySelectorAll(".hr-res-status-form").forEach(function (form) {

    form.addEventListener("submit", function (e) {

        e.preventDefault();

        const status = form.querySelector('input[name="status"]').value;
        const isApproving = status === "Approved";

        Swal.fire({
            title: isApproving ? "Approve this resignation?" : "Reject this resignation?",
            text: isApproving
                ? "This will mark the resignation as Approved and set the employee's status to Resigned."
                : "This will mark the resignation as Rejected.",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: isApproving ? "#2E7D32" : "#C62828",
            cancelButtonColor: "#90A4AE",
            confirmButtonText: isApproving ? "Yes, Approve" : "Yes, Reject"
        }).then(function (result) {

            if (result.isConfirmed) {
                form.submit();
            }

        });

    });

});
</script>

<script src="../assets/js/hr.js"></script>  


</body>

</html>