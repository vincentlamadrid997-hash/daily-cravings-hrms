<?php


session_start();

date_default_timezone_set("Asia/Manila");

require_once "../auth/employee_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";

$page_title = "Employment Contract Details";


$employee_id = $_SESSION["employee_id"] ?? 0;

if (!$employee_id) {

    header("Location: ../login.php");
    exit;

}

requireCSRFToken("employment_contracts.php");


function clean($value)
{

    return htmlspecialchars(
        $value ?? "",
        ENT_QUOTES,
        "UTF-8"
    );

}

function formatDate($date)
{

    if (empty($date)) {

        return "-";

    }

    return date(
        "F d, Y",
        strtotime($date)
    );

}

function formatMoney($amount)
{

    if ($amount === null || $amount === "") {

        return "₱0.00";

    }

    return "₱" . number_format(
        $amount,
        2
    );

}

function contractBadge($status)
{

    switch ($status) {

        case "Active":

            return [
                "class" => "approved",
                "icon" => "fa-circle-check"
            ];


        case "Expired":

            return [
                "class" => "rejected",
                "icon" => "fa-circle-xmark"
            ];


        default:

            return [
                "class" => "cancelled",
                "icon" => "fa-circle-info"
            ];

    }

}


if (isset($_POST["contract_id"])) {

    $_SESSION["selected_contract"] = (int) $_POST["contract_id"];

}


if (!isset($_SESSION["selected_contract"])) {

    header("Location: employee_contracts.php");
    exit;

}


$contract_id = (int) $_SESSION["selected_contract"];


$stmt = $conn->prepare("

SELECT

    c.contract_id,
    c.contract_type,
    c.start_date,
    c.end_date,
    c.salary,
    c.description,
    c.status,
    c.file,
    c.created_at,

    e.employee_code,
    e.first_name,
    e.middle_name,
    e.last_name,

    d.department_name,
    p.position_name

FROM contracts c

INNER JOIN employees e
ON c.employee_id = e.employee_id

LEFT JOIN departments d
ON e.department_id = d.department_id

LEFT JOIN positions p
ON e.position_id = p.position_id

WHERE c.contract_id = :contract_id

AND c.employee_id = :employee_id

LIMIT 1

");

$stmt->execute([

    ":contract_id" => $contract_id,
    ":employee_id" => $employee_id

]);


$contract = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$contract) {

    $_SESSION["error"] = "Employment Contract not found.";

    header("Location: employee_contracts.php");
    exit;

}


$employee_name = trim(

    $contract["first_name"] .
    " " .
    ($contract["middle_name"] ?? "") .
    " " .
    $contract["last_name"]

);


