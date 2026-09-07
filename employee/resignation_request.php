<?php

session_start();

date_default_timezone_set("Asia/Manila");

require_once "../auth/employee_auth.php";
require_once "../config/db.php";require_once "../config/db.php";
require_once "../config/csrf.php";

$page_title = "Submit Resignation Request";


$resignationSuccess = $_SESSION["resignation_success"] ?? "";
$resignationError = $_SESSION["resignation_error"] ?? "";

unset($_SESSION["resignation_success"]);
unset($_SESSION["resignation_error"]);


$employee_id = $_SESSION["employee_id"] ?? 0;

if (!$employee_id) {

    header("Location: ../login.php");
    exit;

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


$employeeName = trim(

    $employee["first_name"]

    . " "
    .

    (!empty($employee["middle_name"])

        ? $employee["middle_name"] . " "
        : ""

    )
    .
    $employee["last_name"]

);


$pendingStmt = $conn->prepare("

    SELECT

        resignation_id,
        status

    FROM resignations

    WHERE employee_id = :employee_id
    AND status = 'Pending'

    LIMIT 1

");


$pendingStmt->execute([

    ":employee_id" => $employee_id

]);


$pendingResignation = $pendingStmt->fetch(PDO::FETCH_ASSOC);


$today = date("Y-m-d");


function escape($value)

{

    return htmlspecialchars(

        $value ?? "",
        ENT_QUOTES,
        "UTF-8"

    );

}


?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= escape($page_title); ?> | Daily Cravings Foods Inc.</title>

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

            <section class="employee-resignation-request-page">

                <div class="employee-resignation-request-header">

                    <div class="employee-resignation-request-title">

                        <h1>
                            <i class="fa-solid fa-user-minus"></i>
                            Submit Resignation Request
                        </h1>

                        <p>
                            Submit your resignation request for HR approval.
                            Please provide complete and accurate information.
                        </p>

                    </div>

                    <div class="employee-resignation-history-btn">

                        <a
                            href="resignation_history.php"
                            cclass="employee-resignation-request-history-btn"

                        >

                            <i class="fa-solid fa-clock-rotate-left"></i>
                            Resignation History
                        </a>

                    </div>

                </div>

                <div class="employee-resignation-card">

                    <div class="employee-resignation-card-header">

                        <div class="employee-resignation-card-icon">
                            <i class="fa-solid fa-user"></i>
                        </div>

                        <div>

                            <h2>Employee Information</h2>

                            <p>Current employment information</p>

                        </div>

                    </div>

                    <div class="employee-resignation-info-grid">

                        <div class="employee-resignation-info-item">

                            <label>
                                <i class="fa-solid fa-id-card"></i>
                                Employee Code
                            </label>

                            <p>
                                <?= escape($employee["employee_code"]); ?>
                            </p>

                        </div>

                        <div class="employee-resignation-info-item">

                            <label>
                                <i class="fa-solid fa-user"></i>
                                Employee Name
                            </label>

                            <p>
                                <?= escape($employeeName); ?>
                            </p>

                        </div>

                        <div class="employee-resignation-info-item">

                            <label>
                                <i class="fa-solid fa-building"></i>
                                Department
                            </label>

                            <p>
                                <?= escape(
                                    $employee["department_name"]
                                    ??
                                    "No Department"
                                ); ?>
                            </p>

                        </div>

                        <div class="employee-resignation-info-item">

                            <label>
                                <i class="fa-solid fa-briefcase"></i>
                                Position
                            </label>

                            <p>
                                <?= escape(
                                    $employee["position_name"]
                                    ??
                                    "No Position"
                                ); ?>
                            </p>

                        </div>

                    </div>

                </div>

                                        <form
                    id="employeeResignationForm"
                    method="POST"
                    action="resignation_request_process.php"
                    autocomplete="off"
                    novalidate
                >

                    <?php csrfField(); ?>

                    <div class="employee-resignation-card">

                        <div class="employee-resignation-card-header">

                            <div class="employee-resignation-card-icon">
                                <i class="fa-solid fa-file-signature"></i>
                            </div>

                            <div>

                                <h2>Resignation Request Form</h2>

                                <p>Provide your resignation details below.</p>

                            </div>

                        </div>

                        <div class="employee-resignation-form-grid">

                            <div class="employee-resignation-form-group">

                                <label>

                                    <i class="fa-solid fa-calendar-days"></i>
                                    Resignation Date
                                    <span>*</span>

                                </label>

                                <input
                                    type="date"
                                    name="resignation_date"
                                    id="resignation_date"
                                    min="<?= $today; ?>"
                                >

                                <small
                                    class="employee-input-error"
                                    id="resignationDateError"
                                ></small>

                            </div>

                            <div class="employee-resignation-form-group">

                                <label>

                                    <i class="fa-solid fa-calendar-check"></i>
                                    Last Working Day
                                    <span>*</span>

                                </label>

                                <input
                                    type="date"
                                    name="last_working_day"
                                    id="last_working_day"
                                    min="<?= $today; ?>"
                                >

                                <small
                                    class="employee-input-error"
                                    id="lastWorkingDayError"
                                ></small>

                            </div>

                        </div>

                    </div>

                    <div class="employee-resignation-card">

                        <div class="employee-resignation-card-header">

                            <div class="employee-resignation-card-icon">
                                <i class="fa-solid fa-comment-dots"></i>
                            </div>

                            <div>

                                <h2>Resignation Reason</h2>

                                <p>Explain the reason why you are requesting resignation.</p>

                            </div>

                        </div>

                        <div class="employee-resignation-form-group employee-resignation-full-width">

                            <label>

                                <i class="fa-solid fa-align-left"></i>
                                Reason
                                <span>*</span>

                            </label>

                            <textarea
                                name="reason"
                                id="resignation_reason"
                                rows="6"
                                maxlength="500"
                                placeholder="Enter your resignation reason..."
                            ></textarea>

                            <small
                                class="employee-resignation-error"
                                id="reasonError"
                            ></small>

                            <div class="employee-resignation-counter">

                                <span id="reasonCounter">
                                    0
                                </span>

                                / 500 Characters

                            </div>

                        </div>

                    </div>

                    <div class="employee-resignation-guidelines">

                        <div class="employee-resignation-guidelines-icon">
                            <i class="fa-solid fa-circle-info"></i>
                        </div>

                        <div>

                            <h4>Resignation Guidelines</h4>

                            <ul>

                                <li>
                                    Resignation requests must be submitted before your intended last working day.
                                </li>

                                <li>
                                    Your request will be reviewed and approved by HR.
                                </li>

                                <li>
                                    Approved resignation cannot be cancelled without HR approval.
                                </li>

                                <li>
                                    Make sure all pending responsibilities are properly endorsed.
                                </li>

                            </ul>

                        </div>

                    </div>

                    <div class="employee-resignation-action-buttons">

                        <button
                            type="reset"
                            class="employee-resignation-reset-btn"

                        >

                            <i class="fa-solid fa-rotate-left"></i>
                            Reset Form
                        </button>

                        <button
                            type="submit"
                            class="employee-resignation-submit-btn"

                        >

                            <i class="fa-solid fa-paper-plane"></i>
                            Submit Resignation Request
                        </button>

                    </div>

                </form>

            </section>

        </main>

    </div>

</div>


<script src="../assets/js/employee.js"></script>

<script>

<?php if (!empty($resignationSuccess)): ?>

Swal.fire({

    icon: "success",
    title: "Success",
    text:

    <?= json_encode($resignationSuccess); ?>,

    confirmButtonColor: "#0D47A1"

});

<?php endif; ?>

<?php if (!empty($resignationError)): ?>

Swal.fire({

    icon: "error",
    title: "Request Failed",
    text:

    <?= json_encode($resignationError); ?>,

    confirmButtonColor: "#D32F2F"

});

<?php endif; ?>

document.getElementById("employeeResignationForm").addEventListener("submit", function (e) {

    e.preventDefault();

    const form = this;

    Swal.fire({
        icon: "warning",
        title: "Submit this resignation request?",
        text: "This will be sent to HR for approval. Make sure your reason and dates are correct before continuing.",
        showCancelButton: true,
        confirmButtonText: "Yes, Submit",
        cancelButtonText: "Cancel",
        confirmButtonColor: "#D32F2F",
        cancelButtonColor: "#90A4AE",
        reverseButtons: true
    }).then(function (result) {

        if (result.isConfirmed) {
            form.submit();
        }

    });

});

</script>


</body>

</html>