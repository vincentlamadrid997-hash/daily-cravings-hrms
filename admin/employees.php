<?php

$debug_log = fopen(__DIR__ . '/../debug.log', 'a');
fwrite($debug_log, "\n=== Page Access: " . date('Y-m-d H:i:s') . " ===\n");

fwrite($debug_log, "Step 1: File started\n");
session_start();

fwrite($debug_log, "Step 2: Session started\n");
require_once "../auth/admin_auth.php";

fwrite($debug_log, "Step 3: Auth loaded\n");
require_once "../config/db.php";
require_once "../config/csrf.php";

fwrite($debug_log, "Step 4: DB loaded\n");
require_once "includes/hr_audit_log.php";

fwrite($debug_log, "Step 5: Audit log loaded\n");
fclose($debug_log);

$page_title = "Employees";


// Default permissions - all allowed
$canAddEmployees         = true;
$canEditEmployees        = true;
$canViewEmployeeDetails  = true;
$canToggleEmployeeStatus = true;

echo "<!-- DEBUG: Step 6 - Permissions set -->\n";

$success = $_SESSION['employee_success'] ?? "";
$error   = $_SESSION['employee_error'] ?? "";

unset($_SESSION['employee_success']);
unset($_SESSION['employee_error']);

echo "<!-- DEBUG: Step 7 - Sessions cleared -->\n";

