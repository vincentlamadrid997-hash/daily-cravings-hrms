<?php


session_start();

date_default_timezone_set("Asia/Manila");

require_once "../auth/employee_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";

$page_title = "Employment Contracts";


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

function moneyFormat($amount)
{
    if (empty($amount)) {
        return "₱0.00";
    }

    return "₱" . number_format(
        $amount,
        2
    );
}

function contractStatus($status)
{
    switch ($status) {
        case "Active":
            return [
                "class" => "active",
                "icon"  => "fa-circle-check"
            ];

        case "Expired":
            return [
                "class" => "expired",
                "icon"  => "fa-circle-xmark"
            ];

        default:
            return [
                "class" => "expired",
                "icon"  => "fa-circle-info"
            ];
    }
}


$employeeStmt = $conn->prepare("

SELECT
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
    $_SESSION["error"] = "Employee record not found.";

    header("Location: dashboard.php");
    exit;
}


$employee_name = trim(
    $employee["first_name"]
    . " "
    . ($employee["middle_name"] ?? "")
    . " "
    . $employee["last_name"]
);


$totalStmt = $conn->prepare("

SELECT COUNT(*)

FROM contracts

WHERE employee_id = :employee_id

");

$totalStmt->execute([
    ":employee_id" => $employee_id
]);

$total_contracts = $totalStmt->fetchColumn();

$activeStmt = $conn->prepare("

SELECT COUNT(*)

FROM contracts

WHERE employee_id = :employee_id

AND status = 'Active'

");

$activeStmt->execute([
    ":employee_id" => $employee_id
]);

$active_contracts = $activeStmt->fetchColumn();

$expiringStmt = $conn->prepare("

SELECT COUNT(*)

FROM contracts

WHERE employee_id = :employee_id

AND status = 'Active'

AND end_date BETWEEN CURDATE()

AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)

");

$expiringStmt->execute([
    ":employee_id" => $employee_id
]);

$expiring_contracts = $expiringStmt->fetchColumn();

$documentStmt = $conn->prepare("

SELECT COUNT(*)

FROM contracts

WHERE employee_id = :employee_id

AND file IS NOT NULL

AND file != ''

");

$documentStmt->execute([
    ":employee_id" => $employee_id
]);

$contract_documents = $documentStmt->fetchColumn();


$contractStmt = $conn->prepare("

SELECT
    c.contract_id,
    c.contract_type,
    c.start_date,
    c.end_date,
    c.salary,
    c.description,
    c.status,
    c.file,
    c.created_at

FROM contracts c

WHERE c.employee_id = :employee_id

ORDER BY c.created_at DESC

");

$contractStmt->execute([
    ":employee_id" => $employee_id
]);

$contracts = $contractStmt->fetchAll(PDO::FETCH_ASSOC);

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

                <section class="employee-contract-page">

                    <div class="employee-contract-header">

                        <div class="employee-contract-title">

                            <h1>
                                <i class="fa-solid fa-file-contract"></i>
                                Employment Contracts
                            </h1>

                            <p>View your employment contracts and available documents.</p>

                        </div>

                    </div>

                    <div class="employee-contract-summary-grid">

                        <div class="employee-contract-summary-card blue">

                            <div class="employee-contract-summary-icon">
                                <i class="fa-solid fa-file-contract"></i>
                            </div>

                            <div class="employee-contract-summary-content">

                                <span>Total Contracts</span>

                                <h2><?= $total_contracts; ?></h2>

                                <p>All contracts created</p>

                            </div>

                        </div>

                        <div class="employee-contract-summary-card green">

                            <div class="employee-contract-summary-icon">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>

                            <div class="employee-contract-summary-content">

                                <span>Active Contracts</span>

                                <h2><?= $active_contracts; ?></h2>

                                <p>Currently active</p>

                            </div>

                        </div>

                        <div class="employee-contract-summary-card orange">

                            <div class="employee-contract-summary-icon">
                                <i class="fa-solid fa-calendar-days"></i>
                            </div>

                            <div class="employee-contract-summary-content">

                                <span>Expiring Soon</span>

                                <h2><?= $expiring_contracts; ?></h2>

                                <p>Within 30 days</p>

                            </div>

                        </div>

                        <div class="employee-contract-summary-card purple">

                            <div class="employee-contract-summary-icon">
                                <i class="fa-solid fa-file-pdf"></i>
                            </div>

                            <div class="employee-contract-summary-content">

                                <span>Documents</span>

                                <h2><?= $contract_documents; ?></h2>

                                <p>Available files</p>

                            </div>

                        </div>

                    </div>

                    <div class="employee-contract-info-card">

                        <div class="employee-contract-info-header">

                            <div class="employee-contract-info-icon">
                                <i class="fa-solid fa-user"></i>
                            </div>

                            <div>

                                <h2>Employee Information</h2>

                                <p>Employment profile details</p>

                            </div>

                        </div>

                        <div class="employee-contract-info-grid">

                            <div class="employee-contract-info-item">

                                <label>
                                    <i class="fa-solid fa-id-card"></i>
                                    Employee Code
                                </label>

                                <p>
                                    <?= clean($employee["employee_code"]); ?>
                                </p>

                            </div>

                            <div class="employee-contract-info-item">

                                <label>
                                    <i class="fa-solid fa-user"></i>
                                    Full Name
                                </label>

                                <p>
                                    <?= clean($employee_name); ?>
                                </p>

                            </div>

                            <div class="employee-contract-info-item">

                                <label>
                                    <i class="fa-solid fa-building"></i>
                                    Department
                                </label>

                                <p>
                                    <?= clean($employee["department_name"]); ?>
                                </p>

                            </div>

                            <div class="employee-contract-info-item">

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

                    <div class="employee-contract-list-card">

                        <div class="employee-contract-list-header">

                            <div class="employee-contract-info-icon">
                                <i class="fa-solid fa-folder-open"></i>
                            </div>

                            <div>

                                <h2>My Employment Contracts</h2>

                                <p>Available contract records</p>

                            </div>

                        </div>

                        <div class="employee-contract-tools">

                            <div class="employee-contract-search-box">
                                <i class="fa-solid fa-magnifying-glass"></i>

                                <input
                                    type="text"
                                    id="contractSearch"
                                    placeholder="Search contract type..."
                                >

                            </div>

                            <div class="employee-contract-filter-box">

                                <select id="contractStatusFilter">

                                    <option value="">
                                        All Status
                                    </option>

                                    <option value="Active">
                                        Active
                                    </option>

                                    <option value="Expired">
                                        Expired
                                    </option>

                                </select>

                            </div>

                        </div>

                        <div class="employee-contract-table-wrapper">

                            <table
                                class="employee-contract-table"
                                id="contractHistoryTable"
                            >

                                <thead>

                                    <tr>

                                        <th>Contract Type</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Salary</th>
                                        <th>Status</th>
                                        <th>Action</th>

                                    </tr>

                                </thead>

                                <tbody>

                                    <?php if (!empty($contracts)): ?>

                                        <?php foreach ($contracts as $contract): ?>

                                            <?php

                                            $status = contractStatus(
                                                $contract["status"]
                                            );

                                            ?>

                                            <tr>

                                                <td>
                                                    <?= clean($contract["contract_type"]); ?>
                                                </td>

                                                <td>
                                                    <?= dateFormat($contract["start_date"]); ?>
                                                </td>

                                                <td>
                                                    <?= dateFormat($contract["end_date"]); ?>
                                                </td>

                                                <td>
                                                    <?= moneyFormat($contract["salary"]); ?>
                                                </td>

                                                <td>

                                                    <span class="employee-contract-status <?= $status["class"]; ?>">

                                                        <i class="fa-solid <?= $status["icon"]; ?>"></i>

                                                        <?= clean($contract["status"]); ?>

                                                    </span>

                                                </td>

                                                <td>

                                                    <div class="employee-contract-actions">

                                                                                                                <form method="POST" action="contract_view.php">

                                                            <?php csrfField(); ?>

                                                            <input
                                                                type="hidden"
                                                                name="contract_id"
                                                                value="<?= htmlspecialchars($contract["contract_id"]); ?>"
                                                            >

                                                            <button
                                                                type="submit"
                                                                class="employee-contract-view-btn"
                                                            >

                                                                <i class="fa-solid fa-eye"></i>
                                                                View Details

                                                            </button>

                                                        </form>

                                                                                                                                                                        <form method="POST" action="contract_pdf.php">

                                                            <?php csrfField(); ?>

                                                            <input
                                                                type="hidden"
                                                                name="contract_id"
                                                                value="<?= htmlspecialchars($contract["contract_id"]); ?>"
                                                            >

                                                            <button
                                                                type="submit"
                                                                class="employee-contract-download-btn"
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

                                            <td colspan="6">

                                                No Employment Contract Available

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


<script src="../assets/js/employee.js"></script>


</body>

</html>