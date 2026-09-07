<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";

$page_title = "Departments";

$success = $_SESSION['department_success'] ?? "";

$error = $_SESSION['department_error'] ?? "";


unset($_SESSION['department_success']);

unset($_SESSION['department_error']);


$totalDepartments = $conn->query("
    SELECT COUNT(*)
    FROM departments
")->fetchColumn();


$activeDepartments = $conn->query("
    SELECT COUNT(*)
    FROM departments
    WHERE status = 'Active'
")->fetchColumn();


$inactiveDepartments = $conn->query("
    SELECT COUNT(*)
    FROM departments
    WHERE status = 'Inactive'
")->fetchColumn();


$withEmployees = $conn->query("
    SELECT COUNT(DISTINCT department_id)
    FROM employees
    WHERE department_id IS NOT NULL
    AND employment_status = 'Active'
")->fetchColumn();


$search = trim($_GET['search'] ?? "");


$sql = "

SELECT

    d.department_id,
    d.department_name,
    d.description,
    d.status,
    d.created_at,

    COUNT(e.employee_id) AS employee_count

FROM departments d

LEFT JOIN employees e

    ON d.department_id = e.department_id

    AND e.employment_status = 'Active'

WHERE 1

";


$params = [];


if (!empty($search)) {

    $sql .= "

    AND (

        d.department_name LIKE ?

        OR d.description LIKE ?

        OR d.status LIKE ?

    )

    ";

    $keyword = "%" . $search . "%";

    $params = [
        $keyword,
        $keyword,
        $keyword
    ];
}


$sql .= "

GROUP BY d.department_id

ORDER BY d.created_at DESC

";


$stmt = $conn->prepare($sql);

$stmt->execute($params);

$departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

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


    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>


<body>


<div class="hr-wrapper">

    <?php require_once "includes/sidebar.php"; ?>

    <div class="hr-content-wrapper">

        <?php require_once "includes/header.php"; ?>

        <main class="hr-main">

            <section class="hr-dept-page">

<div class="hr-dept-summary-grid">

    <div class="hr-dept-summary-card blue">

        <div class="hr-dept-summary-icon">
            <i class="fa-solid fa-building"></i>
        </div>

        <div class="hr-dept-summary-content">

            <span>
                Total Departments
            </span>

            <h2>
                <?= $totalDepartments; ?>
            </h2>

            <p>All department records</p>

        </div>

    </div>

    <div class="hr-dept-summary-card green">

        <div class="hr-dept-summary-icon">
            <i class="fa-solid fa-circle-check"></i>
        </div>

        <div class="hr-dept-summary-content">

            <span>
                Active Departments
            </span>

            <h2>
                <?= $activeDepartments; ?>
            </h2>

            <p>
                Currently active
            </p>

        </div>

    </div>

    <div class="hr-dept-summary-card yellow">

        <div class="hr-dept-summary-icon">
            <i class="fa-solid fa-building-circle-exclamation"></i>
        </div>

        <div class="hr-dept-summary-content">

            <span>
                Inactive Departments
            </span>

            <h2>
                <?= $inactiveDepartments; ?>
            </h2>

            <p>Temporarily inactive</p>

        </div>

    </div>

    <div class="hr-dept-summary-card purple">

        <div class="hr-dept-summary-icon">
            <i class="fa-solid fa-users"></i>
        </div>

        <div class="hr-dept-summary-content">

            <span>
                With Employees
            </span>

            <h2>
                <?= $withEmployees; ?>
            </h2>

            <p>Departments with staff</p>

        </div>

    </div>


</div>

<div class="hr-dept-header">

    <div class="hr-dept-title">

        <h1>

            <i class="fa-solid fa-building"></i>
            Departments

        </h1>

        <p>Manage company departments and organizational structure.</p>

    </div>

    <div class="hr-dept-header-actions">

        <a href="department_add.php"
           class="hr-dept-add-btn">

            <i class="fa-solid fa-building"></i>
            Add Department

        </a>

    </div>

</div>

<div class="hr-dept-table-card">

    <div class="hr-dept-search">

        <form method="GET">

            <div class="hr-dept-search-box">

                <i class="fa-solid fa-magnifying-glass"></i>

                <input

                    type="text"
                    name="search"
                    id="departmentSearch"
                    placeholder="Search department..."
                    value="<?= htmlspecialchars($search); ?>"
                >

            </div>

        </form>

    </div>

    <div class="hr-dept-table-wrapper">

        <table class="hr-dept-table">

            <thead>

                <tr>

                    <th>Department</th>
                    <th>Description</th>
                    <th>Employees</th>
                    <th>Status</th>
                    <th>Created Date</th>
                    <th>Action</th>

                </tr>

            </thead>

            <tbody id="departmentTableBody">

                <?php if (!empty($departments)): ?>

<?php foreach ($departments as $department): ?>

<tr class="department-row">

    <td>

        <div class="hr-dept-name">

            <div class="hr-dept-avatar">

                <i class="fa-solid fa-building"></i>

            </div>

            <div>

                <strong>

                    <?= htmlspecialchars($department['department_name']); ?>

                </strong>

                <small>
                    Department
                </small>

            </div>

        </div>

    </td>

    <td>

        <?= !empty($department['description'])

            ? htmlspecialchars($department['description'])

            : "No description available";

        ?>

    </td>

    <td>

        <span class="hr-dept-employee-count <?= $department['employee_count'] == 0 ? 'zero' : ''; ?>">

            <i class="fa-solid fa-users"></i>

            <?= $department['employee_count']; ?>

            Employee<?= $department['employee_count'] != 1 ? "s" : ""; ?>

        </span>

    </td>

    <td>

        <span class="hr-emp-status <?= strtolower($department['status']); ?>">

            <?= htmlspecialchars($department['status']); ?>

        </span>

    </td>

    <td>

        <?= date(

            "F d, Y",

            strtotime($department['created_at'])

        ); ?>

    </td>

    <td>

        <div class="hr-dept-actions">

            <form action="department_view.php" method="POST">

                <?php csrfField(); ?>

                <input

                    type="hidden"
                    name="department_id"
                    value="<?= $department['department_id']; ?>"
                >

                <button

                    type="submit"
                    class="hr-dept-action view"
                    title="View Department Details"

                >

                    <i class="fa-solid fa-eye"></i>

                </button>

            </form>

            <form action="department_edit.php" method="POST">

                <?php csrfField(); ?>

                <input

                    type="hidden"
                    name="department_id"
                    value="<?= $department['department_id']; ?>"
                >

                <button

                    type="submit"
                    class="hr-dept-action edit"
                    title="Edit Department"

                >

                    <i class="fa-solid fa-pen"></i>

                </button>

            </form>

            <?php

            $isActive = $department['status'] === "Active";

            ?>

                <form action="department_status.php" method="POST" class="hr-dept-status-form">

                <?php csrfField(); ?>

                <input

                    type="hidden"
                    name="department_id"
                    value="<?= $department['department_id']; ?>"
                >

                <button

                    type="submit"
                    class="hr-dept-action <?= $isActive ? 'deactivate' : 'activate'; ?>"
                    title="<?= $isActive
                        ? 'Deactivate Department'
                        : 'Activate Department';
                    ?>"

                >

                    <i class="fa-solid <?= $isActive
                        ? 'fa-building-circle-xmark'
                        : 'fa-building-circle-check';
                    ?>"></i>

                </button>

            </form>

        </div>

    </td>

</tr>


<?php endforeach; ?>


<?php else: ?>


<tr>

    <td colspan="6">

        <div class="hr-dept-empty">

            <i class="fa-solid fa-building-circle-exclamation"></i>

            <h3>No Department Records Found</h3>

            <p>There are currently no department records available.</p>

        </div>

    </td>

</tr>


<?php endif; ?>


<tr id="noDepartmentResult" style="display:none;">

    <td colspan="6">

        <div class="hr-dept-empty">

            <i class="fa-solid fa-magnifying-glass"></i>

            <h3>No Matching Department Found</h3>

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

    icon:"success",

    title:"Updated!",

    text: <?= json_encode($success); ?>,

    confirmButtonColor:"#003DA5"

});

</script>

<?php endif; ?>

<script>
document.querySelectorAll(".hr-dept-status-form").forEach(function (form) {

    form.addEventListener("submit", function (e) {

        e.preventDefault();

        const button = form.querySelector("button");
        const isDeactivating = button.classList.contains("deactivate");

        Swal.fire({
            title: isDeactivating ? "Deactivate this department?" : "Activate this department?",
            text: isDeactivating ? "It will be marked Inactive." : "It will be marked Active.",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: isDeactivating ? "#C62828" : "#2E7D32",
            cancelButtonColor: "#90A4AE",
            confirmButtonText: isDeactivating ? "Yes, Deactivate" : "Yes, Activate"
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