<?php

session_start();

require_once "../auth/admin_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";
require_once "includes/hr_audit_log.php";

$page_title = "Positions";

// Permissions - all allowed
$canAddPositions = true;
$canEditPositions = true;
$canTogglePositionStatus = true;

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

// Live search is now handled client-side in JS below, so we always
// load the full position list here (no $_GET['search'] filtering
// needed server-side anymore).
$sql = "
    SELECT
        p.position_id,
        p.position_name,
        p.description,
        p.status,
        d.department_name,
        p.created_at
    FROM positions p
    LEFT JOIN departments d ON p.department_id = d.department_id
    ORDER BY p.position_id DESC
";

$stmt = $conn->prepare($sql);
$stmt->execute();

$positions = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
            min-width: 800px;
        }

    </style>

</head>

<body>

<div class="admin-wrapper">

    <?php require_once "includes/sidebar.php"; ?>

    <div class="admin-content-wrapper">

        <?php require_once "includes/header.php"; ?>

        <main class="admin-main">

        <section class="admin-user-page">

            <div class="admin-user-summary-grid">

                <div class="admin-user-summary-card blue">

                    <div class="admin-user-summary-icon">
                        <i class="fa-solid fa-briefcase"></i>
                    </div>

                    <div class="admin-user-summary-content">
                        <span>Total Positions</span>
                        <h2><?= number_format($totalPositions); ?></h2>
                        <p>All job positions</p>
                    </div>

                </div>

                <div class="admin-user-summary-card green">

                    <div class="admin-user-summary-icon">
                        <i class="fa-solid fa-check-circle"></i>
                    </div>

                    <div class="admin-user-summary-content">
                        <span>Active</span>
                        <h2><?= number_format($activePositions); ?></h2>
                        <p>Active positions</p>
                    </div>

                </div>

                <div class="admin-user-summary-card purple">

                    <div class="admin-user-summary-icon">
                        <i class="fa-solid fa-times-circle"></i>
                    </div>

                    <div class="admin-user-summary-content">
                        <span>Inactive</span>
                        <h2><?= number_format($inactivePositions); ?></h2>
                        <p>Inactive positions</p>
                    </div>

                </div>

            </div>

            <div class="admin-user-header">

                <div class="admin-user-title">

                    <h1>
                        <i class="fa-solid fa-briefcase"></i>
                        Positions
                    </h1>

                    <p>Manage job positions and titles.</p>

                </div>

                <div class="admin-user-actions">

                        <a href="position_add.php" class="admin-user-add-btn">
                        <i class="fa-solid fa-plus"></i>
                        Add Position
                    </a>

                </div>

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
                                placeholder="Search by position name, department, or description..."
                                autocomplete="off"
                                style="padding: 8px 12px; border: 1px solid #e0e0e0; border-radius: 8px; width: 100%;"
                            >
                        </div>

                        <button type="button" id="liveSearchClear" style="padding: 9px 18px; background: #f0f0f0; color: #555; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                            Clear
                        </button>

                    </div>

                </div>

                <?php if (empty($positions)): ?>

                    <div class="admin-user-empty">

                        <i class="fa-solid fa-inbox"></i>
                        <h3>No Positions Found</h3>
                        <p>No position records available.</p>

                    </div>

                <?php else: ?>

                    <div class="admin-table-scroll">

                    <table class="admin-user-table" id="liveSearchTable">

                        <thead>

                            <tr>
                                <th>Position Name</th>
                                <th>Department</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($positions as $pos): ?>

                                <tr>

                                    <td style="font-weight: 500;"><?= htmlspecialchars($pos['position_name']); ?></td>

                                    <td><?= htmlspecialchars($pos['department_name'] ?? 'N/A'); ?></td>

                                    <td><?= htmlspecialchars(substr($pos['description'] ?? '', 0, 50)); ?></td>

                                    <td>
                                        <span class="admin-badge <?= $pos['status'] === 'Active' ? 'admin-badge-success' : 'admin-badge-danger'; ?>">
                                            <?= htmlspecialchars($pos['status']); ?>
                                        </span>
                                    </td>

                                    <td><?= date('M d, Y', strtotime($pos['created_at'])); ?></td>

                                    <td style="text-align: center;">

                                        <div style="display: flex; gap: 6px; justify-content: center;">

                                        <form method="POST" action="position_view.php" style="display: inline;">
                                            <?php csrfField(); ?>
                                                <input type="hidden" name="position_id" value="<?= $pos['position_id']; ?>">
                                                <button type="submit" class="admin-user-action view" title="View">
                                                    <i class="fa-solid fa-eye"></i>
                                                </button>
                                        </form>

                                        <form method="POST" action="position_edit.php" style="display: inline;">
                                            <?php csrfField(); ?>
                                            <input type="hidden" name="position_id" value="<?= $pos['position_id']; ?>">
                                            <button type="submit" class="admin-user-action edit" title="Edit">
                                                <i class="fa-solid fa-edit"></i>
                                            </button>
                                        </form>

                                            <form method="POST" action="position_status.php" style="display: inline;">
                                                <?php csrfField(); ?>
                                                <input type="hidden" name="position_id" value="<?= $pos['position_id']; ?>">
                                                <button type="submit" class="admin-user-action status <?= $pos['status'] === 'Active' ? 'active' : 'inactive'; ?>" title="<?= $pos['status'] === 'Active' ? 'Deactivate Position' : 'Activate Position'; ?>">
                                                    <i class="fa-solid <?= $pos['status'] === 'Active' ? 'fa-toggle-on' : 'fa-toggle-off'; ?>"></i>
                                                </button>
                                            </form>

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
                        <h3>No Matching Positions</h3>
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