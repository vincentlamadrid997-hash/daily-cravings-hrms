<?php

session_start();

require_once "../auth/admin_auth.php";

if (
    empty($_SESSION['report_ready']) ||
    empty($_SESSION['report_title']) ||
    empty($_SESSION['report_columns'])
) {
    $_SESSION['report_error'] = "Please generate a report first before exporting PDF.";
    header("Location: reports.php");
    exit;
}

$report_title = $_SESSION['report_title'];
$columns      = $_SESSION['report_columns'];
$data         = $_SESSION['report_data'];

?>
<!DOCTYPE html>
<html>
<head>

<title><?= htmlspecialchars($report_title); ?></title>

<style>

* {
    box-sizing: border-box;
}

body {
    font-family: "Segoe UI", Arial, sans-serif;
    background: #f5f7fb;
    padding: 40px;
    color: #37474F;
}

.report-container {
    max-width: 1200px;
    margin: auto;
    background: #ffffff;
    padding: 35px;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,.08);
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 3px solid #FFC72C;
    padding-bottom: 20px;
    margin-bottom: 25px;
}

.company {
    font-size: 26px;
    font-weight: 800;
    color: #003DA5;
    margin-bottom: 8px;
}

.subtitle {
    color: #607D8B;
    font-size: 14px;
}

.report-info {
    text-align: right;
}

.report-info h2 {
    margin: 0;
    color: #003DA5;
    font-size: 24px;
}

.report-info p {
    margin-top: 8px;
    color: #78909C;
    font-size: 13px;
}

.print-btn {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 20px;
}

button {
    background: #003DA5;
    color: white;
    border: none;
    padding: 12px 22px;
    border-radius: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: .3s;
}

button:hover {
    background: #0D47A1;
    transform: translateY(-2px);
}

.table-wrapper {
    overflow: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

thead th {
    background: #003DA5;
    color: white;
    padding: 14px;
    font-size: 13px;
    text-align: left;
}

tbody tr:nth-child(even) {
    background: #F8FAFC;
}

tbody tr:hover {
    background: #EEF5FF;
}

td {
    padding: 12px;
    border-bottom: 1px solid #E3E8EF;
    font-size: 13px;
}

.footer {
    margin-top: 30px;
    padding-top: 15px;
    border-top: 1px solid #E5E7EB;
    text-align: right;
    font-size: 12px;
    color: #78909C;
}

@media print {

    body {
        background: white;
        padding: 0;
    }

    .report-container {
        box-shadow: none;
        border-radius: 0;
        padding: 20px;
    }

    .no-print {
        display: none !important;
    }

    table {
        page-break-inside: auto;
    }

    tr {
        page-break-inside: avoid;
    }

}

</style>

</head>
<body>

<div class="header">

    <div class="company">
        Daily Cravings Foods Inc.
    </div>

    <div class="report-info">
        <h2><?= htmlspecialchars($report_title); ?></h2>
        <p>Generated Admin Report</p>
    </div>

</div>

<div class="print-btn no-print">
    <button onclick="window.print();">
        <i class="fa-solid fa-print"></i>
        Print
    </button>
</div>

<table>

<thead>
<tr>
    <?php foreach ($columns as $column): ?>
        <th><?= htmlspecialchars($column); ?></th>
    <?php endforeach; ?>
</tr>
</thead>

<tbody>

<?php if (!empty($data)): ?>

    <?php foreach ($data as $row): ?>
        <tr>
            <?php foreach ($row as $value): ?>
                <td><?= htmlspecialchars($value ?? "N/A"); ?></td>
            <?php endforeach; ?>
        </tr>
    <?php endforeach; ?>

<?php else: ?>

    <tr>
        <td colspan="<?= count($columns); ?>">
            No Data Available
        </td>
    </tr>

<?php endif; ?>

</tbody>

</table>

<div class="footer">
    Generated: <?= date("F d, Y h:i A"); ?>
</div>

<script>
window.onload = function () {
    window.print();
};

window.onafterprint = function () {
    window.location.href = "reports.php";
};
</script>

</body>
</html>
<?php

unset($_SESSION['report_title']);
unset($_SESSION['report_columns']);
unset($_SESSION['report_data']);
unset($_SESSION['report_ready']);

?>