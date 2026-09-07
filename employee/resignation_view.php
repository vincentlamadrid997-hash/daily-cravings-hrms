<?php


session_start();

date_default_timezone_set("Asia/Manila");

require_once "../auth/employee_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";

$page_title = "Resignation Details";

$employee_id = $_SESSION["employee_id"] ?? 0;

if (!$employee_id) {

    header("Location: ../login.php");
    exit;

}

requireCSRFToken("resignation_history.php");


function clean($value)
{

    return htmlspecialchars(
        $value ?? "",
        ENT_QUOTES,
        "UTF-8"
    );

}

function dateFormat($date)
{

    if (empty($date)) {
        return "-";
    }

    return date(
        "F d, Y",
        strtotime($date)
    );

}


$resignation_id = $_POST["resignation_id"] ?? 0;

if (!$resignation_id) {

    header("Location: resignation_history.php");
    exit;

}


$resignationStmt = $conn->prepare("

SELECT

    r.resignation_id,
    r.employee_id,
    r.resignation_date,
    r.last_working_day,
    r.reason,
    r.status,
    r.remarks,
    r.created_at,

    e.employee_code,
    e.first_name,
    e.middle_name,
    e.last_name,

    d.department_name,
    p.position_name

FROM resignations r

LEFT JOIN employees e
ON r.employee_id = e.employee_id

LEFT JOIN departments d
ON e.department_id = d.department_id

LEFT JOIN positions p
ON e.position_id = p.position_id

WHERE r.resignation_id = :resignation_id
AND r.employee_id = :employee_id

LIMIT 1

");

$resignationStmt->execute([

    ":resignation_id" => $resignation_id,
    ":employee_id"     => $employee_id

]);

$resignation = $resignationStmt->fetch(PDO::FETCH_ASSOC);

if (!$resignation) {

    $_SESSION["resignation_error"] = "Resignation request not found.";

    header("Location: resignation_history.php");
    exit;

}


$employee = [

    "employee_code"   => $resignation["employee_code"],
    "first_name"      => $resignation["first_name"],
    "middle_name"     => $resignation["middle_name"],
    "last_name"       => $resignation["last_name"],
    "department_name" => $resignation["department_name"],
    "position_name"   => $resignation["position_name"]

];


function getResignationStatus($status)
{

    switch ($status) {

        case "Approved":

            return [
                "class" => "approved",
                "icon"  => "fa-circle-check"
            ];

        case "Rejected":

            return [
                "class" => "rejected",
                "icon"  => "fa-circle-xmark"
            ];

        default:

            return [
                "class" => "pending",
                "icon"  => "fa-clock"
            ];

    }

}

$status = getResignationStatus(
    $resignation["status"]
);

$employee_name = trim(

    $employee["first_name"] . " " .
    $employee["middle_name"] . " " .
    $employee["last_name"]

);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= clean($page_title); ?> | Daily Cravings Foods Inc.</title>

    <link rel="stylesheet" href="../assets/css/global.css">

    <link rel="stylesheet" href="../assets/css/employee.css">

    <link rel="stylesheet" href="../assets/css/crud_employee.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>


<body>

<div class="employee-wrapper">

    <?php require_once "includes/sidebar.php"; ?>

    <div class="employee-content-wrapper">

        <?php require_once "includes/header.php"; ?>

        <main class="employee-main">

            <section class="employee-resignation-details-page">

                <div class="employee-resignation-details-header">

                    <div class="employee-resignation-details-title">

                        <h1>
                            <i class="fa-solid fa-file-signature"></i>
                            Resignation Request Details
                        </h1>

                        <p>View complete information about your submitted resignation request.</p>

                    </div>

                    <a
                        href="resignation_history.php"
                        class="employee-resignation-details-back-btn"
                    >

                        <i class="fa-solid fa-arrow-left"></i>
                        Back to History
                    </a>

                </div>

                <div class="employee-resignation-details-card">

                    <div class="employee-resignation-details-card-header">

                        <div class="employee-resignation-details-icon">
                            <i class="fa-solid fa-user"></i>
                        </div>

                        <div>

                            <h2>Employee Information</h2>

                            <p>Employee profile details</p>

                        </div>

                    </div>

                    <div class="employee-resignation-details-grid">

                        <div class="employee-resignation-details-group">

                            <label>
                                <i class="fa-solid fa-id-card"></i>
                                Employee Code
                            </label>

                            <p>
                                <?= clean($employee["employee_code"]); ?>
                            </p>

                        </div>

                        <div class="employee-resignation-details-group">

                            <label>
                                <i class="fa-solid fa-user"></i>
                                Full Name
                            </label>

                            <p>
                                <?= clean($employee_name); ?>
                            </p>

                        </div>

                        <div class="employee-resignation-details-group">

                            <label>
                                <i class="fa-solid fa-building"></i>
                                Department
                            </label>

                            <p>
                                <?= clean($employee["department_name"]); ?>
                            </p>

                        </div>

                        <div class="employee-resignation-details-group">

                            <label>
                                <i class="fa-solid fa-briefcase"></i>
                                Position
                            </label>

                            <p>
                                <?= clean($employee["position_name"]); ?>
                            </p>

                        </div>

                    </div>

                </div>

                <div class="employee-resignation-details-card">

                    <div class="employee-resignation-details-card-header">

                        <div class="employee-resignation-details-icon">
                            <i class="fa-solid fa-file-signature"></i>
                        </div>

                        <div>

                            <h2>Resignation Information</h2>

                            <p>Submitted resignation request details</p>

                        </div>

                    </div>

                    <div class="employee-resignation-details-grid">

                        <div class="employee-resignation-details-group">

                            <label>
                                <i class="fa-solid fa-calendar-days"></i>
                                Resignation Date
                            </label>

                            <p>
                                <?= dateFormat($resignation["resignation_date"]); ?>
                            </p>

                        </div>

                        <div class="employee-resignation-details-group">

                            <label>
                                <i class="fa-solid fa-calendar-check"></i>
                                Last Working Day
                            </label>

                            <p>
                                <?= dateFormat($resignation["last_working_day"]); ?>
                            </p>

                        </div>

                        <div class="employee-resignation-details-group">

                            <label>
                                <i class="fa-solid fa-calendar-plus"></i>
                                Submitted Date
                            </label>

                            <p>
                                <?= dateFormat($resignation["created_at"]); ?>
                            </p>

                        </div>

                    </div>

                </div>

                <div class="employee-resignation-details-card">

                    <div class="employee-resignation-details-card-header">

                        <div class="employee-resignation-details-icon">
                            <i class="fa-solid fa-circle-info"></i>
                        </div>

                        <div>

                            <h2>Request Status</h2>

                            <p>Current approval status</p>

                        </div>

                    </div>

                    <div class="employee-resignation-details-summary">

                        <div class="employee-resignation-details-summary-box">

                            <span>
                                Status
                            </span>

                            <h3>

                                <span class="employee-resignation-status-badge <?= $status["class"]; ?>">

                                    <i class="fa-solid <?= $status["icon"]; ?>"></i>
                                    <?= clean($resignation["status"]); ?>

                                </span>

                            </h3>

                        </div>

                        <div class="employee-resignation-details-summary-box">

                            <span>
                                HR Remarks
                            </span>

                            <h3>
                                <?= !empty($resignation["remarks"])
                                    ? clean($resignation["remarks"])
                                    : "No remarks"; ?>
                            </h3>

                        </div>

                    </div>

                </div>

                <div class="employee-resignation-details-card">

                    <div class="employee-resignation-details-card-header">

                        <div class="employee-resignation-details-icon">
                            <i class="fa-solid fa-comment-dots"></i>
                        </div>

                        <div>

                            <h2>Additional Information</h2>

                            <p>Resignation reason and supporting information</p>

                        </div>

                    </div>

                    <div class="employee-resignation-details-grid">

                        <div
                            class="employee-resignation-details-group"
                            style="grid-column: span 2;"
                        >

                            <label>
                                <i class="fa-solid fa-message"></i>
                                Reason for Resignation
                            </label>

                            <p>
                                <?= nl2br(clean($resignation["reason"])); ?>
                            </p>

                        </div>

                    </div>

                </div>

                <div class="employee-resignation-details-actions">

                    <form
                        action="resignation_pdf.php"
                        method="POST"
                    >

                        <input
                            type="hidden"
                            name="resignation_id"
                            value="<?= $resignation["resignation_id"]; ?>"
                        >

                        <button
                            type="submit"
                            class="employee-resignation-details-btn download"
                        >

                            <i class="fa-solid fa-file-pdf"></i>
                            Download Resignation
                        </button>

                    </form>

                </div>

            </section>

        </main>

    </div>

</div>

</body>

</html>