<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";

$page_title = "Contracts";


$success = $_SESSION['contract_success'] ?? "";
$error   = $_SESSION['contract_error'] ?? "";

unset($_SESSION['contract_success']);
unset($_SESSION['contract_error']);


$conn->exec("
    UPDATE contracts
    SET status = 'Expired'
    WHERE end_date < CURDATE()
      AND status = 'Active'
");


$totalContracts = $conn->query("
    SELECT COUNT(*)
    FROM contracts c
    INNER JOIN employees e
        ON c.employee_id = e.employee_id
    WHERE e.employment_status = 'Active'
")->fetchColumn();

$activeContracts = $conn->query("
    SELECT COUNT(*)
    FROM contracts c
    INNER JOIN employees e
        ON c.employee_id = e.employee_id
    WHERE e.employment_status = 'Active'
      AND c.status = 'Active'
")->fetchColumn();

$expiredContracts = $conn->query("
    SELECT COUNT(*)
    FROM contracts c
    INNER JOIN employees e
        ON c.employee_id = e.employee_id
    WHERE e.employment_status = 'Active'
      AND c.status = 'Expired'
")->fetchColumn();

$totalEmployees = $conn->query("
    SELECT COUNT(DISTINCT c.employee_id)
    FROM contracts c
    INNER JOIN employees e
        ON c.employee_id = e.employee_id
    WHERE e.employment_status = 'Active'
")->fetchColumn();


$search = trim($_GET['search'] ?? "");


$sql = "
    SELECT
        c.contract_id,
        c.employee_id,
        c.contract_type,
        c.start_date,
        c.end_date,
        c.salary,
        c.description,
        c.file,
        c.status,
        c.created_at,

        e.employee_code,
        e.first_name,
        e.middle_name,
        e.last_name

    FROM contracts c

    INNER JOIN employees e
        ON c.employee_id = e.employee_id

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
            OR c.contract_type LIKE ?
            OR c.status LIKE ?
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
    ORDER BY c.created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->execute($params);

$contracts = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

            <section class="hr-con-page">

                <div class="hr-con-summary-grid">

                    <div class="hr-con-summary-card blue">
                        <div class="hr-con-summary-icon">
                            <i class="fa-solid fa-file-signature"></i>
                        </div>

                        <div class="hr-con-summary-content">
                            <span>Total Contracts</span>
                            <h2><?= $totalContracts; ?></h2>
                            <p>Employee contracts</p>
                        </div>
                    </div>

                    <div class="hr-con-summary-card green">
                        <div class="hr-con-summary-icon">
                            <i class="fa-solid fa-file-circle-check"></i>
                        </div>

                        <div class="hr-con-summary-content">
                            <span>Active Contracts</span>
                            <h2><?= $activeContracts; ?></h2>
                            <p>Currently active</p>
                        </div>
                    </div>

                    <div class="hr-con-summary-card red">
                        <div class="hr-con-summary-icon">
                            <i class="fa-solid fa-file-circle-xmark"></i>
                        </div>

                        <div class="hr-con-summary-content">
                            <span>Expired Contracts</span>
                            <h2><?= $expiredContracts; ?></h2>
                            <p>Contract ended</p>
                        </div>
                    </div>

                    <div class="hr-con-summary-card purple">
                        <div class="hr-con-summary-icon">
                            <i class="fa-solid fa-users"></i>
                        </div>

                        <div class="hr-con-summary-content">
                            <span>Employees With Contracts</span>
                            <h2><?= $totalEmployees; ?></h2>
                            <p>Active employees</p>
                        </div>
                    </div>

                </div>

                <div class="hr-con-header">

                    <div class="hr-con-title">

                        <h1>
                            <i class="fa-solid fa-file-signature"></i>
                            Contracts
                        </h1>

                        <p>Manage employee employment contracts.</p>

                    </div>

                    <a
                        href="contract_add.php"
                        class="hr-con-add-btn"
                    >
                        <i class="fa-solid fa-plus"></i>
                        Add Contract
                    </a>

                </div>

                <div class="hr-con-table-card">

                    <div class="hr-con-search">

                        <div class="hr-con-search-box">

                            <i class="fa-solid fa-magnifying-glass"></i>

                            <input
                                type="text"
                                id="contractSearch"
                                placeholder="Search employee, contract type or status..."
                                autocomplete="off"
                            >

                        </div>

                    </div>

                    <div class="hr-con-table-wrapper">

                        <table class="hr-con-table">

                            <thead>

                                <tr>

                                    <th>Employee</th>
                                    <th>Contract Type</th>
                                    <th>Salary</th>
                                    <th>Duration</th>
                                    <th>Status</th>
                                    <th width="170">Action</th>

                                </tr>

                            </thead>

                            <tbody id="contractTableBody">

                                <?php if (!empty($contracts)): ?>

                                    <?php foreach ($contracts as $contract): ?>

                                        <tr class="contract-row">

                                            <td>

                                                <div class="hr-con-name">

                                                    <div class="hr-con-avatar">
                                                        <i class="fa-solid fa-user"></i>
                                                    </div>

                                                    <div>

                                                        <strong>
                                                            <?= htmlspecialchars(
                                                                trim(
                                                                    $contract['first_name'] . " " .
                                                                    (!empty($contract['middle_name'])
                                                                        ? $contract['middle_name'] . " "
                                                                        : "") .
                                                                    $contract['last_name']
                                                                )
                                                            ); ?>
                                                        </strong>

                                                        <br>

                                                        <small>
                                                            <?= htmlspecialchars($contract['employee_code']); ?>
                                                        </small>

                                                    </div>

                                                </div>

                                            </td>

                                            <td>
                                                <?= htmlspecialchars($contract['contract_type']); ?>
                                            </td>

                                            <td>
                                                ₱<?= number_format($contract['salary'], 2); ?>
                                            </td>

                                            <td>

                                                <?= date("M d, Y", strtotime($contract['start_date'])); ?>

                                                <br>

                                                <small>
                                                    to
                                                    <?= date("M d, Y", strtotime($contract['end_date'])); ?>
                                                </small>

                                            </td>

                                            <td>

                                                <span class="hr-con-status <?= strtolower($contract['status']); ?>">

                                                    <?php if ($contract['status'] === "Active"): ?>

                                                        <i class="fa-solid fa-file-circle-check"></i>

                                                    <?php else: ?>

                                                        <i class="fa-solid fa-file-circle-xmark"></i>

                                                    <?php endif; ?>

                                                    <?= htmlspecialchars($contract['status']); ?>

                                                </span>

                                            </td>

                                            <td>

                                                <div class="hr-con-actions">

                                                                                                        <form action="contract_view.php" method="POST">

                                                        <?php csrfField(); ?>

                                                        <input
                                                            type="hidden"
                                                            name="contract_id"
                                                            value="<?= $contract['contract_id']; ?>"
                                                        >

                                                        <button
                                                            type="submit"
                                                            class="hr-con-action view"
                                                            title="View Contract"
                                                        >
                                                            <i class="fa-solid fa-eye"></i>
                                                        </button>

                                                    </form>

                                                                                                       <form action="contract_edit.php" method="POST">

                                                        <?php csrfField(); ?>

                                                        <input
                                                            type="hidden"
                                                            name="contract_id"
                                                            value="<?= $contract['contract_id']; ?>"
                                                        >

                                                        <button
                                                            type="submit"
                                                            class="hr-con-action edit"
                                                            title="Edit Contract"
                                                        >
                                                            <i class="fa-solid fa-pen"></i>
                                                        </button>

                                                    </form>

                                             <form action="contract_status.php" method="POST" class="hr-con-status-form">

                                                        <?php csrfField(); ?>

                                                        <input
                                                            type="hidden"
                                                            name="contract_id"
                                                            value="<?= $contract['contract_id']; ?>"
                                                        >

                                                        <button
                                                            type="submit"
                                                            class="hr-con-action status <?= strtolower($contract['status']); ?>"
                                                            title="<?= $contract['status'] === 'Active'
                                                                ? 'Mark as Expired'
                                                                : 'Activate Contract'; ?>"
                                                        >

                                                            <?php if ($contract['status'] === "Active"): ?>

                                                                <i class="fa-solid fa-file-circle-xmark"></i>

                                                            <?php else: ?>

                                                                <i class="fa-solid fa-file-circle-check"></i>

                                                            <?php endif; ?>

                                                        </button>

                                                    </form>

                                                </div>

                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                <?php endif; ?>

                                <tr id="noContractResult" style="display:none;">

                                    <td colspan="6">

                                        <div class="hr-con-empty">

                                            <i class="fa-solid fa-magnifying-glass"></i>

                                            <h3>No Contracts Found</h3>

                                            <p>No records matched your search.</p>

                                        </div>

                                    </td>

                                </tr>

                                <?php if (empty($contracts)): ?>

                                    <tr>

                                        <td colspan="6">

                                            <div class="hr-con-empty">

                                                <i class="fa-solid fa-file-circle-xmark"></i>

                                                <h3>No Contracts Available</h3>

                                                <p>Add your first employee contract.</p>

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
document.addEventListener("DOMContentLoaded", function () {

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
document.addEventListener("DOMContentLoaded", function () {

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
document.addEventListener("DOMContentLoaded", function () {

    const searchInput = document.getElementById("contractSearch");
    const rows = document.querySelectorAll(".contract-row");
    const noResult = document.getElementById("noContractResult");

    if (!searchInput) {
        return;
    }

    searchInput.addEventListener("keyup", function () {

        const keyword = this.value.toLowerCase().trim();
        let visible = 0;

        rows.forEach(function (row) {

            const text = row.textContent.toLowerCase();

            if (text.includes(keyword)) {
                row.style.display = "";
                visible++;
            } else {
                row.style.display = "none";
            }

        });

        if (noResult) {
            noResult.style.display = visible === 0 ? "" : "none";
        }

    });

});
</script>


<script>
document.querySelectorAll(".hr-con-status-form").forEach(function (form) {

    form.addEventListener("submit", function (e) {

        e.preventDefault();

        const button = form.querySelector("button");
        const isActive = button.classList.contains("active");

        Swal.fire({
            title: isActive ? "Mark this contract as expired?" : "Reactivate this contract?",
            text: isActive ? "It will be marked Expired." : "It will be marked Active.",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: isActive ? "#C62828" : "#2E7D32",
            cancelButtonColor: "#90A4AE",
            confirmButtonText: isActive ? "Yes, Mark Expired" : "Yes, Reactivate"
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