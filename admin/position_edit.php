<?php

session_start();

require_once "../auth/admin_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";
require_once "includes/hr_audit_log.php";

$page_title = "Edit Position";

if (isset($_POST['position_id'])) {
    $_SESSION['selected_position'] = (int) $_POST['position_id'];
}

if (!isset($_SESSION['selected_position'])) {
    header("Location: positions.php");
    exit;
}

$position_id = (int) $_SESSION['selected_position'];

$stmt = $conn->prepare("
    SELECT * FROM positions WHERE position_id = ?
");

$stmt->execute([$position_id]);
$position = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$position) {
    unset($_SESSION['selected_position']);
    header("Location: positions.php");
    exit;
}

$departments = $conn->query("
    SELECT department_id, department_name
    FROM departments
    WHERE status = 'Active'
    ORDER BY department_name ASC
")->fetchAll(PDO::FETCH_ASSOC);

$alert = ["type" => "", "message" => ""];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_position'])) {

    requireCSRFToken("position_edit.php");

    $position_name = trim($_POST['position_name']);
    $description = trim($_POST['description']);
    $department_id = $_POST['department_id'];
    $status = $_POST['status'];

    if (empty($position_name) || empty($department_id)) {
        $alert["type"] = "error";
        $alert["message"] = "Please complete all required fields.";
    } else {
        try {
            $conn->beginTransaction();

            // Track changes
            $changes = [];
            if ($position['position_name'] !== $position_name) {
                $changes[] = "name: " . $position['position_name'] . " -> " . $position_name;
            }
            if (($position['description'] ?? '') !== $description) {
                $changes[] = "description changed";
            }
            if ($position['department_id'] != $department_id) {
                $changes[] = "department changed";
            }
            if ($position['status'] !== $status) {
                $changes[] = "status: " . $position['status'] . " -> " . $status;
            }

            $stmt = $conn->prepare("
                UPDATE positions
                SET position_name = ?, description = ?, department_id = ?, status = ?, updated_at = NOW()
                WHERE position_id = ?
            ");

            $stmt->execute([
                $position_name,
                !empty($description) ? $description : null,
                $department_id,
                $status,
                $position_id
            ]);

            // ========== LOG THE POSITION EDIT ==========
            if (!empty($changes)) {
                logPositionEdit($conn, $position_name, $changes);
            }
            // ==========================================

            $conn->commit();

            unset($_SESSION['selected_position']);
            $_SESSION['position_success'] = "Position updated successfully.";
            header("Location: positions.php");
            exit;

        } catch (PDOException $e) {
            $conn->rollBack();
            error_log("Position edit error: " . $e->getMessage());
            $alert["type"] = "error";
            $alert["message"] = "Failed to update position.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($page_title); ?> | Daily Cravings Foods Inc.</title>

    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/crud_hr.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>

<div class="admin-wrapper">

    <?php require_once "includes/sidebar.php"; ?>

    <div class="admin-content-wrapper">

        <?php require_once "includes/header.php"; ?>

        <main class="admin-main">

        <section class="crud-page">

            <div class="crud-header">

                <div class="crud-title">

                    <h1>
                        <i class="fa-solid fa-edit"></i>
                        Edit Position
                    </h1>

                    <p>Update position details.</p>

                </div>

                <div class="crud-header-actions">

                    <a href="positions.php" class="crud-back-btn">
                        <i class="fa-solid fa-arrow-left"></i>
                        Back to Positions
                    </a>

                </div>

            </div>

            <?php if ($alert['type']): ?>
                <div class="crud-alert <?= $alert['type'] === 'error' ? 'error' : 'success'; ?>">
                    <i class="fa-solid fa-<?= $alert['type'] === 'error' ? 'exclamation-circle' : 'check-circle'; ?>"></i>
                    <?= htmlspecialchars($alert['message']); ?>
                </div>
            <?php endif; ?>

            <div class="crud-card">

                <div class="crud-card-header">

                    <div class="crud-icon">
                        <i class="fa-solid fa-briefcase"></i>
                    </div>

                    <div>
                        <h2>Position Details</h2>
                        <p>Update this position's information.</p>
                    </div>

                </div>

                <form method="POST" action="position_edit.php">
                    <?php csrfField(); ?>

                    <input type="hidden" name="update_position" value="1">

                    <div class="crud-form-grid">

                        <div class="crud-form-group">
                            <label>
                                <i class="fa-solid fa-briefcase"></i>
                                Position Name
                            </label>
                            <input type="text" name="position_name" required value="<?= htmlspecialchars($position['position_name']); ?>">
                        </div>

                        <div class="crud-form-group">
                            <label>
                                <i class="fa-solid fa-building"></i>
                                Department
                            </label>
                            <select name="department_id" required>
                                <option value="">Select Department</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['department_id']; ?>" <?= $position['department_id'] == $dept['department_id'] ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($dept['department_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="crud-form-group">
                            <label>
                                <i class="fa-solid fa-circle-check"></i>
                                Status
                            </label>
                            <select name="status">
                                <option value="Active" <?= $position['status'] === 'Active' ? 'selected' : ''; ?>>Active</option>
                                <option value="Inactive" <?= $position['status'] === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>

                        <div class="crud-form-group crud-full">
                            <label>
                                <i class="fa-solid fa-align-left"></i>
                                Description
                            </label>
                            <textarea name="description" rows="4" style="resize: vertical;"><?= htmlspecialchars($position['description'] ?? ''); ?></textarea>
                        </div>

                    </div>

                    <div class="crud-actions">

                        <a href="positions.php" class="crud-btn crud-btn-secondary">
                            <i class="fa-solid fa-xmark"></i>
                            Cancel
                        </a>

                                                <button type="submit" id="posEditSaveBtn" disabled class="crud-btn crud-btn-primary">
                            <i class="fa-solid fa-save"></i>
                            Update Position
                        </button>

                    </div>

                </form>

            </div>

        </section>

        </main>

    </div>

</div>

<script>
(function () {

    const form = document.querySelector('form[action="position_edit.php"]');
    const saveBtn = document.getElementById("posEditSaveBtn");
    const initialState = new URLSearchParams(new FormData(form)).toString();

    function checkForChanges() {
        const currentState = new URLSearchParams(new FormData(form)).toString();
        saveBtn.disabled = (currentState === initialState);
    }

    form.addEventListener("input", checkForChanges);
    form.addEventListener("change", checkForChanges);

    form.addEventListener("submit", function (e) {

        e.preventDefault();

        Swal.fire({
            icon: "question",
            title: "Save changes?",
            text: "This will update this position's record.",
            showCancelButton: true,
            confirmButtonText: "Yes, Save",
            cancelButtonText: "Cancel",
            confirmButtonColor: "#003DA5",
            reverseButtons: true
        }).then(function (result) {

            if (result.isConfirmed) {
                form.submit();
            }

        });

    });

})();
</script>

<script src="../assets/js/admin.js"></script>

</body>

</html>