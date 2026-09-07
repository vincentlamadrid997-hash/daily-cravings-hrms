<?php


session_start();

date_default_timezone_set("Asia/Manila");

require_once "../auth/employee_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";


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

    if ($amount === null || $amount === "") {

        return "₱0.00";

    }

    return "₱" . number_format(
        $amount,
        2
    );

}

function contractStatusBadge($status)
{

    switch ($status) {

        case "Active":

            return [

                "class" => "active",
                "icon" => "fa-circle-check"

            ];

        case "Expired":

            return [

                "class" => "expired",
                "icon" => "fa-circle-xmark"

            ];

        case "Cancelled":

            return [

                "class" => "cancelled",
                "icon" => "fa-circle-minus"

            ];

        default:

            return [

                "class" => "inactive",
                "icon" => "fa-circle-info"

            ];

    }

}


$contract_id = $_POST["contract_id"] ?? 0;


if (!$contract_id) {

    header(
        "Location: employee_contracts.php"
    );

    exit;

}


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

    $_SESSION["contract_error"] =

        "Employment Contract not found.";


    header(
        "Location: employee_contracts.php"
    );

    exit;

}


$employee_name = trim(

    $contract["first_name"]
    . " "
    . ($contract["middle_name"] ?? "")
    . " "
    . $contract["last_name"]

);


$status = contractStatusBadge(
    $contract["status"]
);


$contract_reference =

    "DCFI-CON-" .
    str_pad(

        $contract["contract_id"],
        5,
        "0",
        STR_PAD_LEFT

    );


$generated_date = date(
    "F d, Y h:i A"
);


$page_title =

    "Employment Contract Document";


?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= clean($page_title); ?> | Daily Cravings Foods Inc.</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"/>


<style>
* {

    box-sizing: border-box;

}

body {

    margin: 0;
    padding: 40px;
    background: #F5F7FB;

    font-family:
        "Segoe UI",
        Arial,
        sans-serif;

    color: #263238;

}

.contract-document {

    max-width: 1000px;
    margin: auto;
    background: #FFFFFF;
    border-radius: 28px;
    overflow: hidden;

    box-shadow:
        0 15px 45px rgba(0,0,0,.10);

}

.contract-document-page {

    padding: 45px;
    min-height: 100vh;

}

.contract-header {

    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding-bottom: 30px;
    margin-bottom: 35px;

    border-bottom:
        4px solid #FFC107;

}

.company-area {

    display: flex;
    flex-direction: column;

}

.company-name {

    font-size: 32px;
    font-weight: 900;
    color: #003DA5;
    letter-spacing: .5px;

}

.company-subtitle {

    margin-top: 8px;
    font-size: 14px;
    color: #607D8B;
    line-height: 1.6;

}

.contract-reference {

    margin-top: 18px;
    background: #F8FAFC;
    border:1px solid #E3EAF2;
    padding: 12px 18px;
    border-radius: 15px;
    width: max-content;

}

.contract-reference small {

    display: block;
    color: #78909C;
    font-size: 12px;
    font-weight: 700;

}

.contract-reference strong {

    display: block;
    margin-top: 5px;
    color: #263238;
    font-size: 16px;

}

.contract-title {

    text-align: right;

}

.contract-title h1 {

    margin: 0;
    color: #003DA5;
    font-size: 27px;
    font-weight: 900;
    text-transform: uppercase;

}

.contract-title p {

    margin-top: 10px;
    color: #78909C;
    font-size: 13px;

}

.contract-card {

    background: #FFFFFF;
    border: 1px solid #E8EDF5;
    border-radius: 22px;
    padding: 28px;
    margin-bottom: 28px;

    box-shadow:
        0 8px 25px rgba(0,0,0,.04);

    page-break-inside: avoid;

}

.contract-card-header {

    display: flex;
    align-items: center;
    gap: 16px;
    padding-bottom: 18px;
    margin-bottom: 25px;

    border-bottom:
        1px solid #EDF1F7;

}

.contract-icon {

    width: 58px;
    height: 58px;
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 18px;
    background: #E3F2FD;
    color: #003DA5;
    font-size: 24px;

}

.contract-card-header h2 {

    margin: 0;
    font-size: 21px;
    font-weight: 900;
    color: #263238;

}

.contract-card-header p {

    margin: 6px 0 0;
    font-size: 13px;
    color: #78909C;

}

.contract-grid {

    display: grid;

    grid-template-columns:
        repeat(2,1fr);

    gap: 25px;

}

