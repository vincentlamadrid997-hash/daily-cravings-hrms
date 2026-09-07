<?php

require_once __DIR__ . "/../../config/db.php";

$employee_id = $_SESSION["employee_id"] ?? 0;


$stmt = $conn->prepare("

    SELECT

        pay_period,
        basic_salary,
        allowances,
        deductions,
        gross_pay,
        net_pay,
        status

    FROM payroll

    WHERE employee_id = ?

    ORDER BY created_at DESC

    LIMIT 1

");

$stmt->execute([$employee_id]);

$payroll = $stmt->fetch(PDO::FETCH_ASSOC);

$status = strtolower($payroll["status"] ?? "");

$status_icon = "fa-file";
$status_class = "draft";

$status_icon = "fa-file";
$status_class = "draft";

switch ($status) {

    case "paid":
        $status_icon = "fa-circle-check";
        $status_class = "paid";
        break;


    case "generated":
        $status_icon = "fa-file-circle-check";
        $status_class = "generated";
        break;


    case "draft":
        $status_icon = "fa-file-lines";
        $status_class = "draft";
        break;

}

?>


<div class="employee-widget">

    <div class="employee-widget-header">

        <div class="employee-widget-title">

            <i class="fa-solid fa-money-check-dollar"></i>

            <h3>Latest Payroll</h3>

        </div>

    </div>

    <div class="employee-widget-body">

        <?php if ($payroll): ?>

            <div class="employee-payroll-summary">

                <div class="employee-payroll-summary-card period">

                    <div class="employee-attendance-top">

                        <div class="employee-payroll-summary-icon">

                            <i class="fa-solid fa-calendar-days"></i>

                        </div>

                        <div>

                            <h4>Pay Period</h4>

                            <h2>

                                <?= htmlspecialchars($payroll["pay_period"]); ?>

                            </h2>

                        </div>

                    </div>

                </div>

                <div class="employee-payroll-summary-card net">

                    <div class="employee-attendance-top">

                        <div class="employee-payroll-summary-icon">

                            <i class="fa-solid fa-money-bill-wave"></i>

                        </div>

                        <div>

                            <h4>Net Pay</h4>

                            <h2>

                                ₱<?= number_format($payroll["net_pay"], 2); ?>

                            </h2>

                        </div>

                    </div>

                </div>

                <div class="employee-payroll-summary-card gross">

                    <div class="employee-attendance-top">

                        <div class="employee-payroll-summary-icon">

                            <i class="fa-solid fa-file-invoice-dollar"></i>

                        </div>

                        <div>

                            <h4>Gross Pay</h4>

                            <h2>

                                ₱<?= number_format($payroll["gross_pay"], 2); ?>

                            </h2>

                        </div>

                    </div>

                </div>

                <div class="employee-payroll-summary-card status <?= $status_class; ?>">

                    <div class="employee-attendance-top">

                        <div class="employee-payroll-summary-icon">

                            <i class="fa-solid <?= $status_icon; ?>"></i>

                        </div>

                        <div>

                            <h4>Status</h4>

                            <h2>

                                <?= htmlspecialchars($payroll["status"]); ?>

                            </h2>

                        </div>

                    </div>

                </div>

            </div>

            <div class="employee-payroll-breakdown-container">

                <div class="employee-payroll-breakdown-title">

                    <i class="fa-solid fa-wallet"></i>

                </div>

                <div class="employee-activity-content">

                    <div class="employee-activity-header">

                        <h4>Payroll Breakdown</h4>

                        <span class="employee-payroll-status <?= $status_class; ?>">

                            <?= htmlspecialchars($payroll["status"]); ?>

                        </span>

                    </div>

                    <p>

                        <strong>Basic Salary:</strong>

                        ₱<?= number_format($payroll["basic_salary"], 2); ?>

                    </p>

                    <p>

                        <strong>Allowances:</strong>

                        ₱<?= number_format($payroll["allowances"], 2); ?>

                    </p>

                    <p>

                        <strong>Deductions:</strong>

                        ₱<?= number_format($payroll["deductions"], 2); ?>

                    </p>

                </div>

            </div>

        <?php else: ?>

            <div class="employee-empty-state">

                <i class="fa-solid fa-wallet"></i>

                <h4>No Payroll Available</h4>

                <p>Your payroll information will appear here after HR generates your payroll.</p>

            </div>

        <?php endif; ?>

    </div>

</div>