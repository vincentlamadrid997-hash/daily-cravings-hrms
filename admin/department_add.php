<?php

session_start();

require_once "../auth/admin_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";
require_once "includes/hr_audit_log.php";

$page_title = "Add Department";

$alert = ["type" => "", "message" => ""];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    requireCSRFToken("department_add.php");

    $department_name = trim($_POST['department_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'Active';

    if (empty($department_name)) {
        $alert["type"] = "error";
        $alert["message"] = "Department name is required.";
    } else {
        // Check if department already exists
        $check = $conn->prepare("
            SELECT department_id
            FROM departments
            WHERE department_name = ?
        ");

        $check->execute([$department_name]);

        if ($check->fetch()) {
            $alert["type"] = "error";
            $alert["message"] = "Department name already exists.";
        } else {
            try {
                $conn->beginTransaction();

                $stmt = $conn->prepare("
                    INSERT INTO departments
                    (department_name, description, status, created_at)
                    VALUES (?, ?, ?, NOW())
                ");

                $stmt->execute([
                    $department_name,
                    !empty($description) ? $description : null,
                    $status
                ]);

                // ========== LOG THE DEPARTMENT CREATION ==========
                logDepartmentCreation($conn, $department_name, $description);
                // ================================================

                $conn->commit();

                $alert["type"] = "success";
                $alert["message"] = "Department added successfully.";

            } catch (PDOException $e) {
                $conn->rollBack();
                error_log("Department creation error: " . $e->getMessage());
                $alert["type"] = "error";
                $alert["message"] = "Failed to create department.";
            }
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
                            <i class="fa-solid fa-building"></i>
                            Add Department
                        </h1>

                        <p>Create new company department.</p>

                    </div>

                    <div class="crud-header-actions">

                        <a href="departments.php" class="crud-back-btn">
                            <i class="fa-solid fa-arrow-left"></i>
                            Back to Departments
                        </a>

                    </div>

                </div>

                <div class="crud-card">

                    <div class="crud-card-header">

                        <div class="crud-icon">
                            <i class="fa-solid fa-building"></i>
                        </div>

                        <div>
                            <h2>Department Information</h2>
                            <p>Fill in department details.</p>
                        </div>

                    </div>

                    <form method="POST" action="department_add.php">
                        <?php csrfField(); ?>

                        <div class="crud-form-grid">

                            <div class="crud-form-group">
                                <label>
                                    <i class="fa-solid fa-building"></i>
                                    Department Name
                                </label>
                                <input type="text" name="department_name" placeholder="Enter department name" required value="<?= htmlspecialchars($_POST['department_name'] ?? ''); ?>">
                            </div>

                            <div class="crud-form-group">
                                <label>
                                    <i class="fa-solid fa-circle-check"></i>
                                    Status
                                </label>
                                <select name="status">
                                    <option value="Active" <?= ($_POST['status'] ?? 'Active') === 'Active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="Inactive" <?= ($_POST['status'] ?? '') === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>

                            <div class="crud-form-group crud-full">
                                <label>
                                    <i class="fa-solid fa-align-left"></i>
                                    Description
                                </label>
                                <textarea name="description" rows="5" placeholder="Enter department description"><?= htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                            </div>

                        </div>

                        <div class="crud-actions">

                            <a href="departments.php" class="crud-btn crud-btn-secondary">
                                <i class="fa-solid fa-xmark"></i>
                                Cancel
                            </a>

                            <button type="submit" class="crud-btn crud-btn-primary">
                                <i class="fa-solid fa-save"></i>
                                Save Department
                            </button>

                        </div>

                    </form>

                </div>

            </section>

        </main>

    </div>

</div>

<?php if (!empty($alert["message"])): ?>

<script>

document.addEventListener("DOMContentLoaded", function () {

    Swal.fire({

        icon: "<?= $alert['type']; ?>",

        title: "<?= $alert['type'] == 'success' ? 'Department Added!' : 'Unable to Add Department'; ?>",

        text: "<?= htmlspecialchars($alert['message'], ENT_QUOTES, 'UTF-8'); ?>",

        confirmButtonColor: "#003DA5",
        confirmButtonText: "OK",
        allowOutsideClick: false,
        allowEscapeKey: false

    }).then((result) => {

        <?php if ($alert["type"] == "success"): ?>

        if (result.isConfirmed) {
            window.location.href = "departments.php";
        }

        <?php endif; ?>

    });

});

</script>

<?php endif; ?>

<script src="../assets/js/admin.js"></script>

</body>

</html>