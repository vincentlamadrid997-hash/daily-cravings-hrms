<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";

$page_title = "Payroll Management";


$success = $_SESSION['payroll_success'] ?? "";
$error   = $_SESSION['payroll_error'] ?? "";

unset($_SESSION['payroll_success']);
unset($_SESSION['payroll_error']);


$totalPayroll = $conn->query("
    SELECT COUNT(*)
    FROM payroll p
    INNER JOIN employees e
        ON p.employee_id = e.employee_id
    WHERE e.employment_status = 'Active'
")->fetchColumn();


$generatedPayroll = $conn->query("
    SELECT COUNT(*)
    FROM payroll p
    INNER JOIN employees e
        ON p.employee_id = e.employee_id
    WHERE e.employment_status = 'Active'
    AND p.status = 'Generated'
")->fetchColumn();


$paidPayroll = $conn->query("
    SELECT COUNT(*)
    FROM payroll p
    INNER JOIN employees e
        ON p.employee_id = e.employee_id
    WHERE e.employment_status = 'Active'
    AND p.status = 'Paid'
")->fetchColumn();


$draftPayroll = $conn->query("
    SELECT COUNT(*)
    FROM payroll p
    INNER JOIN employees e
        ON p.employee_id = e.employee_id
    WHERE e.employment_status = 'Active'
    AND p.status = 'Draft'
")->fetchColumn();


$search = trim($_GET['search'] ?? "");


$sql = "

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

WHERE e.employment_status = 'Active'

";


$params = [];


if ($search !== "") {

    $sql .= "

    AND (

        e.employee_code LIKE ?
        OR e.first_name LIKE ?
        OR e.middle_name LIKE ?
        OR e.last_name LIKE ?
        OR p.pay_period LIKE ?
        OR p.status LIKE ?

    )

    ";


    $keyword = "%{$search}%";


    $params = [

        $keyword,
        $keyword,
        $keyword,
        $keyword,
        $keyword,
        $keyword

    ];

}


$sql .= "

ORDER BY p.created_at DESC

";


$stmt = $conn->prepare($sql);

$stmt->execute($params);


$payrolls = $stmt->fetchAll(PDO::FETCH_ASSOC);


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

            <section class="hr-pay-page">

                <div class="hr-pay-summary-grid">

                    <div class="hr-pay-summary-card blue">

                        <div class="hr-pay-summary-icon">

                            <i class="fa-solid fa-money-check-dollar"></i>

                        </div>

                        <div class="hr-pay-summary-content">

                            <span>
                                Total Payroll
                            </span>

                            <h2>
                                <?= $totalPayroll; ?>
                            </h2>

                            <p>Employee payroll records</p>

                        </div>

                    </div>

                    <div class="hr-pay-summary-card green">

                        <div class="hr-pay-summary-icon">

                            <i class="fa-solid fa-file-circle-check"></i>

                        </div>

                        <div class="hr-pay-summary-content">

                            <span>
                                Generated Payroll
                            </span>

                            <h2>
                                <?= $generatedPayroll; ?>
                            </h2>

                            <p>Ready for payment</p>

                        </div>

                    </div>

                    <div class="hr-pay-summary-card purple">

                        <div class="hr-pay-summary-icon">

                            <i class="fa-solid fa-circle-check"></i>

                        </div>

                        <div class="hr-pay-summary-content">

                            <span>
                                Paid Payroll
                            </span>

                            <h2>
                                <?= $paidPayroll; ?>
                            </h2>

                            <p>Released salary</p>

                        </div>

                    </div>

                    <div class="hr-pay-summary-card orange">

                        <div class="hr-pay-summary-icon">

                            <i class="fa-solid fa-file-pen"></i>

                        </div>

                        <div class="hr-pay-summary-content">

                            <span>
                                Draft Payroll
                            </span>

                            <h2>
                                <?= $draftPayroll; ?>
                            </h2>

                            <p>Pending review</p>

                        </div>

                    </div>

                </div>

                <div class="hr-pay-header">

                    <div class="hr-pay-title">

                        <h1>

                            <i class="fa-solid fa-money-check-dollar"></i>
                            Payroll Management

                        </h1>

                        <p>Manage employee monthly payroll and payslips.</p>

                    </div>

                    <div class="hr-pay-header-action">

                        <a 
                            href="payroll_generate.php"
                            class="hr-pay-add-btn"
                        >

                            <i class="fa-solid fa-plus"></i>
                            Generate Payroll
                        </a>

                    </div>

                </div>

                <div class="hr-pay-table-card">

                    <div class="hr-pay-search">

                        <div class="hr-pay-search-box">

                            <i class="fa-solid fa-magnifying-glass"></i>

                            <input

                                type="text"
                                id="payrollSearch"
                                placeholder="Search employee, pay period or status..."
                                autocomplete="off"
                            >

                        </div>

                    </div>

                    <div class="hr-pay-table-wrapper">

                        <table class="hr-pay-table">

                            <thead>

                                <tr>

                                    <th>Employee</th>
                                    <th>Pay Period</th>
                                    <th>Basic Salary</th>
                                    <th>Gross Pay</th>
                                    <th>Net Pay</th>
                                    <th>Status</th>
                                    <th width="220">
                                        Action
                                    </th>
                                    
                                </tr>

                            </thead>

                            <tbody id="payrollTableBody">

                                <?php if (!empty($payrolls)): ?>

                                    <?php foreach ($payrolls as $payroll): ?>

                                        <tr class="payroll-row">

<td>

    <div class="hr-pay-name">

        <div class="hr-pay-avatar">

            <i class="fa-solid fa-user"></i>

        </div>

        <div>

            <strong>

                <?= htmlspecialchars(
                    trim(
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
                    )
                ); ?>

            </strong>

            <br>

            <small>

                <?= htmlspecialchars($payroll['employee_code']); ?>

            </small>

        </div>

    </div>

</td>

<td>

    <?= htmlspecialchars($payroll['pay_period']); ?>

</td>

<td>

    ₱<?= number_format($payroll['basic_salary'], 2); ?>

</td>

<td>

    ₱<?= number_format($payroll['gross_pay'], 2); ?>

</td>

<td>

    <strong>

        ₱<?= number_format($payroll['net_pay'], 2); ?>

    </strong>

</td>

<td>

    <span class="hr-pay-status <?= strtolower($payroll['status']); ?>">

        <?php if ($payroll['status'] == "Paid"): ?>

            <i class="fa-solid fa-money-check-dollar"></i>

        <?php elseif ($payroll['status'] == "Generated"): ?>

            <i class="fa-solid fa-file-circle-check"></i>

        <?php else: ?>

            <i class="fa-solid fa-file-pen"></i>

        <?php endif; ?>

        <?= htmlspecialchars($payroll['status']); ?>

    </span>

</td>

<td>

    <div class="hr-pay-actions">

                <form action="payroll_view.php" method="POST">

            <?php csrfField(); ?>

            <input
                type="hidden"
                name="payroll_id"
                value="<?= $payroll['payroll_id']; ?>"
            >

            <button
                type="submit"
                class="hr-pay-action view"
                title="View Payroll"
            >

                <i class="fa-solid fa-eye"></i>

            </button>

        </form>

                <form action="payroll_edit.php" method="POST">

            <?php csrfField(); ?>

            <input
                type="hidden"
                name="payroll_id"
                value="<?= $payroll['payroll_id']; ?>"
            >

            <button
                type="submit"
                class="hr-pay-action edit"
                title="Edit Payroll"
            >

                <i class="fa-solid fa-pen"></i>

            </button>

        </form>

                <form action="payslip_download.php" method="POST">

            <?php csrfField(); ?>

            <input
                type="hidden"
                name="payroll_id"
                value="<?= $payroll['payroll_id']; ?>"
            >

            <button
                type="submit"
                class="hr-pay-action download"
                title="Print Payslip"
            >

                <i class="fa-solid fa-print"></i>

            </button>

        </form>

            <form action="payroll_status.php" method="POST" class="hr-pay-mark-paid-form">
                <?php csrfField(); ?>
            <input
                type="hidden"
                name="payroll_id"
                value="<?= $payroll['payroll_id']; ?>"
            >

            <button
                type="submit"
                class="hr-pay-action paid"
                title="Mark as Paid"

                <?= 
                    $payroll['status'] == "Paid"
                    ?
                    "disabled"
                    :
                    ""
                ?>

            >

                                <i class="fa-solid fa-money-bill-wave"></i>

            </button>

                </form>

        <?php if ($payroll['status'] !== "Paid"): ?>

        <form action="payroll_delete.php" method="POST" class="hr-pay-delete-form">

            <?php csrfField(); ?>

            <input
                type="hidden"
                name="payroll_id"
                value="<?= $payroll['payroll_id']; ?>"
            >

            <button
                type="submit"
                class="hr-pay-action delete"
                title="Delete Payroll"
            >

                <i class="fa-solid fa-trash"></i>

            </button>

        </form>

        <?php endif; ?>

    </div>

</td>

</tr>

<?php endforeach; ?>

<?php endif; ?>

<tr id="noPayrollResult" style="display:none;">

    <td colspan="7">

        <div class="hr-pay-empty">

            <i class="fa-solid fa-magnifying-glass"></i>

            <h3>No Payroll Found</h3>

            <p>No records matched your search.</p>

        </div>

    </td>

</tr>

<?php if (empty($payrolls)): ?>

<tr>

    <td colspan="7">

        <div class="hr-pay-empty">

            <i class="fa-solid fa-money-check-dollar"></i>

            <h3>No Payroll Available</h3>

            <p>Generate your first employee payroll.</p>

        </div>

    </td>

</tr>

<?php endif; ?>

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

document.addEventListener("DOMContentLoaded", function(){

    Swal.fire({

        icon: "success",

        title: "Success!",

        text: <?= json_encode($success); ?>,

        confirmButtonColor: "#003DA5",

        confirmButtonText: "OK"

    });

});

</script>

<?php endif; ?>

<?php if (!empty($error)): ?>

<script>

document.addEventListener("DOMContentLoaded", function(){

    Swal.fire({

        icon: "error",

        title: "Unable to Continue",

        text: <?= json_encode($error); ?>,

        confirmButtonColor: "#003DA5",

        confirmButtonText: "OK"

    });


});

</script>

<?php endif; ?>

<script>

document.addEventListener("DOMContentLoaded", function(){

    const searchInput = document.getElementById(
        "payrollSearch"
    );

    const rows = document.querySelectorAll(
        ".payroll-row"
    );

    const noResult = document.getElementById(
        "noPayrollResult"
    );


    if (!searchInput) {

        return;

    }


    searchInput.addEventListener(
        "keyup",
        function(){

            let keyword = this.value
                .toLowerCase()
                .trim();

            let visibleRows = 0;

            rows.forEach(function(row){

                let data = row.textContent
                    .toLowerCase();

                if (data.includes(keyword)) {

                    row.style.display = "";

                    visibleRows++;

                } else {

                    row.style.display = "none";

                }

            });


            if (noResult) {

                if (visibleRows === 0) {

                    noResult.style.display = "";

                } else {

                    noResult.style.display = "none";

                }

            }

        }
    );

});

</script>


<style>

    .hr-pay-action.delete{

        background:#FDECEC;
        color:#C62828;

    }

    .hr-pay-action.delete:hover{

        background:#C62828;
        color:#ffffff;

    }

</style>

<script>

document.querySelectorAll(".hr-pay-delete-form").forEach(function (form) {

    form.addEventListener("submit", function (e) {

        e.preventDefault();

        Swal.fire({
            title: "Delete this payroll record?",
            text: "This cannot be undone. Only Draft or Generated payroll can be deleted — Paid records are protected.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#C62828",
            cancelButtonColor: "#90A4AE",
            confirmButtonText: "Yes, Delete"
        }).then(function (result) {

            if (result.isConfirmed) {
                form.submit();
            }

        });

    });

});

</script>

<script>
document.querySelectorAll(".hr-pay-mark-paid-form").forEach(function (form) {

    form.addEventListener("submit", function (e) {

        const button = form.querySelector("button");

        if (button.disabled) {
            return;
        }

        e.preventDefault();

        Swal.fire({
            title: "Mark this payroll as Paid?",
            text: "This action cannot be undone — once marked Paid, this record can no longer be edited or deleted.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#2E7D32",
            cancelButtonColor: "#90A4AE",
            confirmButtonText: "Yes, Mark as Paid"
        }).then(function (result) {

            if (result.isConfirmed) {
                form.submit();
            }

        });

    });

});
</script>

<script>
document.querySelectorAll(".hr-pay-mark-paid-form").forEach(function (form) {

    form.addEventListener("submit", function (e) {

        const button = form.querySelector("button");

        if (button.disabled) {
            return;
        }

        e.preventDefault();

        Swal.fire({
            title: "Mark this payroll as Paid?",
            text: "This action cannot be undone — once marked Paid, this record can no longer be edited or deleted.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#2E7D32",
            cancelButtonColor: "#90A4AE",
            confirmButtonText: "Yes, Mark as Paid"
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