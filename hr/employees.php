<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/permissions.php";

$page_title = "Employees";

// Server-side permission enforcement (Admin bypasses this automatically).
requirePermission($conn, 'view_employees', 'dashboard.php');

$currentRole = $_SESSION['role'] ?? '';
$canAddEmployees         = userHasPermission($conn, $currentRole, 'add_employees');
$canEditEmployees        = userHasPermission($conn, $currentRole, 'edit_employees');
$canViewEmployeeDetails  = userHasPermission($conn, $currentRole, 'view_employee_details');
$canToggleEmployeeStatus = userHasPermission($conn, $currentRole, 'toggle_employee_status');

$success = $_SESSION['employee_success'] ?? "";
$error   = $_SESSION['employee_error'] ?? "";

unset($_SESSION['employee_success']);
unset($_SESSION['employee_error']);


$totalEmployees = $conn->query("
    SELECT COUNT(*)
    FROM employees
")->fetchColumn();


$activeEmployees = $conn->query("
    SELECT COUNT(*)
    FROM employees
    WHERE employment_status = 'Active'
")->fetchColumn();


$inactiveEmployees = $conn->query("
    SELECT COUNT(*)
    FROM employees
    WHERE employment_status = 'Inactive'
")->fetchColumn();


$resignedEmployees = $conn->query("
    SELECT COUNT(*)
    FROM employees
    WHERE employment_status = 'Resigned'
")->fetchColumn();


$search = trim($_GET['search'] ?? "");


$sql = "

SELECT

    e.employee_id,
    e.employee_code,
    e.first_name,
    e.middle_name,
    e.last_name,
    e.email,
    e.phone,
    e.employment_status,
    e.hire_date,

    d.department_name,
    p.position_name

FROM employees e

LEFT JOIN departments d
    ON e.department_id = d.department_id

LEFT JOIN positions p
    ON e.position_id = p.position_id

WHERE 1

";


$params = [];


if (!empty($search)) {

    $sql .= "

    AND (

        e.employee_code LIKE ?
        OR e.first_name LIKE ?
        OR e.last_name LIKE ?
        OR e.email LIKE ?
        OR d.department_name LIKE ?
        OR p.position_name LIKE ?
        OR e.employment_status LIKE ?

    )

    ";

    $keyword = "%" . $search . "%";

    $params = [
        $keyword,
        $keyword,
        $keyword,
        $keyword,
        $keyword,
        $keyword,
        $keyword
    ];
}


$sql .= "

ORDER BY e.created_at DESC

";


$stmt = $conn->prepare($sql);

$stmt->execute($params);

$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars($page_title); ?> | Daily Cravings Foods Inc.
    </title>


    <link rel="stylesheet" href="../assets/css/global.css">

    <link rel="stylesheet" href="../assets/css/hr.css">

    <link 
        rel="stylesheet" 
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>


<body>


<div class="hr-wrapper">

    <?php require_once "includes/sidebar.php"; ?>

    <div class="hr-content-wrapper">

        <?php require_once "includes/header.php"; ?>

        <main class="hr-main">

            <section class="hr-emp-page">

                <div class="hr-emp-summary-grid">

                    <div class="hr-emp-summary-card blue">

                        <div class="hr-emp-summary-icon">
                            <i class="fa-solid fa-users"></i>
                        </div>

                        <div class="hr-emp-summary-content">

                            <span>Total Employees</span>

                            <h2><?= $totalEmployees; ?></h2>

                            <p>All employee records</p>

                        </div>

                    </div>

                    <div class="hr-emp-summary-card green">

                        <div class="hr-emp-summary-icon">
                            <i class="fa-solid fa-user-check"></i>
                        </div>

                        <div class="hr-emp-summary-content">

                            <span>Active Employees</span>

                            <h2><?= $activeEmployees; ?></h2>

                            <p>Currently working</p>

                        </div>

                    </div>

                    <div class="hr-emp-summary-card yellow">

                        <div class="hr-emp-summary-icon">
                            <i class="fa-solid fa-user-clock"></i>
                        </div>

                        <div class="hr-emp-summary-content">

                            <span>Inactive Employees</span>

                            <h2><?= $inactiveEmployees; ?></h2>

                            <p>Temporarily inactive</p>

                        </div>

                    </div>

                    <div class="hr-emp-summary-card purple">

                        <div class="hr-emp-summary-icon">
                            <i class="fa-solid fa-user-xmark"></i>
                        </div>

                        <div class="hr-emp-summary-content">

                            <span>Resigned</span>

                            <h2><?= $resignedEmployees; ?></h2>

                            <p>Former employees</p>

                        </div>

                    </div>

                </div>

                <div class="hr-emp-header">

                    <div class="hr-emp-title">

                        <h1>
                            <i class="fa-solid fa-users"></i>
                            Employees
                        </h1>

                        <p>Manage employee records and information.</p>

                    </div>

                    <div class="hr-emp-header-actions">

                        <?php if ($canAddEmployees): ?>

                            <a href="employee_add.php" class="hr-emp-add-btn">

                                <i class="fa-solid fa-user-plus"></i>
                                Add Employee

                            </a>

                        <?php endif; ?>

                    </div>

                </div>

                <div class="hr-emp-table-card">

                    <div class="hr-emp-search">

                        <form method="GET">

                            <div class="hr-emp-search-box">

                                <i class="fa-solid fa-magnifying-glass"></i>

                                <input
                                    type="text"
                                    name="search"
                                    id="employeeSearch"
                                    placeholder="Search employee..."
                                    value="<?= htmlspecialchars($search); ?>"
                                >

                            </div>

                        </form>

                    </div>

                    <div class="hr-emp-table-wrapper">

                        <table class="hr-emp-table">

                            <thead>

                                <tr>

                                    <th>Employee</th>
                                    <th>Employee ID</th>
                                    <th>Department</th>
                                    <th>Position</th>
                                    <th>Status</th>
                                    <th>Hire Date</th>
                                    <th>Action</th>

                                </tr>

                            </thead>

                            <tbody id="employeeTableBody">

                            <?php if (!empty($employees)): ?>

                                <?php foreach ($employees as $employee): ?>

                                    <tr class="employee-row">

                                        <td>

                                            <div class="hr-emp-name">

                                                <div class="hr-emp-avatar">

                                                    <i class="fa-solid fa-user"></i>

                                                </div>

                                                <div>

                                                    <strong>

                                                        <?= htmlspecialchars(
                                                            $employee['first_name'] . " " . $employee['last_name']
                                                        ); ?>

                                                    </strong>

                                                    <small>

                                                        <?= htmlspecialchars(
                                                            $employee['email']
                                                        ); ?>

                                                    </small>

                                                </div>

                                            </div>

                                        </td>

                                        <td>

                                            <?= htmlspecialchars(
                                                $employee['employee_code'] ?? "N/A"
                                            ); ?>

                                        </td>

                                        <td>

                                            <?= htmlspecialchars(
                                                $employee['department_name'] ?? "N/A"
                                            ); ?>

                                        </td>

                                        <td>

                                            <?= htmlspecialchars(
                                                $employee['position_name'] ?? "N/A"
                                            ); ?>

                                        </td>

                                        <td>

                                            <span class="hr-emp-status <?= strtolower($employee['employment_status']); ?>">

                                                <?= htmlspecialchars(
                                                    $employee['employment_status']
                                                ); ?>

                                            </span>

                                        </td>

                                        <td>

                                            <?= date(
                                                "F d, Y",
                                                strtotime($employee['hire_date'])
                                            ); ?>

                                        </td>

                                        <td>

                                            <div class="hr-emp-actions">

                                                <?php if ($canViewEmployeeDetails): ?>

                                                <form action="employee_view.php" method="POST">

                                                        <?php csrfField(); ?>

                                                        <input
                                                            type="hidden"
                                                            name="employee_id"
                                                            value="<?= $employee['employee_id']; ?>"
                                                        >

                                                        <button
                                                            type="submit"
                                                            class="hr-emp-action view"
                                                            title="View Employee Details"
                                                        >

                                                            <i class="fa-solid fa-eye"></i>

                                                        </button>

                                                    </form>

                                                <?php endif; ?>

                                                <?php if ($canEditEmployees): ?>

                                                    <form action="employee_edit.php" method="POST">

                                                        <?php csrfField(); ?>

                                                        <input
                                                            type="hidden"
                                                            name="employee_id"
                                                            value="<?= $employee['employee_id']; ?>"
                                                        >

                                                        <button
                                                            type="submit"
                                                            class="hr-emp-action edit"
                                                            title="Edit Employee"
                                                        >

                                                            <i class="fa-solid fa-pen"></i>

                                                        </button>

                                                    </form>

                                                <?php endif; ?>

                                                <?php if ($canToggleEmployeeStatus): ?>

                                                    <?php

                                                    $isActive = 
                                                        $employee['employment_status'] === "Active";

                                                    ?>

                                                    <form action="employee_status.php" method="POST" class="hr-emp-status-form" data-was-resigned="<?= $employee['employment_status'] === 'Resigned' ? '1' : '0'; ?>">

                                                        <?php csrfField(); ?>

                                                        <input
                                                            type="hidden"
                                                            name="employee_id"
                                                            value="<?= $employee['employee_id']; ?>"
                                                        >

                                                        <button
                                                            type="submit"
                                                            class="hr-emp-action <?= $isActive ? 'deactivate' : 'activate'; ?>"
                                                            title="<?= $isActive ? 'Deactivate Employee' : ($employee['employment_status'] === 'Resigned' ? 'Reactivate Resigned Employee' : 'Activate Employee'); ?>"
                                                        >

                                                            <i class="fa-solid <?= $isActive ? 'fa-user-slash' : 'fa-user-check'; ?>"></i>

                                                        </button>

                                                    </form>

                                                <?php endif; ?>

                                            </div>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <tr>

                                    <td colspan="7">

                                        <div class="hr-emp-empty">

                                            <i class="fa-solid fa-user-slash"></i>

                                            <h3>No Employee Records Found</h3>

                                            <p>There are currently no employee records available.</p>

                                        </div>

                                    </td>

                                </tr>

                            <?php endif; ?>

                            <tr id="noEmployeeResult" style="display:none;">

                                <td colspan="7">

                                    <div class="hr-emp-empty">

                                        <i class="fa-solid fa-magnifying-glass"></i>

                                        <h3>No Matching Employee Found</h3>

                                        <p>Try searching another keyword.</p>

                                    </div>

                                </td>

                            </tr>

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

Swal.fire({

    icon: "success",

    title: "Updated!",

    text: <?= json_encode($success); ?>,

    confirmButtonColor: "#003DA5"

});

</script>


<?php endif; ?>

<?php if (!empty($error)): ?>


<script>

Swal.fire({

    icon: "error",

    title: "Error",

    text: <?= json_encode($error); ?>,

    confirmButtonColor: "#003DA5"

});

</script>


<?php endif; ?>


<script>

document.querySelectorAll(".hr-emp-status-form").forEach(function (form) {

    form.addEventListener("submit", function (e) {

        e.preventDefault();

        const button = form.querySelector("button");
        const wasResigned = form.getAttribute("data-was-resigned") === "1";
        const isDeactivating = button.classList.contains("deactivate");

        let title, text, confirmColor, confirmText;

        if (wasResigned) {

            title = "Reactivate this employee?";
            text = "This employee previously resigned. This will set their status back to Active.";
            confirmColor = "#2E7D32";
            confirmText = "Yes, Reactivate";

        } else if (isDeactivating) {

            title = "Deactivate this employee?";
            text = "They will be marked Inactive.";
            confirmColor = "#C62828";
            confirmText = "Yes, Deactivate";

        } else {

            title = "Activate this employee?";
            text = "They will be marked Active.";
            confirmColor = "#2E7D32";
            confirmText = "Yes, Activate";

        }

        Swal.fire({
            title: title,
            text: text,
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: confirmColor,
            cancelButtonColor: "#90A4AE",
            confirmButtonText: confirmText
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