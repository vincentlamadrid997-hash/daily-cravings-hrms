<?php

session_start();

date_default_timezone_set("Asia/Manila");


require_once "../auth/employee_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";


$employee_id = $_SESSION["employee_id"] ?? 0;

if(!$employee_id){

    header("Location: ../login.php");
    exit;

}

requireCSRFToken("my_payroll.php");


if(!isset($_POST["payroll_id"])){

    header("Location: my_payroll.php");
    exit;

}


$payroll_id = (int) $_POST["payroll_id"];


$stmt = $conn->prepare("

SELECT

    p.payroll_id,
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

WHERE
    p.payroll_id = :payroll_id

AND
    p.employee_id = :employee_id

");

$stmt->execute([

    ":payroll_id" => $payroll_id,
    ":employee_id" => $employee_id

]);

$payroll = $stmt->fetch(PDO::FETCH_ASSOC);


if(!$payroll){

    header("Location: my_payroll.php");
    exit;

}


$employee_name = trim(

    $payroll["first_name"]
    ." "
    .($payroll["middle_name"] ?? "")
    ." "
    .$payroll["last_name"]

);

if(empty($payroll["department_name"])){

    $department = "No Department";

}else{

    $department = $payroll["department_name"];

}


if(empty($payroll["position_name"])){

    $position = "No Position";

}else{

    $position = $payroll["position_name"];

}


$page_title = "Employee Payslip";

$generated_date = date(

    "F d, Y h:i A"

);

function payrollMoney($amount)

{

    return "₱" . number_format(
        $amount,
        2
    );

}

function payrollStatusClass($status)

{

    return match($status){

        "Paid" => "paid",
        "Generated" => "generated",
        "Draft" => "draft",
        default => "unknown"

    };

}

function cleanText($value)

{

    return htmlspecialchars(
        $value ?? "",
        ENT_QUOTES,
        "UTF-8"
    );

}


?>

<style>

@page{

    size:A4;
    margin:15mm;

}

*{

    box-sizing:border-box;

}

body{

    font-family:"Segoe UI",Arial,sans-serif;
    background:#F5F7FB;
    color:#263238;
    margin:0;
    padding:30px;

}

.employee-payslip-wrapper{

    width:100%;
    max-width:820px;
    margin:auto;
    background:#ffffff;
    border-radius:22px;
    padding:35px;

    box-shadow:
    0 12px 35px rgba(0,0,0,.08);

}

.employee-payslip-page-one{

    page-break-after:always;

}

.employee-payslip-page-two{

    page-break-before:always;

}

.employee-payslip-header{

    display:flex;
    justify-content:space-between;
    align-items:center;
    padding-bottom:25px;
    margin-bottom:30px;
    border-bottom:4px solid #FFC107;

}

.employee-payslip-company{

    font-size:30px;
    font-weight:900;
    color:#003DA5;
    letter-spacing:.5px;

}

.employee-payslip-company-sub{

    margin-top:8px;
    font-size:14px;
    color:#607D8B;

}

.employee-payslip-title{

    text-align:right;

}

.employee-payslip-title h1{

    margin:0;
    font-size:26px;
    font-weight:900;
    color:#003DA5;

}

.employee-payslip-title p{

    margin-top:8px;
    font-size:13px;
    color:#78909C;

}

.employee-payslip-card{

    background:#FFFFFF;
    border:1px solid #E5EAF2;
    border-radius:20px;
    padding:25px;
    margin-bottom:25px;

    box-shadow:
    0 8px 20px rgba(0,0,0,.05);

}

.employee-payslip-card-header{

    display:flex;
    align-items:center;
    gap:15px;
    padding-bottom:18px;
    margin-bottom:20px;
    border-bottom:1px solid #EDF1F7;

}

.employee-payslip-icon{

    width:50px;
    height:50px;
    display:flex;
    justify-content:center;
    align-items:center;
    background:#E3F2FD;
    color:#003DA5;
    border-radius:16px;
    font-size:22px;

}

.employee-payslip-card-header h2{

    margin:0;
    color:#263238;
    font-size:21px;
    font-weight:800;

}

.employee-payslip-card-header p{

    margin:5px 0 0;
    color:#78909C;
    font-size:13px;

}

.employee-payslip-info-grid{

    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:18px;

}

.employee-payslip-info-item{

    background:#F8FAFC;
    border-radius:15px;
    padding:15px 18px;
    border-left:4px solid #003DA5;

}

.employee-payslip-info-item label{

    display:block;
    font-size:12px;
    font-weight:700;
    color:#78909C;
    margin-bottom:8px;

}

.employee-payslip-info-item label i{

    color:#003DA5;
    margin-right:8px;

}

.employee-payslip-info-item strong{

    display:block;
    font-size:15px;
    color:#263238;

}

@media print{

    body{

        background:#fff;
        padding:0;

    }

    .employee-payslip-wrapper{

        box-shadow:none;
        border-radius:0;
        padding:20px;

    }

    .no-print{

        display:none!important;

    }

}

</style>


<body>

<div class="employee-payslip-wrapper">

<div class="employee-payslip-page-one">

<div class="employee-payslip-header">

<div>

<div class="employee-payslip-company">
Daily Cravings Foods Inc.
</div>

<div class="employee-payslip-company-sub">
Employee Payroll Management System
</div>

</div>

<div class="employee-payslip-title">

<h1>Employee Payslip</h1>

<p>Generated Payroll Payslip</p>

</div>

</div>

<div class="employee-payslip-card">

<div class="employee-payslip-card-header">

<div class="employee-payslip-icon">
<i class="fa-solid fa-user"></i>
</div>

<div>

<h2>Employee Information</h2>

<p>Personal employment details</p>

</div>

</div>

<div class="employee-payslip-info-grid">

<div class="employee-payslip-info-item">

<label>
<i class="fa-solid fa-id-card"></i>
Employee Code
</label>

<strong>
<?= cleanText($payroll["employee_code"]); ?>
</strong>

</div>

<div class="employee-payslip-info-item">

<label>
<i class="fa-solid fa-user"></i>
Employee Name
</label>

<strong>
<?= cleanText($employee_name); ?>
</strong>

</div>

<div class="employee-payslip-info-item">

<label>
<i class="fa-solid fa-building"></i>
Department
</label>

<strong>
<?= cleanText($department); ?>
</strong>

</div>

<div class="employee-payslip-info-item">

<label>
<i class="fa-solid fa-briefcase"></i>
Position
</label>

<strong>
<?= cleanText($position); ?>
</strong>

</div>

<div class="employee-payslip-info-item">

<label>
<i class="fa-solid fa-calendar-days"></i>
Pay Period
</label>

<strong>
<?= cleanText($payroll["pay_period"]); ?>
</strong>

</div>

<div class="employee-payslip-info-item">

<label>
<i class="fa-solid fa-clock"></i>
Generated Date
</label>

<strong>
<?= $generated_date; ?>
</strong>

</div>

</div>

</div>

<div class="employee-payslip-card">

<div class="employee-payslip-card-header">

<div class="employee-payslip-icon">
<i class="fa-solid fa-money-bill-transfer"></i>
</div>

<div>

<h2>Salary Breakdown</h2>

<p>Detailed salary computation</p>

</div>

</div>

<div class="employee-payslip-salary-grid">

<div class="employee-payslip-salary-item">

<div class="salary-icon basic">
<i class="fa-solid fa-wallet"></i>
</div>

<div>

<span>
Basic Salary
</span>

<strong>
<?= payrollMoney($payroll["basic_salary"]); ?>
</strong>

</div>

</div>

<div class="employee-payslip-salary-item">

<div class="salary-icon allowance">

<i class="fa-solid fa-circle-plus"></i>

</div>

<div>

<span>
Allowances
</span>

<strong>
<?= payrollMoney($payroll["allowances"]); ?>
</strong>

</div>

</div>

<div class="employee-payslip-salary-item">

<div class="salary-icon deduction">
<i class="fa-solid fa-circle-minus"></i>
</div>

<div>

<span>
Deductions
</span>

<strong>
<?= payrollMoney($payroll["deductions"]); ?>
</strong>

</div>

</div>

</div>

</div>


<style>

.employee-payslip-salary-grid{

    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:18px;

}

.employee-payslip-salary-item{

    background:#F8FAFC;
    border-radius:18px;
    padding:20px;
    display:flex;
    align-items:center;
    gap:15px;

}

.salary-icon{

    width:45px;
    height:45px;
    display:flex;
    align-items:center;
    justify-content:center;
    border-radius:14px;
    font-size:20px;

}

.salary-icon.basic{

    background:#E3F2FD;
    color:#003DA5;

}

.salary-icon.allowance{

    background:#E8F5E9;
    color:#2E7D32;

}

.salary-icon.deduction{

    background:#FDECEC;
    color:#C62828;

}

.employee-payslip-salary-item span{

    display:block;
    color:#78909C;
    font-size:12px;
    font-weight:700;

}

.employee-payslip-salary-item strong{

    display:block;
    margin-top:5px;
    font-size:18px;
    color:#263238;

}

</style>


<div class="employee-payslip-page-two">

<div class="employee-payslip-card">

<div class="employee-payslip-card-header">

<div class="employee-payslip-icon">
<i class="fa-solid fa-file-invoice-dollar"></i>
</div>

<div>

<h2>Payroll Summary</h2>

<p>Final salary result</p>

</div>

</div>

<div class="employee-payslip-summary-grid">

<div class="employee-payslip-summary-box">

<span>
Gross Pay
</span>

<strong>
<?= payrollMoney($payroll["gross_pay"]); ?>
</strong>

</div>

<div class="employee-payslip-summary-box net">

<span>
Net Pay
</span>

<strong>
<?= payrollMoney($payroll["net_pay"]); ?>
</strong>

</div>

<div class="employee-payslip-summary-box">

<span>
Payroll Status
</span>

<strong>

<span class="employee-payslip-status 
<?= payrollStatusClass($payroll["status"]); ?>">

<i class="fa-solid fa-circle-check"></i>
<?= cleanText($payroll["status"]); ?>

</span>

</strong>

</div>

</div>

</div>


<style>

.employee-payslip-summary-grid{

    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:20px;

}

.employee-payslip-summary-box{

    background:#F8FAFC;
    padding:25px;
    border-radius:18px;
    text-align:center;

}

.employee-payslip-summary-box span{

    display:block;
    font-size:13px;
    font-weight:700;
    color:#78909C;

}

.employee-payslip-summary-box strong{

    display:block;
    margin-top:12px;
    font-size:25px;
    color:#003DA5;

}

.employee-payslip-summary-box.net{

    background:#E8F5E9;
    border:2px solid #81C784;

}

.employee-payslip-summary-box.net strong{

    color:#2E7D32;
    font-size:32px;

}

.employee-payslip-status{

    display:inline-flex;
    align-items:center;
    gap:8px;
    padding:10px 22px;
    border-radius:30px;
    font-size:14px;

}

.employee-payslip-status.paid{

    background:#E8F5E9;
    color:#2E7D32;

}

.employee-payslip-status.generated{

    background:#FFF8E1;
    color:#F9A825;

}

.employee-payslip-status.draft{

    background:#ECEFF1;
    color:#455A64;

}

</style>


<div class="employee-payslip-card">

<div class="employee-payslip-card-header">

<div class="employee-payslip-icon">
<i class="fa-solid fa-calculator"></i>
</div>

<div>

<h2>Payroll Computation</h2>

<p>Salary calculation breakdown</p>

</div>

</div>

<div class="employee-payslip-computation">

<div class="computation-row">

<span>
Basic Salary
</span>

<strong>
<?= payrollMoney($payroll["basic_salary"]); ?>
</strong>

</div>

<div class="computation-symbol">
+
</div>

<div class="computation-row">

<span>
Allowances
</span>

<strong>
<?= payrollMoney($payroll["allowances"]); ?>
</strong>

</div>

<div class="computation-symbol minus">
-
</div>

<div class="computation-row">

<span>
Deductions
</span>

<strong>
<?= payrollMoney($payroll["deductions"]); ?>
</strong>

</div>

<div class="computation-line"></div>

<div class="computation-row total">

<span>
Net Pay
</span>

<strong>
<?= payrollMoney($payroll["net_pay"]); ?>
</strong>

</div>

</div>

</div>


<style>

.employee-payslip-computation{

    background:#F8FAFC;
    border-radius:18px;
    padding:25px;

}

.computation-row{

    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:12px 5px;

}

.computation-row span{

    font-weight:700;
    color:#455A64;

}

.computation-row strong{

    color:#003DA5;

}

.computation-symbol{

    text-align:center;
    font-size:24px;
    font-weight:900;
    color:#003DA5;

}

.computation-symbol.minus{

    color:#C62828;

}

.computation-line{

    margin:15px 0;
    border-top:3px solid #003DA5;

}

.computation-row.total strong{

    font-size:26px;
    color:#2E7D32;

}

</style>


<div class="employee-payslip-print-area no-print">

<button
onclick="window.print();"
class="employee-payslip-print-button"
>

<i class="fa-solid fa-print"></i>
Print Payslip
</button>

</div>


<style>

.employee-payslip-print-area{

    display:flex;
    justify-content:flex-end;
    margin-top:25px;
    margin-bottom:20px;

}

.employee-payslip-print-button{

    background:#003DA5;
    color:white;
    border:none;
    padding:13px 28px;
    border-radius:14px;
    font-size:14px;
    font-weight:700;
    cursor:pointer;
    transition:.3s;

}

.employee-payslip-print-button:hover{

    background:#0D47A1;
    transform:translateY(-2px);

}

.employee-payslip-footer{

    margin-top:35px;
    padding-top:20px;
    border-top:2px solid #E5EAF2;
    display:flex;
    justify-content:space-between;
    align-items:center;
    font-size:12px;
    color:#78909C;

}

.employee-payslip-footer strong{

    color:#003DA5;
    font-size:14px;

}

.employee-payslip-footer-right{

    text-align:right;

}

@media print{

    body{

        background:white;
        padding:0;

    }

    .employee-payslip-wrapper{

        max-width:none;
        width:100%;
        box-shadow:none;
        border-radius:0;
        padding:10px;

    }

    .employee-payslip-print-area{

        display:none!important;

    }

    .employee-payslip-card{

        box-shadow:none;

    }

}

</style>


<div class="employee-payslip-footer">

<div>

<strong>
Daily Cravings Foods Inc.
</strong>

<br>

Employee HR Management System

<br>

Confidential Employee Payroll Document

</div>

<div class="employee-payslip-footer-right">

Generated:

<br>

<?= $generated_date; ?>

<br>

Payroll ID:
<?= cleanText($payroll["payroll_id"]); ?>

</div>

</div>


</div>


<script>

window.onload = function(){

    setTimeout(function(){

        window.print();

    },500);

};


window.onafterprint = function(){

    window.location.href = "my_payroll.php";

};

</script>


</body>

</html>