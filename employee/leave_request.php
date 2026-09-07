<?php



session_start();

date_default_timezone_set("Asia/Manila");

require_once "../auth/employee_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";

$page_title = "Submit Leave Request";


$leaveSuccess = $_SESSION["leave_success"] ?? "";
$leaveError = $_SESSION["leave_error"] ?? "";

unset($_SESSION["leave_success"]);
unset($_SESSION["leave_error"]);


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


$leaveTypeStmt = $conn->prepare("

SELECT

    leave_type_id,
    leave_type_name,
    description,
    allow_custom_reason

FROM leave_types
WHERE status='Active'
ORDER BY leave_type_name ASC

");


$leaveTypeStmt->execute();


$leaveTypes = $leaveTypeStmt->fetchAll(PDO::FETCH_ASSOC);


$holidayStmt = $conn->prepare("

SELECT

    holiday_date

FROM holidays

WHERE status='Active'

");


$holidayStmt->execute();


$holidayDates = [];


foreach ($holidayStmt->fetchAll(PDO::FETCH_ASSOC) as $holiday) {

    $holidayDates[] = $holiday["holiday_date"];

}


$holidayJSON = json_encode($holidayDates);


$today = date("Y-m-d");


function escape($value)

{

    return htmlspecialchars(

        $value ?? "",
        ENT_QUOTES,
        "UTF-8"

    );

}


function leaveBadgeClass($leaveName)

{

    $name = strtolower($leaveName);


    if (str_contains($name, "vacation")) {
        return "vacation";

    }

    if (str_contains($name, "sick")) {
        return "sick";

    }

    if (str_contains($name, "emergency")) {
        return "emergency";

    }

    if (str_contains($name, "maternity")) {
        return "maternity";

    }

    if (str_contains($name, "paternity")) {
        return "paternity";

    }

    if (str_contains($name, "bereavement")) {
        return "bereavement";

    }

    return "other";

}


?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0" >

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

            <section class="employee-leave-request-page">

                <div class="employee-leave-request-header">

                    <div class="employee-leave-request-title">

                        <h1>
                            <i class="fa-solid fa-paper-plane"></i>
                            Submit Leave Request
                        </h1>

                        <p>
                            Submit your leave request for approval.
                            Please complete all required information before submitting.
                        </p>

                    </div>

                    <div class="employee-leave-request-header-action">

                        <a
                            href="leave_history.php"
                            class="employee-leave-request-history-btn"
                        >

                            <i class="fa-solid fa-clock-rotate-left"></i>
                            Leave History

                        </a>

                    </div>

                </div>

                <div class="employee-leave-card">

                    <div class="employee-leave-card-header">

                        <div class="employee-leave-card-icon">
                            <i class="fa-solid fa-user"></i>
                        </div>

                        <div>

                            <h2>Employee Information</h2>

                            <p>Current employment information</p>

                        </div>

                    </div>

                    <div class="employee-leave-info-grid">

                        <div class="employee-leave-info-item">

                            <label>
                                <i class="fa-solid fa-id-card"></i>
                                Employee Code
                            </label>

                            <p>
                                <?= escape($employee["employee_code"]); ?>
                            </p>

                        </div>

                        <div class="employee-leave-info-item">

                            <label>
                                <i class="fa-solid fa-user"></i>
                                Employee Name
                            </label>

                            <p>
                                <?= escape($employeeName); ?>
                            </p>

                        </div>

                        <div class="employee-leave-info-item">

                            <label>
                                <i class="fa-solid fa-building"></i>
                                Department
                            </label>

                            <p>
                                <?= escape($employee["department_name"] ?? "No Department"); ?>
                            </p>

                        </div>

                        <div class="employee-leave-info-item">

                            <label>
                                <i class="fa-solid fa-briefcase"></i>
                                Position
                            </label>

                            <p>
                                <?= escape($employee["position_name"] ?? "No Position"); ?>
                            </p>

                        </div>

                    </div>

                </div>

                                <form
                    id="employeeLeaveRequestForm"
                    method="POST"
                    action="leave_request_process.php"
                    enctype="multipart/form-data"
                    autocomplete="off"
                    novalidate
                >

                    <?php csrfField(); ?>

                    <div class="employee-leave-card">

                        <div class="employee-leave-card-header">

                            <div class="employee-leave-card-icon">
                                <i class="fa-solid fa-file-signature"></i>
                            </div>

                            <div>

                                <h2>Leave Request Form</h2>

                                <p>Complete all required information before submitting your leave request.</p>

                            </div>

                        </div>

                        <div class="employee-leave-form-grid">

                            <div class="employee-leave-form-group">

                                <label>

                                    <i class="fa-solid fa-layer-group"></i>
                                    Leave Type

                                    <span>*</span>

                                </label>

                                <select
                                    name="leave_type"
                                    id="leave_type"
                                >

                                    <option value="">

                                        -- Select Leave Type --

                                    </option>

                                    <?php foreach ($leaveTypes as $type): ?>

                                        <option
                                            value="<?= $type["leave_type_id"]; ?>"
                                            data-custom="<?= $type["allow_custom_reason"]; ?>"
                                        >

                                            <?= escape($type["leave_type_name"]); ?>
                                        </option>

                                    <?php endforeach; ?>

                                    <option value="others">

                                        Others

                                    </option>

                                </select>

                                <small
                                    class="employee-input-error"
                                    id="leaveTypeError"
                                ></small>

                            </div>

                            <div
                                class="employee-leave-form-group"
                                id="otherLeaveWrapper"
                                style="display:none;"
                            >

                                <label>

                                    <i class="fa-solid fa-pen"></i>
                                    Specify Leave Type

                                    <span>*</span>

                                </label>

                                <input
                                    type="text"
                                    name="other_leave_type"
                                    id="other_leave_type"
                                    maxlength="100"
                                    placeholder="Enter your leave type"
                                >

                                <small
                                    class="employee-input-error"
                                    id="otherLeaveError"
                                ></small>

                            </div>

                            <div class="employee-leave-form-group">

                                <label>

                                    <i class="fa-solid fa-calendar-days"></i>
                                    Start Date

                                    <span>*</span>

                                </label>

                                <input
                                    type="date"
                                    name="start_date"
                                    id="start_date"
                                    min="<?= $today; ?>"
                                >

                                <small
                                    class="employee-input-error"
                                    id="startDateError"
                                ></small>

                            </div>

                            <div class="employee-leave-form-group">

                                <label>

                                    <i class="fa-solid fa-calendar-check"></i>
                                    End Date

                                    <span>*</span>

                                </label>

                                <input
                                    type="date"
                                    name="end_date"
                                    id="end_date"
                                    min="<?= $today; ?>"
                                >

                                <small
                                    class="employee-input-error"
                                    id="endDateError"
                                ></small>

                            </div>

                            <div class="employee-leave-form-group">

                                <label>
                                    <i class="fa-solid fa-clock"></i>
                                    Number of Leave Days
                                </label>

                                <input
                                    type="text"
                                    name="computed_days"
                                    id="leave_days"
                                    readonly
                                    placeholder="Auto Computed"
                                >

                            </div>

                        </div>

                    </div>

                    <div class="employee-leave-card">

                        <div class="employee-leave-card-header">

                            <div class="employee-leave-card-icon">
                                <i class="fa-solid fa-comment-dots"></i>
                            </div>

                            <div>

                                <h2>Leave Reason</h2>

                                <p>Provide a clear explanation for your leave request.</p>

                            </div>

                        </div>

                        <div class="employee-leave-form-group employee-leave-full-width">

                            <label>

                                <i class="fa-solid fa-align-left"></i>
                                Reason

                                <span>*</span>

                            </label>

                            <textarea
                                name="reason"
                                id="leave_reason"
                                rows="6"
                                maxlength="500"
                                placeholder="State the reason for your leave request..."
                            ></textarea>

                            <small
                                class="employee-input-error"
                                id="reasonError"
                            ></small>

                            <div class="employee-leave-character-counter">

                                <span id="reasonCounter">
                                    0
                                </span>

                                / 500 Characters

                            </div>

                        </div>

                    </div>

                    <div class="employee-leave-card">

                        <div class="employee-leave-card-header">

                            <div class="employee-leave-card-icon">
                                <i class="fa-solid fa-paperclip"></i>
                            </div>

                            <div>

                                <h2>Supporting Attachment</h2>

                                <p>Optional supporting document for your leave request.</p>

                            </div>

                        </div>

                        <div class="employee-leave-form-group employee-leave-full-width">

                            <label>
                                <i class="fa-solid fa-file-arrow-up"></i>
                                Upload Attachment
                            </label>

                            <input
                                type="file"
                                name="attachment"
                                id="attachment"
                                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                            >

                            <small class="employee-file-note">

                                Accepted Files:
                                PDF, JPG, JPEG, PNG, DOC, DOCX

                                <br>

                                Maximum file size:
                                5MB

                            </small>

                            <small
                                class="employee-input-error"
                                id="attachmentError"
                            ></small>

                        </div>

                    </div>

                    <div class="employee-leave-guidelines">

                        <div class="employee-leave-guidelines-icon">
                            <i class="fa-solid fa-circle-exclamation"></i>
                        </div>

                        <div>

                            <h4>Leave Request Guidelines</h4>

                            <ul>

                                <li>
                                    Leave requests are subject to supervisor and HR approval.
                                </li>

                                <li>
                                    Duplicate leave requests with the same schedule are not allowed.
                                </li>

                                <li>
                                    Leave dates will be checked automatically.
                                </li>

                                <li>
                                    Weekend and holiday requests will be validated.
                                </li>

                                <li>
                                    Supporting documents may be required depending on leave type.
                                </li>

                            </ul>

                        </div>

                    </div>

                    <div class="employee-leave-action-buttons">

                        <button
                            type="reset"
                            class="employee-leave-reset-btn"
                        >

                            <i class="fa-solid fa-rotate-left"></i>
                            Reset Form
                        </button>

                        <button
                            type="submit"
                            class="employee-leave-submit-btn"
                        >

                            <i class="fa-solid fa-paper-plane"></i>
                            Submit Leave Request
                        </button>

                    </div>

                </form>

            </section>

        </main>

    </div>

</div>


<script>

    const holidays = <?= $holidayJSON; ?>;

</script>

<script src="../assets/js/employee.js"></script>

<script>

document.getElementById("employeeLeaveRequestForm").addEventListener("submit", function (e) {

    e.preventDefault();

    const form = this;

    Swal.fire({
        icon: "question",
        title: "Submit this leave request?",
        text: "This will be sent to HR for approval. Make sure your dates and reason are correct before continuing.",
        showCancelButton: true,
        confirmButtonText: "Yes, Submit",
        cancelButtonText: "Cancel",
        confirmButtonColor: "#0D47A1",
        cancelButtonColor: "#90A4AE",
        reverseButtons: true
    }).then(function (result) {

        if (result.isConfirmed) {
            form.submit();
        }

    });

});

<?php if(!empty($leaveSuccess)): ?>

Swal.fire({

    icon: "success",
    title: "Success",
    text: <?= json_encode($leaveSuccess); ?>,
    confirmButtonColor:"#0D47A1"

});

<?php endif; ?>

<?php if(!empty($leaveError)): ?>

Swal.fire({

    icon:"error",
    title:"Request Failed",
    text:<?= json_encode($leaveError); ?>,
    confirmButtonColor:"#D32F2F"

});


<?php endif; ?>


</script>


</body>


</html>