.contract-field {

    display: flex;
    flex-direction: column;
    gap: 8px;

}

.contract-field label {

    font-size: 13px;
    font-weight: 800;
    color: #607D8B;

}

.contract-field label i {

    color: #003DA5;
    margin-right: 8px;

}

.contract-field span {

    font-size: 16px;
    font-weight: 700;
    color: #263238;

}

.contract-status {

    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 18px;
    border-radius: 30px;
    width: max-content;
    font-size: 13px;
    font-weight: 800;

}

.contract-status.active {

    background: #E8F5E9;
    color: #2E7D32;

}

.contract-status.expired {

    background: #FDECEC;
    color: #C62828;

}

.contract-status.cancelled {

    background: #ECEFF1;
    color: #607D8B;

}

.contract-status.inactive {

    background: #FFF8E1;
    color: #F9A825;

}

@media print {

    body {

        background: #FFFFFF;
        padding: 0;

    }

    .contract-document {

        box-shadow: none;
        border-radius: 0;

    }

    .no-print {

        display: none !important;

    }

    .contract-document-page {

        padding: 35px;

    }

}

@media(max-width:900px) {

    body {

        padding: 15px;

    }

    .contract-header {

        flex-direction: column;
        gap: 25px;

    }

    .contract-title {

        text-align: left;

    }

    .contract-grid {

        grid-template-columns: 1fr;

    }

    .contract-document-page {

        padding: 25px;

    }

}

.salary-summary {

    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;

}

.salary-box {

    display: flex;
    align-items: center;
    gap: 14px;
    background: #F8FAFC;
    border: 1px solid #E3EAF2;
    border-radius: 18px;
    padding: 18px;

}

.salary-box.highlight {

    background: #FFF8E1;
    border-color: #FFE082;

}

.salary-box small {

    display: block;
    color: #78909C;
    font-size: 12px;
    font-weight: 700;

}

.salary-box h3 {

    margin: 6px 0 0;
    color: #263238;
    font-size: 18px;

}

.salary-icon {

    width: 46px;
    height: 46px;
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 14px;
    background: #E3F2FD;
    color: #003DA5;
    font-size: 18px;
    flex-shrink: 0;

}

.contract-description-box {

    background: #F8FAFC;
    border: 1px solid #E3EAF2;
    border-radius: 16px;
    padding: 20px;
    line-height: 1.7;
    color: #37474F;

}

.no-description {

    color: #90A4AE;
    font-style: italic;
    margin: 0;

}

.contract-footer {

    margin-top: 40px;
    padding-top: 20px;
    border-top: 1px solid #E5E7EB;
    text-align: right;
    color: #78909C;
    font-size: 13px;

}

.contract-print-area {

    display: flex;
    justify-content: flex-end;
    padding: 20px 35px 35px;

}

.print-contract-btn {

    background: #003DA5;
    color: #ffffff;
    border: none;
    padding: 13px 25px;
    border-radius: 14px;
    font-weight: 700;
    cursor: pointer;

}

</style>

</head>


<body>

