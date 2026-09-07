<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";


$page_title = "Edit Payroll";


if (!isset($_POST['payroll_id'])) {

    $_SESSION['payroll_error'] = "Invalid payroll selected.";

    header("Location: payroll.php");
    exit;

}


$payroll_id = (int) $_POST['payroll_id'];


$alert = [
    "type" => "",
    "message" => ""
];


$stmt = $conn->prepare("

    SELECT

        p.payroll_id,
        p.employee_id,
        p.pay_period,
        p.basic_salary,
        p.allowances,
        p.deductions,
        p.gross_pay,
        p.net_pay,
        p.status,
        p.created_at,

        e.employee_code,
        e.first_name,
        e.middle_name,
        e.last_name,

        d.department_name,

        pos.position_name

    FROM payroll p

    INNER JOIN employees e
        ON p.employee_id = e.employee_id

    LEFT JOIN departments d
        ON e.department_id = d.department_id

    LEFT JOIN positions pos
        ON e.position_id = pos.position_id

    WHERE p.payroll_id = ?

    LIMIT 1

");


$stmt->execute([
    $payroll_id
]);


$payroll = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$payroll) {

    $_SESSION['payroll_error'] = "Payroll record not found.";

    header("Location: payroll.php");
    exit;

}


if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST['update_payroll'])
) {

    requireCSRFToken("payroll_edit.php");


    $current_status = $payroll['status'];

    $new_status = $_POST['status'];


    if ($current_status === "Paid") {

        $_SESSION['payroll_error'] =
            "Paid payroll can no longer be edited.";

        header("Location: payroll.php");
        exit;

    }


    if (
        $current_status === "Generated"
        &&
        $new_status === "Draft"
    ) {

        $_SESSION['payroll_error'] =
            "Generated payroll cannot return to Draft.";

        header("Location: payroll.php");
        exit;

    }


    $basic_salary = (float) $_POST['basic_salary'];

    $allowances = (float) $_POST['allowances'];

    $deductions = (float) $_POST['deductions'];


    $gross_pay = 
        $basic_salary + $allowances;


    $net_pay =
        $gross_pay - $deductions;


    if ($net_pay < 0) {

        $net_pay = 0;

    }


    $update = $conn->prepare("

        UPDATE payroll

        SET

            basic_salary = ?,
            allowances = ?,
            deductions = ?,
            gross_pay = ?,
            net_pay = ?,
            status = ?

        WHERE payroll_id = ?

    ");


    $success = $update->execute([

        $basic_salary,
        $allowances,
        $deductions,
        $gross_pay,
        $net_pay,
        $new_status,
        $payroll_id

    ]);


    if ($success) {

        $_SESSION['payroll_success'] =
            "Payroll updated successfully.";

        header("Location: payroll.php");
        exit;

    } 
    else {

        $alert["type"] = "error";

        $alert["message"] =
            "Failed to update payroll.";

    }


}


$employee_name = trim(

    $payroll['first_name']
    . " "
    .
    (
        !empty($payroll['middle_name'])
        ?
        $payroll['middle_name'] . " "
        :
        ""
    )
    .
    $payroll['last_name']

);


$attendance_deduction =

    $payroll['gross_pay']
    -
    $payroll['net_pay']
    -
    $payroll['deductions'];


if ($attendance_deduction < 0) {

    $attendance_deduction = 0;

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

    <link rel="stylesheet" href="../assets/css/payroll_crud.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


</head>

<body>

<div class="hr-wrapper">

    <?php require_once "includes/sidebar.php"; ?>

    <div class="hr-content-wrapper">

        <?php require_once "includes/header.php"; ?>

        <main class="hr-main">

            <section class="hr-pay-crud-page">

                <div class="hr-pay-crud-header">

                    <div class="hr-pay-crud-title">

                        <h1>

                            <i class="fa-solid fa-pen-to-square"></i>
                            Edit Payroll

                        </h1>

                        <p>Update employee payroll information.</p>

                    </div>

                    <div class="hr-pay-crud-header-actions">

                        <a 
                            href="payroll.php"
                            class="hr-pay-crud-back-btn"
                        >

                            <i class="fa-solid fa-arrow-left"></i>
                            Back to Payroll
                        </a>

                    </div>

                </div>

                <div class="hr-pay-crud-card">

                    <div class="hr-pay-crud-card-header">

                        <div class="hr-pay-crud-icon">

                            <i class="fa-solid fa-money-check-dollar"></i>

                        </div>

                        <div>

                            <h2>Payroll Information</h2>

                            <p>Edit salary details and payroll status.</p>

                        </div>

                    </div>

                                                            <form method="POST" id="payrollEditForm" novalidate>

                        <?php csrfField(); ?>

                        <input 
                            type="hidden"
                            name="payroll_id"
                            value="<?= $payroll_id; ?>"
                        >

                        <div class="hr-pay-crud-form-grid">

<div class="hr-pay-crud-form-group">

    <label>

        <i class="fa-solid fa-user"></i>
        Employee

    </label>

    <input

        type="text"
        readonly
        class="hr-pay-readonly"
        value="<?= htmlspecialchars($employee_name); ?>"
    >

</div>

<div class="hr-pay-crud-form-group">

    <label>

        <i class="fa-solid fa-calendar-days"></i>
        Payroll Period

    </label>

    <input

        type="text"
        readonly
        class="hr-pay-readonly"
        value="<?= htmlspecialchars($payroll['pay_period']); ?>"
    >

</div>

<div class="hr-pay-crud-form-group">

    <label>

        <i class="fa-solid fa-file-circle-check"></i>
        Payroll Status

    </label>

    <?php if ($payroll['status'] === "Paid"): ?>

        <input

            type="text"
            readonly
            class="hr-pay-readonly"
            value="Paid"
        >

        <input

            type="hidden"
            name="status"
            value="Paid"
        >

    <?php elseif ($payroll['status'] === "Generated"): ?>

        <select

            name="status"
            required
        >

            <option value="Generated" selected>
                Generated
            </option>

            <option value="Paid">
                Paid
            </option>

        </select>

    <?php else: ?>

        <select

            name="status"
            required
        >

            <option value="Draft" selected>
                Draft
            </option>

            <option value="Generated">
                Generated
            </option>

        </select>

    <?php endif; ?>

</div>

<div class="hr-pay-crud-form-group">

    <label>

        <i class="fa-solid fa-peso-sign"></i>
        Basic Salary

    </label>

    <div class="hr-pay-crud-input-icon">

        <span>
            ₱
        </span>

        <input

            type="number"
            step="0.01"
            name="basic_salary"
            value="<?= $payroll['basic_salary']; ?>"
            <?= $payroll['status']=="Paid" ? "readonly" : ""; ?>
        >

    </div>

</div>

<div class="hr-pay-crud-form-group">

    <label>

        <i class="fa-solid fa-hand-holding-dollar"></i>
        Allowances

    </label>

    <div class="hr-pay-crud-input-icon">

        <span>
            ₱
        </span>

        <input

            type="number"
            step="0.01"
            name="allowances"
            value="<?= $payroll['allowances']; ?>"
            <?= $payroll['status']=="Paid" ? "readonly" : ""; ?>
        >

    </div>

</div>

<div class="hr-pay-crud-form-group">

    <label>

        <i class="fa-solid "fa-minus-circle"></i>
        Deductions

    </label>

    <div class="hr-pay-crud-input-icon">

        <span>
            ₱
        </span>

        <input

            type="number"
            step="0.01"
            name="deductions"
            value="<?= $payroll['deductions']; ?>"
            <?= $payroll['status']=="Paid" ? "readonly" : ""; ?>
        >

    </div>

</div>

<div class="hr-pay-crud-form-group">

    <label>

        <i class="fa-solid fa-calendar-xmark"></i>
        Attendance Deduction

    </label>

    <div class="hr-pay-crud-input-icon">

        <span>
            ₱
        </span>

        <input

            type="text"
            readonly
            class="hr-pay-readonly"
            value="<?= number_format($attendance_deduction,2); ?>"
        >

    </div>

</div>

<div class="hr-pay-crud-form-group">

    <label>

        <i class="fa-solid fa-arrow-trend-up"></i>
        Gross Pay

    </label>

    <div class="hr-pay-crud-input-icon">

        <span>
            ₱
        </span>

        <input

            type="text"
            id="grossPay"
            readonly
            class="hr-pay-readonly"
            value="<?= number_format($payroll['gross_pay'],2); ?>"
        >

    </div>

</div>

<div class="hr-pay-crud-form-group">

    <label>

        <i class="fa-solid fa-wallet"></i>
        Net Pay

    </label>

    <div class="hr-pay-crud-input-icon">

        <span>
            ₱
        </span>

        <input

            type="text"
            id="netPay"
            readonly
            class="hr-pay-readonly"
            value="<?= number_format($payroll['net_pay'],2); ?>"
        >

    </div>

</div>

<div class="hr-pay-crud-actions">

    <?php if ($payroll['status'] === "Paid"): ?>

        <button

            type="button"
            class="hr-pay-crud-submit-btn"
            disabled
            style="opacity:.6;cursor:not-allowed;"
        >

            <i class="fa-solid fa-lock"></i>
            Payroll Locked

        </button>

    <?php else: ?>

                <button

            type="submit"
            id="payrollEditSaveBtn"
            disabled
            name="update_payroll"
            class="hr-pay-crud-submit-btn"
        >

            <i class="fa-solid fa-save"></i>
            Save Changes

        </button>

    <?php endif; ?>

</div>

</div>

</form>

</div>

<?php if (!empty($alert['message'])): ?>

<script>

document.addEventListener("DOMContentLoaded", function () {

    Swal.fire({

        icon: "<?= $alert['type']; ?>",

        title: "Unable to Update",

        text: <?= json_encode($alert['message']); ?>,

        confirmButtonColor: "#003DA5"

    });

});

</script>

<?php endif; ?>

<script>

const basicSalary = document.querySelector(
    '[name="basic_salary"]'
);

const allowanceInput = document.querySelector(
    '[name="allowances"]'
);

const deductionInput = document.querySelector(
    '[name="deductions"]'
);

const grossPay = document.getElementById(
    "grossPay"
);

const netPay = document.getElementById(
    "netPay"
);

function computePayroll() {

    let basic =
        parseFloat(basicSalary.value) || 0;

    let allowance =
        parseFloat(allowanceInput.value) || 0;

    let deduction =
        parseFloat(deductionInput.value) || 0;

    let gross =
        basic + allowance;

    let net =
        gross - deduction;

    if (net < 0) {

        net = 0;

    }

    grossPay.value =

        gross.toLocaleString(
            "en-PH",
            {
                minimumFractionDigits: 2
            }
        );

    netPay.value =

        net.toLocaleString(
            "en-PH",
            {
                minimumFractionDigits: 2
            }
        );

}


if (basicSalary) {

    basicSalary.addEventListener(
        "input",
        computePayroll
    );

}

if (allowanceInput) {

    allowanceInput.addEventListener(
        "input",
        computePayroll
    );

}

if (deductionInput) {

    deductionInput.addEventListener(
        "input",
        computePayroll
    );

}

</script>


<script>
(function () {

    const form = document.getElementById("payrollEditForm");
    const saveBtn = document.getElementById("payrollEditSaveBtn");
    const initialState = new URLSearchParams(new FormData(form)).toString();

    function checkForChanges() {
        const currentState = new URLSearchParams(new FormData(form)).toString();
        saveBtn.disabled = (currentState === initialState);
    }

    form.addEventListener("input", checkForChanges);
    form.addEventListener("change", checkForChanges);

})();

document.getElementById("payrollEditForm").addEventListener("submit", function (e) {

    e.preventDefault();

    const form = this;

    Swal.fire({
        icon: "question",
        title: "Save changes?",
        text: "This will update this payroll record.",
        showCancelButton: true,
        confirmButtonText: "Yes, Save",
        cancelButtonText: "Cancel",
        confirmButtonColor: "#003DA5",
        reverseButtons: true
    }).then(function (result) {

        if (result.isConfirmed) {
            form.submit();
        }

    });

});
</script>

<script src="../assets/js/hr.js"></script>


</body>

</html>