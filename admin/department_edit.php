<?php

session_start();

require_once "../auth/admin_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";
require_once "includes/hr_audit_log.php";

$page_title = "Edit Department";
if (isset($_POST['department_id'])) {
    $_SESSION['selected_department'] = (int) $_POST['department_id'];
}

if (!isset($_SESSION['selected_department'])) {
    header("Location: departments.php");
    exit;
}

$department_id = (int) $_SESSION['selected_department'];

$stmt = $conn->prepare("
    SELECT * FROM departments WHERE department_id = ?
");

$stmt->execute([$department_id]);
$department = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$department) {
    unset($_SESSION['selected_department']);
    header("Location: departments.php");
    exit;
}

$alert = ["type" => "", "message" => ""];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_department'])) {

    requireCSRFToken("department_edit.php");

    $department_name = trim($_POST['department_name']);
    $description = trim($_POST['description']);
    $status = $_POST['status'];

    if (empty($department_name)) {
        $alert["type"] = "error";
        $alert["message"] = "Department name is required.";
    } else {
        try {
            $conn->beginTransaction();

            // Track changes
            $changes = [];
            if ($department['department_name'] !== $department_name) {
                $changes[] = "name: " . $department['department_name'] . " -> " . $department_name;
            }
            if (($department['description'] ?? '') !== $description) {
                $changes[] = "description changed";
            }
            if ($department['status'] !== $status) {
                $changes[] = "status: " . $department['status'] . " -> " . $status;
            }

            $stmt = $conn->prepare("
                UPDATE departments
                SET department_name = ?, description = ?, status = ?, updated_at = NOW()
                WHERE department_id = ?
            ");

            $stmt->execute([
                $department_name,
                !empty($description) ? $description : null,
                $status,
                $department_id
            ]);

            // ========== LOG THE DEPARTMENT EDIT ==========
            if (!empty($changes)) {
                logDepartmentEdit($conn, $department_name, $changes);
            }
            // ============================================

            $conn->commit();

            unset($_SESSION['selected_department']);
            $_SESSION['department_success'] = "Department updated successfully.";
            header("Location: departments.php");
            exit;

        } catch (PDOException $e) {
            $conn->rollBack();
            error_log("Department edit error: " . $e->getMessage());
            $alert["type"] = "error";
            $alert["message"] = "Failed to update department.";
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
                        Edit Department
                    </h1>

                    <p>Update department details.</p>

                </div>

                <div class="crud-header-actions">

                    <a href="departments.php" class="crud-back-btn">
                        <i class="fa-solid fa-arrow-left"></i>
                        Back to Departments
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
                        <i class="fa-solid fa-building"></i>
                    </div>

                    <div>
                        <h2>Department Details</h2>
                        <p>Update this department's information.</p>
                    </div>

                </div>

                <form method="POST" action="department_edit.php">
                    <?php csrfField(); ?>

                    <input type="hidden" name="update_department" value="1">

                    <div class="crud-form-grid">

                        <div class="crud-form-group">
                            <label>
                                <i class="fa-solid fa-building"></i>
                                Department Name
                            </label>
                            <input type="text" name="department_name" required value="<?= htmlspecialchars($department['department_name']); ?>">
                        </div>

                        <div class="crud-form-group">
                            <label>
                                <i class="fa-solid fa-circle-check"></i>
                                Status
                            </label>
                            <select name="status">
                                <option value="Active" <?= $department['status'] === 'Active' ? 'selected' : ''; ?>>Active</option>
                                <option value="Inactive" <?= $department['status'] === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>

                        <div class="crud-form-group crud-full">
                            <label>
                                <i class="fa-solid fa-align-left"></i>
                                Description
                            </label>
                            <textarea name="description" rows="4" style="resize: vertical;"><?= htmlspecialchars($department['description'] ?? ''); ?></textarea>
                        </div>

                    </div>

                    <div class="crud-actions">

                        <a href="departments.php" class="crud-btn crud-btn-secondary">
                            <i class="fa-solid fa-xmark"></i>
                            Cancel
                        </a>

                                                <button type="submit" id="deptEditSaveBtn" disabled class="crud-btn crud-btn-primary">
                            <i class="fa-solid fa-save"></i>
                            Update Department
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

    const form = document.querySelector('form[action="department_edit.php"]');
    const saveBtn = document.getElementById("deptEditSaveBtn");
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
            text: "This will update this department's record.",
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