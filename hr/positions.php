<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";

$page_title = "Positions";


$success = $_SESSION['position_success'] ?? "";
$error   = $_SESSION['position_error'] ?? "";

unset($_SESSION['position_success']);
unset($_SESSION['position_error']);


$totalPositions = $conn->query("
    SELECT COUNT(*)
    FROM positions
")->fetchColumn();

$activePositions = $conn->query("
    SELECT COUNT(*)
    FROM positions
    WHERE status = 'Active'
")->fetchColumn();

$inactivePositions = $conn->query("
    SELECT COUNT(*)
    FROM positions
    WHERE status = 'Inactive'
")->fetchColumn();


$totalDepartments = $conn->query("
    SELECT COUNT(*)
    FROM departments
    WHERE status = 'Active'
")->fetchColumn();

$search = trim($_GET['search'] ?? "");

$sql = "

SELECT

    p.position_id,
    p.position_name,
    p.description,
    p.status,
    p.created_at,

    d.department_name,

    COUNT(
        CASE
            WHEN e.employment_status = 'Active'
            THEN e.employee_id
        END
    ) AS employee_count

FROM positions p

LEFT JOIN departments d

       ON p.department_id = d.department_id

LEFT JOIN employees e

       ON p.position_id = e.position_id
      AND e.department_id = d.department_id
      AND e.employment_status = 'Active'

WHERE d.status = 'Active'

";

$params = [];

if (!empty($search)) {

    $sql .= "

    AND (

        p.position_name LIKE ?

        OR d.department_name LIKE ?

        OR p.description LIKE ?

        OR p.status LIKE ?

    )

    ";

    $keyword = "%{$search}%";

    $params = [

        $keyword,
        $keyword,
        $keyword,
        $keyword

    ];

}

$sql .= "

GROUP BY

    p.position_id

ORDER BY

    p.created_at DESC

";

$stmt = $conn->prepare($sql);

$stmt->execute($params);

$positions = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

            <section class="hr-pos-page">

                <div class="hr-pos-summary-grid">

                    <div class="hr-pos-summary-card blue">

                        <div class="hr-pos-summary-icon">

                            <i class="fa-solid fa-briefcase"></i>

                        </div>

                        <div class="hr-pos-summary-content">

                            <span>Total Positions</span>

                            <h2><?= $totalPositions; ?></h2>

                            <p>All company positions</p>

                        </div>

                    </div>

                    <div class="hr-pos-summary-card green">

                        <div class="hr-pos-summary-icon">

                            <i class="fa-solid fa-circle-check"></i>

                        </div>

                        <div class="hr-pos-summary-content">

                            <span>Active Positions</span>

                            <h2><?= $activePositions; ?></h2>

                            <p>Available positions</p>

                        </div>

                    </div>

                    <div class="hr-pos-summary-card red">

                        <div class="hr-pos-summary-icon">

                            <i class="fa-solid fa-circle-xmark"></i>

                        </div>

                        <div class="hr-pos-summary-content">

                            <span>Inactive Positions</span>

                            <h2><?= $inactivePositions; ?></h2>

                            <p>Inactive positions</p>

                        </div>

                    </div>

                    <div class="hr-pos-summary-card purple">

                        <div class="hr-pos-summary-icon">

                            <i class="fa-solid fa-building"></i>

                        </div>

                        <div class="hr-pos-summary-content">

                            <span>Departments</span>

                            <h2><?= $totalDepartments; ?></h2>

                            <p>Connected departments</p>

                        </div>

                    </div>

                </div>

                <div class="hr-pos-header">

                    <div class="hr-pos-title">

                        <h1>

                            <i class="fa-solid fa-briefcase"></i>
                            Positions

                        </h1>

                        <p>Manage company positions and department assignments.</p>

                </div>

                    
                    <a href="position_add.php"
                        class="hr-pos-add-btn"
                    >

                        <i class="fa-solid fa-plus"></i>
                        Add Position

                    </a>

                </div>

                <div class="hr-pos-table-card">

                    <div class="hr-pos-search">

                        <div class="hr-pos-search-box">

                            <i class="fa-solid fa-magnifying-glass"></i>

                            <input

                                type="text"
                                id="positionSearch"
                                placeholder="Search position..."
                                autocomplete="off"
                            >

                        </div>

                    </div>

                    <div class="hr-pos-table-wrapper">

                        <table class="hr-pos-table">

                            <thead>

                                <tr>

                                    <th>Position</th>
                                    <th>Department</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Date Created</th>
                                    <th width="170">
                                        Action
                                    </th>

                                </tr>

                            </thead>

                            <tbody id="positionTableBody">

                                <?php if (!empty($positions)): ?>

    <?php foreach ($positions as $position): ?>

        <tr class="position-row">

            <td>

                <div class="hr-pos-name">

                    <div class="hr-pos-avatar">

                        <i class="fa-solid fa-briefcase"></i>

                    </div>

                    <span>

                        <?= htmlspecialchars($position['position_name']); ?>

                    </span>

                </div>

            </td>

            <td>

                <?= htmlspecialchars($position['department_name'] ?? "N/A"); ?>

            </td>

            <td>

                <?= !empty($position['description'])
                    ? htmlspecialchars($position['description'])
                    : "No description";
                ?>

            </td>

            <td>

                <span class="hr-pos-status <?= strtolower($position['status']); ?>">

                    <?php if ($position['status'] === "Active"): ?>

                        <i class="fa-solid fa-circle-check"></i>

                    <?php else: ?>

                        <i class="fa-solid fa-circle-xmark"></i>

                    <?php endif; ?>

                    <?= htmlspecialchars($position['status']); ?>

                </span>

            </td>

            <td>

                <?= date(
                    "F d, Y",
                    strtotime($position['created_at'])
                ); ?>

            </td>

            <td>

                <div class="hr-pos-actions">

                    <form
                        action="position_view.php"
                        method="POST"
                    >

                        <?php csrfField(); ?>

                        <input
                            type="hidden"
                            name="position_id"
                            value="<?= $position['position_id']; ?>"
                        >

                        <button
                            type="submit"
                            class="hr-pos-action view"
                            title="View Position"
                        >

                            <i class="fa-solid fa-eye"></i>

                        </button>

                    </form>

                    <form
                        action="position_edit.php"
                        method="POST"
                    >

                        <?php csrfField(); ?>

                        <input
                            type="hidden"
                            name="position_id"
                            value="<?= $position['position_id']; ?>"
                        >

                        <button
                            type="submit"
                            class="hr-pos-action edit"
                            title="Edit Position"
                        >

                            <i class="fa-solid fa-pen"></i>

                        </button>

                    </form>

                                        <form
                        action="position_status.php"
                        method="POST"
                        class="hr-pos-status-form"
                    >

                        <?php csrfField(); ?>

                        <input
                            type="hidden"
                            name="position_id"
                            value="<?= $position['position_id']; ?>"
                        >

                        <button

                            type="submit"

                            class="hr-pos-action status <?= strtolower($position['status']); ?>"

                            title="<?= $position['status'] === 'Active'
                                ? 'Deactivate Position'
                                : 'Activate Position';
                            ?>"

                        >

                            <?php if ($position['status'] === "Active"): ?>

                                <i class="fa-solid fa-toggle-on"></i>

                            <?php else: ?>

                                <i class="fa-solid fa-toggle-off"></i>

                            <?php endif; ?>

                        </button>

                    </form>

                </div>

            </td>

        </tr>

    <?php endforeach; ?>

<?php endif; ?>

<tr
    id="noPositionResult"
    style="display:none;"
>

    <td colspan="6">

        <div class="hr-pos-empty">

            <i class="fa-solid fa-magnifying-glass"></i>

            <h3>No Positions Found</h3>

            <p>No records matched your search.</p>

        </div>

    </td>

</tr>

<?php if (empty($positions)): ?>

<tr>

    <td colspan="6">

        <div class="hr-pos-empty">

            <i class="fa-solid fa-briefcase"></i>

            <h3>No Positions Found</h3>

            <p>

                There are currently no position records available.

            </p>

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

        confirmButtonColor: "#003DA5"

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

        confirmButtonColor: "#003DA5"

    });

});

</script>

<?php endif; ?>


<script>
document.querySelectorAll(".hr-pos-status-form").forEach(function (form) {

    form.addEventListener("submit", function (e) {

        e.preventDefault();

        const button = form.querySelector("button");
        const isActive = button.classList.contains("active");

        Swal.fire({
            title: isActive ? "Deactivate this position?" : "Activate this position?",
            text: isActive ? "It will be marked Inactive." : "It will be marked Active.",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: isActive ? "#C62828" : "#2E7D32",
            cancelButtonColor: "#90A4AE",
            confirmButtonText: isActive ? "Yes, Deactivate" : "Yes, Activate"
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