try {
    $totalEmployees = $conn->query("
        SELECT COUNT(*)
        FROM employees
    ")->fetchColumn();
    echo "<!-- DEBUG: Step 8 - Total employees queried: $totalEmployees -->\n";
} catch (Exception $e) {
    die("Database Error: " . $e->getMessage());
}

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

// Live search is now handled client-side in JS below, so we always
// load the full employee list here (no $_GET['search'] filtering
// needed server-side anymore).
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
        d.department_name,
        p.position_name
    FROM employees e
    LEFT JOIN departments d ON e.department_id = d.department_id
    LEFT JOIN positions p ON e.position_id = p.position_id
    ORDER BY e.employee_id DESC
";

$stmt = $conn->prepare($sql);
$stmt->execute();

$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<!-- DEBUG: Step 9 - Employees fetched: " . count($employees) . " records -->\n";

?>

<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($page_title); ?> | Daily Cravings Foods Inc.</title>

    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <style>

        .admin-table-scroll {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .admin-table-scroll table {
            min-width: 900px;
        }

    </style>

</head>

<body>

<?php echo "<!-- DEBUG: Step 10 - HTML starting -->\n"; ?>

<div class="admin-wrapper">

    <?php require_once "includes/sidebar.php"; ?>

    <div class="admin-content-wrapper">

        <?php require_once "includes/header.php"; ?>

        <main class="admin-main">

        <section class="admin-user-page">

            <div class="admin-user-summary-grid">

                <div class="admin-user-summary-card blue">

                    <div class="admin-user-summary-icon">
                        <i class="fa-solid fa-users"></i>
                    </div>

                    <div class="admin-user-summary-content">
                        <span>Total Employees</span>
                        <h2><?= number_format($totalEmployees); ?></h2>
                        <p>All employees in the system</p>
                    </div>

                </div>

                <div class="admin-user-summary-card green">

                    <div class="admin-user-summary-icon">
                        <i class="fa-solid fa-user-check"></i>
                    </div>

                    <div class="admin-user-summary-content">
                        <span>Active</span>
                        <h2><?= number_format($activeEmployees); ?></h2>
                        <p>Currently employed</p>
                    </div>

                </div>

                <div class="admin-user-summary-card purple">

                    <div class="admin-user-summary-icon">
                        <i class="fa-solid fa-user-slash"></i>
                    </div>

                    <div class="admin-user-summary-content">
                        <span>Inactive</span>
                        <h2><?= number_format($inactiveEmployees); ?></h2>
                        <p>Inactive employees</p>
                    </div>

                </div>

            </div>

            <div class="admin-user-header">

                <div class="admin-user-title">

                    <h1>
                        <i class="fa-solid fa-users"></i>
                        Employees
                    </h1>

                    <p>Manage employee records and details.</p>

                </div>

                <?php if ($canAddEmployees): ?>

                    <div class="admin-user-actions">

                            <a href="employee_add.php" class="admin-user-add-btn">
                            <i class="fa-solid fa-plus"></i>
                            Add Employee
                        </a>

                    </div>

                <?php endif; ?>

            </div>

            <?php if ($success): ?>
                <div class="admin-alert admin-alert-success">
                    <i class="fa-solid fa-check-circle"></i>
                    <?= htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="admin-alert admin-alert-error">
                    <i class="fa-solid fa-exclamation-circle"></i>
                    <?= htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="admin-user-table-card">

                <div style="padding: 18px 20px; border-bottom: 1px solid #f0f0f0;">

                    <div style="display: flex; gap: 12px; align-items: flex-end;">

                        <div style="flex: 1;">
                            <label style="font-size: 12px; font-weight: 600; color: #888; display: block; margin-bottom: 6px;">Search</label>
                            <input
                                type="text"
                                id="liveSearchInput"
                                placeholder="Search by name, email, or code..."
                                autocomplete="off"
                                style="padding: 8px 12px; border: 1px solid #e0e0e0; border-radius: 8px; width: 100%;"
                            >
                        </div>

                        <button type="button" id="liveSearchClear" style="padding: 9px 18px; background: #f0f0f0; color: #555; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                            Clear
                        </button>

                    </div>

                </div>

                <?php if (empty($employees)): ?>

                    <div class="admin-user-empty">

                        <i class="fa-solid fa-inbox"></i>
                        <h3>No Employees Found</h3>
                        <p>No employee records available.</p>

                    </div>

                <?php else: ?>

                    <div class="admin-table-scroll">

                    <table class="admin-user-table" id="liveSearchTable">

                        <thead>

                            <tr>
                                <th>Employee Code</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Department</th>
                                <th>Position</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($employees as $employee): ?>

                                <tr>

                                    <td style="font-weight: 500;"><?= htmlspecialchars($employee['employee_code']); ?></td>

                                    <td>
                                        <?= htmlspecialchars($employee['first_name'] . ' ' . ($employee['middle_name'] ? $employee['middle_name'] . ' ' : '') . $employee['last_name']); ?>
                                    </td>

                                    <td><?= htmlspecialchars($employee['email']); ?></td>

                                    <td><?= htmlspecialchars($employee['department_name'] ?? 'N/A'); ?></td>

                                    <td><?= htmlspecialchars($employee['position_name'] ?? 'N/A'); ?></td>

                                    <td>
                                        <span class="admin-badge <?= $employee['employment_status'] === 'Active' ? 'admin-badge-success' : 'admin-badge-danger'; ?>">
                                            <?= htmlspecialchars($employee['employment_status']); ?>
                                        </span>
                                    </td>

                                    <td style="text-align: center;">

                                        <div style="display: flex; gap: 6px; justify-content: center;">

                                            <?php if ($canViewEmployeeDetails): ?>

                                                <form method="POST" action="employee_view.php" style="display: inline;">
                                                    <?php csrfField(); ?>
                                                    <input type="hidden" name="employee_id" value="<?= $employee['employee_id']; ?>">
                                                    <button type="submit" class="admin-user-action view" title="View">
                                                        <i class="fa-solid fa-eye"></i>
                                                    </button>
                                                </form>

                                            <?php endif; ?>

                                            <?php if ($canEditEmployees): ?>

                                                <form method="POST" action="employee_edit.php" style="display: inline;">
                                                    <?php csrfField(); ?>
                                                    <input type="hidden" name="employee_id" value="<?= $employee['employee_id']; ?>">
                                                    <button type="submit" class="admin-user-action edit" title="Edit">
                                                        <i class="fa-solid fa-edit"></i>
                                                    </button>
                                                </form>

                                            <?php endif; ?>

                                            <?php if ($canToggleEmployeeStatus): ?>

                                                <form method="POST" action="employee_status.php" style="display: inline;">
                                                    <?php csrfField(); ?>
                                                    <input type="hidden" name="employee_id" value="<?= $employee['employee_id']; ?>">
                                                    <button type="submit" class="admin-user-action status <?= $employee['employment_status'] === 'Active' ? 'active' : 'inactive'; ?>" title="<?= $employee['employment_status'] === 'Active' ? 'Deactivate Employee' : 'Activate Employee'; ?>">
                                                        <i class="fa-solid <?= $employee['employment_status'] === 'Active' ? 'fa-toggle-on' : 'fa-toggle-off'; ?>"></i>
                                                    </button>
                                                </form>

                                            <?php endif; ?>

                                        </div>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                    </div>

                    <div
                        id="liveSearchNoResults"
                        class="admin-user-empty"
                        style="display:none;"
                    >
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <h3>No Matching Employees</h3>
                        <p>Try a different search term.</p>
                    </div>

                <?php endif; ?>

            </div>

        </section>

        </main>

    </div>

</div>

<script>

    (function () {

        var input     = document.getElementById("liveSearchInput");
        var clearBtn  = document.getElementById("liveSearchClear");
        var table     = document.getElementById("liveSearchTable");
        var noResults = document.getElementById("liveSearchNoResults");

        if (!input || !table) {
            return;
        }

        var rows = table.querySelectorAll("tbody tr");

        function applyFilter() {

            var term = input.value.trim().toLowerCase();
            var visibleCount = 0;

            rows.forEach(function (row) {

                var matches = row.textContent.toLowerCase().indexOf(term) !== -1;

                row.style.display = matches ? "" : "none";

                if (matches) {
                    visibleCount++;
                }

            });

            if (noResults) {
                noResults.style.display = (term !== "" && visibleCount === 0)
                    ? ""
                    : "none";
            }

            table.style.display = (term !== "" && visibleCount === 0)
                ? "none"
                : "";

        }

        input.addEventListener("input", applyFilter);

        input.addEventListener("keydown", function (event) {
            if (event.key === "Enter") {
                event.preventDefault();
            }
        });

        if (clearBtn) {
            clearBtn.addEventListener("click", function () {
                input.value = "";
                applyFilter();
                input.focus();
            });
        }

    })();

</script>

<div
    id="userAlertData"
    data-success="<?= htmlspecialchars($success); ?>"
    data-error="<?= htmlspecialchars($error); ?>"
></div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../assets/js/admin.js"></script>

</body>

</html>