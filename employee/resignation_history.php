<?php


session_start();

date_default_timezone_set("Asia/Manila");

require_once "../auth/employee_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";

$page_title = "Resignation History";

$employee_id = $_SESSION["employee_id"] ?? 0;

if (!$employee_id) {

    header("Location: ../login.php");
    exit;

}


function clean($value)

{

    return htmlspecialchars(

        $value ?? "",
        ENT_QUOTES,
        "UTF-8"

    );

}


$employeeStmt = $conn->prepare("

SELECT

    e.employee_id,
    e.employee_code,
    e.first_name,
    e.middle_name,
    e.last_name,
    d.department_name,
    p.position_name

FROM employees e

LEFT JOIN departments d
ON e.department_id = d.department_id

LEFT JOIN positions p
ON e.position_id = p.position_id

WHERE e.employee_id = :employee_id
LIMIT 1

");

$employeeStmt->execute([

    ":employee_id" => $employee_id

]);

$employee = $employeeStmt->fetch(PDO::FETCH_ASSOC);

if (!$employee) {

    header("Location: dashboard.php");
    exit;

}


function getResignationStatus($status)

{

    switch ($status) {

        case "Approved":

            return [

                "class" => "approved",
                "icon" => "fa-circle-check"

            ];

        case "Rejected":

            return [

                "class" => "rejected",
                "icon" => "fa-circle-xmark"

            ];

        default:

            return [

                "class" => "pending",
                "icon" => "fa-clock"

            ];

    }

}


function dateFormat($date)

{

    if (empty($date)) {

        return "-";

    }

    return date(

        "M d, Y",
        strtotime($date)

    );

}


function countResignation(

    $conn,
    $employee_id,
    $status = null

)

{

    if ($status) {

        $stmt = $conn->prepare("

            SELECT COUNT(*)
            FROM resignations
            WHERE employee_id = ?
            AND status = ?

        ");

        $stmt->execute([

            $employee_id,
            $status

        ]);


    } else {

        $stmt = $conn->prepare("

            SELECT COUNT(*)
            FROM resignations
            WHERE employee_id = ?

        ");

        $stmt->execute([

            $employee_id

        ]);

    }

    return (int) $stmt->fetchColumn();

}

$totalResignation = countResignation(

    $conn,
    $employee_id

);

$pendingResignation = countResignation(

    $conn,
    $employee_id,
    "Pending"

);

$approvedResignation = countResignation(

    $conn,
    $employee_id,
    "Approved"

);

$rejectedResignation = countResignation(

    $conn,
    $employee_id,
    "Rejected"

);


$resignationStmt = $conn->prepare("

SELECT

    resignation_id,
    resignation_date,
    last_working_day,
    reason,
    status,
    remarks,
    created_at

FROM resignations
WHERE employee_id = :employee_id
ORDER BY created_at DESC

");

$resignationStmt->execute([

    ":employee_id" => $employee_id

]);

$resignationHistory = $resignationStmt->fetchAll(PDO::FETCH_ASSOC);


$success = $_SESSION["resignation_success"] ?? "";
$error = $_SESSION["resignation_error"] ?? "";

unset($_SESSION["resignation_success"]);
unset($_SESSION["resignation_error"]);


?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= clean($page_title); ?> | Daily Cravings Foods Inc.</title>

    <link rel="stylesheet" href="../assets/css/global.css">

    <link rel="stylesheet" href="../assets/css/employee.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>


<body>

<div class="employee-wrapper">

    <?php require_once "includes/sidebar.php"; ?>

    <div class="employee-content-wrapper">

        <?php require_once "includes/header.php"; ?>

        <main class="employee-main">

            <section class="employee-resignation-history-page">

                <div class="employee-resignation-history-header">

                    <div class="employee-resignation-history-title">

                        <h1>
                            <i class="fa-solid fa-clock-rotate-left"></i>
                            Resignation History
                        </h1>

                        <p>Review your submitted resignation requests and approval status.</p>

                    </div>

                    <a
                        href="resignation_request.php"
                        class="employee-new-resignation-btn"

                    >

                        <i class="fa-solid fa-plus"></i>
                        Submit Resignation Request
                    </a>

                </div>

                <div class="employee-resignation-summary-grid">

                    <div class="employee-resignation-summary-card blue">

                        <div class="employee-resignation-summary-icon">
                            <i class="fa-solid fa-file-signature"></i>
                        </div>

                        <div class="employee-resignation-summary-content">

                            <span>
                                Total Requests
                            </span>

                            <h2>
                                <?= number_format($totalResignation); ?>
                            </h2>

                            <p>All submitted resignation requests.</p>

                        </div>

                    </div>

                    <div class="employee-resignation-summary-card orange">

                        <div class="employee-resignation-summary-icon">
                            <i class="fa-solid fa-clock"></i>
                        </div>

                        <div class="employee-resignation-summary-content">

                            <span>
                                Pending
                            </span>

                            <h2>
                                <?= number_format($pendingResignation); ?>
                            </h2>

                            <p>Waiting for HR approval.</p>

                        </div>

                    </div>

                    <div class="employee-resignation-summary-card green">

                        <div class="employee-resignation-summary-icon">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>

                        <div class="employee-resignation-summary-content">

                            <span>
                                Approved
                            </span>

                            <h2>
                                <?= number_format($approvedResignation); ?>
                            </h2>

                            <p>Approved requests.</p>

                        </div>

                    </div>

                    <div class="employee-resignation-summary-card red">

                        <div class="employee-resignation-summary-icon">
                            <i class="fa-solid fa-circle-xmark"></i>
                        </div>

                        <div class="employee-resignation-summary-content">

                            <span>
                                Rejected
                            </span>

                            <h2>
                                <?= number_format($rejectedResignation); ?>
                            </h2>

                            <p>Declined requests.</p>

                        </div>

                    </div>

                </div>

                <div class="employee-resignation-table-card">

                    <div class="employee-resignation-table-title">

                        <h2>
                            <i class="fa-solid fa-table-list"></i>
                            Resignation Request History
                        </h2>

                        <p>Browse your submitted resignation applications.</p>

                    </div>

                    <div class="employee-resignation-search">

                        <div class="employee-resignation-search-box">
                            <i class="fa-solid fa-magnifying-glass"></i>

                            <input
                                type="text"
                                id="resignationSearch"
                                placeholder="Search reason or status..."
                            >

                        </div>

                        <div class="employee-resignation-filter-box">

                            <select id="resignationStatusFilter">

                                <option value="">

                                    All Status

                                </option>

                                <option value="Pending">

                                    Pending

                                </option>

                                <option value="Approved">

                                    Approved

                                </option>

                                <option value="Rejected">

                                    Rejected

                                </option>

                            </select>

                        </div>

                    </div>

                    <div class="employee-resignation-table-wrapper">

                        <table
                            class="employee-resignation-table"
                            id="resignationHistoryTable"

                        >

                            <thead>

                                <tr>

                                    <th>#</th>
                                    <th>Resignation Date</th>
                                    <th>Last Working Day</th>
                                    <th>Reason</th>
                                    <th>Submitted</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>

                            </thead>

                        <tbody>

                            <?php if (!empty($resignationHistory)): ?>

                                <?php foreach ($resignationHistory as $index => $resignation): ?>

                                    <?php

                                    $status = getResignationStatus(
                                        $resignation["status"]

                                    );

                                    ?>

                                    <tr>

                                        <td>
                                            <?= $index + 1; ?>
                                        </td>

                                        <td>
                                            <?= dateFormat(
                                                $resignation["resignation_date"]
                                            ); ?>
                                        </td>

                                        <td>
                                            <?= dateFormat(
                                                $resignation["last_working_day"]
                                            ); ?>
                                        </td>

                                        <td>

                                            <div class="employee-resignation-reason">
                                                <i class="fa-solid fa-comment-dots"></i>

                                                <span>
                                                    <?= clean(
                                                        $resignation["reason"]
                                                    ); ?>
                                                </span>

                                            </div>

                                        </td>

                                        <td>
                                            <?= dateFormat(
                                                $resignation["created_at"]
                                            ); ?>
                                        </td>

                                        <td>

                                            <span
                                                class="employee-resignation-status <?= $status["class"]; ?>"

                                            >

                                                <i
                                                    class="fa-solid <?= $status["icon"]; ?>"
                                                ></i>

                                                <?= clean(
                                                    $resignation["status"]
                                                ); ?>

                                            </span>

                                        </td>

                                        <td>

                                        <div class="employee-resignation-actions">

                                                                                        <form
                                                method="POST"
                                                action="resignation_view.php"

                                            >

                                                <?php csrfField(); ?>

                                                <input
                                                    type="hidden"
                                                    name="resignation_id"
                                                    value="<?= $resignation["resignation_id"]; ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    class="employee-resignation-view-btn"

                                                >

                                                    <i class="fa-solid fa-eye"></i>
                                                    View Details
                                                </button>

                                            </form>

                                                                                        <form
                                                method="POST"
                                                action="resignation_pdf.php"
                                                target="_blank"
                                            >

                                                <?php csrfField(); ?>

                                                <input
                                                    type="hidden"
                                                    name="resignation_id"
                                                    value="<?= $resignation["resignation_id"]; ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    class="employee-resignation-pdf-btn"
                                                    title="Export Resignation PDF"

                                                >

                                                    <i class="fa-solid fa-file-pdf"></i>
                                                    PDF
                                                </button>

                                            </form>

                                        </div>

                                        </td>
                                        
                                    </tr>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <tr>

                                    <td colspan="7">

                                        <div class="employee-resignation-empty">
                                            <i class="fa-solid fa-file-circle-xmark"></i>

                                            <h3>No Resignation Requests Found</h3>

                                            <p>You have not submitted any resignation request yet.</p>

                                        </div>

                                    </td>

                                </tr>

                            <?php endif; ?>

                            <tr

                                id="employeeResignationSearchEmpty"
                                style="display:none;"

                            >

                                <td colspan="7">

                                    <div class="employee-resignation-empty">
                                        <i class="fa-solid fa-magnifying-glass"></i>

                                        <h3>No Matching Request</h3>

                                        <p>No resignation request matched your search.</p>

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


<script src="../assets/js/employee.js"></script>


<?php if ($success): ?>


<script>

Swal.fire({

    icon: "success",

    title: "Success",

    text: <?= json_encode($success); ?>,

    confirmButtonColor: "#0D47A1"

});

</script>

<?php endif; ?>

<?php if ($error): ?>

<script>

Swal.fire({

    icon: "error",

    title: "Error",

    text: <?= json_encode($error); ?>,

    confirmButtonColor: "#C62828"

});

</script>

<?php endif; ?>


</body>

</html>