$status = contractBadge(
    $contract["status"]
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

            <section class="employee-contract-details-page">

                <div class="employee-contract-details-header">

                    <div class="employee-contract-details-title">

                        <h1>
                            <i class="fa-solid fa-file-contract"></i>
                            Employment Contract Details
                        </h1>

                        <p>Review your employment contract information and available documents.</p>

                    </div>

                    <a
                        href="employment_contracts.php"
                        class="employee-contract-details-back-btn"
                    >

                        <i class="fa-solid fa-arrow-left"></i>
                        Back to Contracts
                    </a>

                </div>

                <div class="employee-contract-details-card">

                    <div class="employee-contract-details-card-header">

                        <div class="employee-contract-details-icon">
                            <i class="fa-solid fa-file-contract"></i>
                        </div>

                        <div>

                            <h2>Employment Contract Information</h2>

                            <p>Complete contract details and employment information.</p>

                        </div>

                    </div>

                    <div class="employee-contract-details-grid">

                        <div class="employee-contract-details-group">

                            <label>
                                <i class="fa-solid fa-file-signature"></i>
                                Contract Type
                            </label>

                            <p>
                                <?= clean($contract["contract_type"]); ?>
                            </p>

                        </div>

                        <div class="employee-contract-details-group">

                            <label>
                                <i class="fa-solid fa-user"></i>
                                Employee Name
                            </label>

                            <p>
                                <?= clean($employee_name); ?>
                            </p>

                        </div>

                        <div class="employee-contract-details-group">

                            <label>
                                <i class="fa-solid fa-id-card"></i>
                                Employee Code
                            </label>

                            <p>
                                <?= clean($contract["employee_code"]); ?>
                            </p>

                        </div>

                        <div class="employee-contract-details-group">

                            <label>
                                <i class="fa-solid fa-building"></i>
                                Department
                            </label>

                            <p>
                                <?= clean($contract["department_name"]); ?>
                            </p>

                        </div>

                        <div class="employee-contract-details-group">

                            <label>
                                <i class="fa-solid fa-briefcase"></i>
                                Position
                            </label>

                            <p>
                                <?= clean($contract["position_name"]); ?>
                            </p>

                        </div>

                        <div class="employee-contract-details-group">

                            <label>
                                <i class="fa-solid fa-calendar-check"></i>
                                Contract Status
                            </label>

                            <p>

                                <span class="employee-status-badge <?= $status["class"]; ?>">

                                    <i class="fa-solid <?= $status["icon"]; ?>"></i>
                                    <?= clean($contract["status"]); ?>

                                </span>

                            </p>

                        </div>

                    </div>

                </div>

                <div class="employee-contract-details-card">

                    <div class="employee-contract-details-card-header">

                        <div class="employee-contract-details-icon">
                            <i class="fa-solid fa-circle-info"></i>
                        </div>

                        <div>

                            <h2>Contract Summary</h2>

                            <p>Important contract dates and financial details.</p>

                        </div>

                    </div>

                    <div class="employee-contract-details-summary">

                        <div class="employee-contract-summary-box">

                            <span>
                                <i class="fa-solid fa-calendar-plus"></i>
                                Contract Start Date
                            </span>

                            <h3>
                                <?= formatDate($contract["start_date"]); ?>
                            </h3>

                        </div>

                        <div class="employee-contract-summary-box">

                            <span>
                                <i class="fa-solid fa-calendar-xmark"></i>
                                Contract End Date
                            </span>

                            <h3>
                                <?= formatDate($contract["end_date"]); ?>
                            </h3>

                        </div>

                        <div class="employee-contract-summary-box">

                            <span>
                                <i class="fa-solid fa-money-bill-wave"></i>
                                Monthly Salary
                            </span>

                            <h3>
                                <?= formatMoney($contract["salary"]); ?>
                            </h3>

                        </div>

                    </div>

                </div>

                <div class="employee-contract-details-card">

                    <div class="employee-contract-details-card-header">

                        <div class="employee-contract-details-icon">
                            <i class="fa-solid fa-align-left"></i>
                        </div>

                        <div>

                            <h2>Contract Description</h2>

                            <p>Additional information regarding your contract.</p>

                        </div>

                    </div>

                    <div class="employee-contract-description">

                        <?php if (!empty($contract["description"])): ?>

                            <p>
                                <?= nl2br(
                                    clean($contract["description"])
                                ); ?>
                            </p>

                        <?php else: ?>

                            <p class="employee-contract-no-description">

                                No contract description available.

                            </p>

                        <?php endif; ?>

                    </div>

                </div>

                <div class="employee-contract-details-actions">

                    <?php if (!empty($contract["file"])): ?>

                        <form
                            method="POST"
                            action="contract_pdf.php"
                        >

                            <input
                                type="hidden"
                                name="contract_id"
                                value="<?= htmlspecialchars($contract["contract_id"]); ?>"
                            >

                            <button
                                type="submit"
                                class="employee-contract-details-btn download"
                            >

                                <i class="fa-solid fa-file-pdf"></i>
                                Download Contract
                            </button>

                        </form>

                    <?php endif; ?>

                </div>

            </section>

        </main>

    </div>

</div>


<script src="../assets/js/employee.js"></script>


</body>

</html>