<div class="contract-document">

    <div class="contract-document-page">

        <div class="contract-header">

            <div class="company-area">

                <div class="company-name">
                    Daily Cravings Foods Inc.
                </div>

                <div class="company-subtitle">

                    Human Resource Management System

                    <br>

                    Official Employee Employment Contract Document

                </div>

                <div class="contract-reference">

                    <small>
                        Contract Reference No.
                    </small>

                    <strong>
                        <?= clean($contract_reference); ?>
                    </strong>

                </div>

            </div>

            <div class="contract-title">

                <h1>Employment Contract</h1>

                <p>
                    Generated:
                    <?= clean($generated_date); ?>
                </p>

            </div>

        </div>

        <div class="contract-card">

            <div class="contract-card-header">

                <div class="contract-icon">
                    <i class="fa-solid fa-user-tie"></i>
                </div>

                <div>

                    <h2>Employee Information</h2>

                    <p>Employee profile and assigned company position details.</p>

                </div>

            </div>

            <div class="contract-grid">

                <div class="contract-field">

                    <label>
                        <i class="fa-solid fa-id-card"></i>
                        Employee Code
                    </label>

                    <span>
                        <?= clean($contract["employee_code"]); ?>
                    </span>

                </div>

                <div class="contract-field">

                    <label>
                        <i class="fa-solid fa-user"></i>
                        Full Name
                    </label>

                    <span>
                        <?= clean($employee_name); ?>
                    </span>

                </div>

                <div class="contract-field">

                    <label>
                        <i class="fa-solid fa-building"></i>
                        Department
                    </label>

                    <span>
                        <?= clean($contract["department_name"]); ?>
                    </span>

                </div>

                <div class="contract-field">

                    <label>
                        <i class="fa-solid fa-briefcase"></i>
                        Position
                    </label>

                    <span>
                        <?= clean($contract["position_name"]); ?>
                    </span>

                </div>

            </div>

        </div>

        <div class="contract-card">

            <div class="contract-card-header">

                <div class="contract-icon">
                    <i class="fa-solid fa-file-contract"></i>
                </div>

                <div>

                    <h2>Employment Contract Information</h2>

                    <p>Contract agreement details and validity period.</p>

                </div>

            </div>

            <div class="contract-grid">

                <div class="contract-field">

                    <label>
                        <i class="fa-solid fa-file-signature"></i>
                        Contract Type
                    </label>

                    <span>
                        <?= clean($contract["contract_type"]); ?>
                    </span>

                </div>

                <div class="contract-field">

                    <label>
                        <i class="fa-solid fa-circle-check"></i>
                        Contract Status
                    </label>

                    <span>

                        <span class="contract-status <?= $status["class"]; ?>">

                            <i class="fa-solid <?= $status["icon"]; ?>"></i>
                            <?= clean($contract["status"]); ?>

                        </span>

                    </span>

                </div>

                <div class="contract-field">

                    <label>
                        <i class="fa-solid fa-calendar-plus"></i>
                        Contract Start Date
                    </label>

                    <span>
                        <?= dateFormat($contract["start_date"]); ?>
                    </span>

                </div>

                <div class="contract-field">

                    <label>
                        <i class="fa-solid fa-calendar-xmark"></i>
                        Contract End Date
                    </label>

                    <span>
                        <?= dateFormat($contract["end_date"]); ?>
                    </span>

                </div>

            </div>

        </div>

        <div class="contract-card">

            <div class="contract-card-header">

                <div class="contract-icon">
                    <i class="fa-solid fa-money-bill-wave"></i>
                </div>

                <div>

                    <h2>Contract Summary</h2>

                    <p>Important contract duration and salary information.</p>

                </div>

            </div>

            <div class="salary-summary">

                <div class="salary-box">

                    <div class="salary-icon">
                        <i class="fa-solid fa-calendar-plus"></i>
                    </div>

                    <div>

                        <small>
                            Contract Start Date
                        </small>

                        <h3>
                            <?= dateFormat($contract["start_date"]); ?>
                        </h3>

                    </div>

                </div>

                <div class="salary-box">

                    <div class="salary-icon">
                        <i class="fa-solid fa-calendar-xmark"></i>
                    </div>

                    <div>

                        <small>
                            Contract End Date
                        </small>

                        <h3>
                            <?= dateFormat($contract["end_date"]); ?>
                        </h3>

                    </div>

                </div>

                <div class="salary-box highlight">

                    <div class="salary-icon">
                        <i class="fa-solid fa-peso-sign"></i>
                    </div>

                    <div>

                        <small>
                            Monthly Salary
                        </small>

                        <h3>
                            <?= moneyFormat($contract["salary"]); ?>
                        </h3>

                    </div>

                </div>

            </div>

        </div>

        <div class="contract-card">

            <div class="contract-card-header">

                <div class="contract-icon">
                    <i class="fa-solid fa-align-left"></i>
                </div>

                <div>

                    <h2>Contract Description</h2>

                    <p>Additional information regarding employment agreement.</p>

                </div>

            </div>

            <div class="contract-description-box">

                <?php if(!empty($contract["description"])): ?>

                    <p>
                        <?= nl2br(
                            clean($contract["description"])
                        ); ?>
                    </p>

                <?php else: ?>

                    <p class="no-description">

                        No contract description available.

                    </p>

                <?php endif; ?>

            </div>

        </div>

        <div class="contract-footer">

            <div>

                Generated:
                <?= $generated_date; ?>

                <br>

                Daily Cravings Foods Inc.

                <br>

                HR Management System

            </div>

        </div>

    </div>

</div>

<div class="no-print contract-print-area">

    <button
        onclick="window.print();"
        class="print-contract-btn"
    >

        <i class="fa-solid fa-print"></i>
        Print Employment Contract
    </button>

</div>


<script>

window.onload = function(){

    setTimeout(function(){

        window.print();

    },500);

};


window.onafterprint = function(){

    window.location.href = "employment_contracts.php";

};

</script>


</body>

</html>