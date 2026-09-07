<?php

session_start();

require_once "../auth/admin_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";
require_once "includes/hr_audit_log.php";

$page_title = "Add Position";

$departments = $conn->query("
    SELECT department_id, department_name
    FROM departments
    WHERE status = 'Active'
    ORDER BY department_name ASC
")->fetchAll(PDO::FETCH_ASSOC);

$alert = ["type" => "", "message" => ""];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    requireCSRFToken("position_add.php");

    $position_name = trim($_POST['position_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $department_id = $_POST['department_id'] ?? '';
    $status = $_POST['status'] ?? 'Active';

    if (empty($position_name) || empty($department_id)) {
        $alert["type"] = "error";
        $alert["message"] = "Please complete all required fields.";
    } else {
        // Check if position already exists
        $check = $conn->prepare("
            SELECT position_id
            FROM positions
            WHERE position_name = ? AND department_id = ?
        ");

        $check->execute([$position_name, $department_id]);

        if ($check->fetch()) {
            $alert["type"] = "error";
            $alert["message"] = "This position already exists in the selected department.";
        } else {
            try {
                $conn->beginTransaction();

                // Get department name for logging
                $deptStmt = $conn->prepare("SELECT department_name FROM departments WHERE department_id = ?");
                $deptStmt->execute([$department_id]);
                $dept = $deptStmt->fetch(PDO::FETCH_ASSOC);

                $stmt = $conn->prepare("
                    INSERT INTO positions
                    (position_name, description, department_id, status, created_at)
                    VALUES (?, ?, ?, ?, NOW())
                ");

                $stmt->execute([
                    $position_name,
                    !empty($description) ? $description : null,
                    $department_id,
                    $status
                ]);

                // ========== LOG THE POSITION CREATION ==========
                logPositionCreation($conn, $position_name, $dept['department_name'], $description);
                // =============================================

                $conn->commit();

                $alert["type"] = "success";
                $alert["message"] = "Position added successfully.";

            } catch (PDOException $e) {
                $conn->rollBack();
                error_log("Position creation error: " . $e->getMessage());
                $alert["type"] = "error";
                $alert["message"] = "Failed to create position.";
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
                            <i class="fa-solid fa-briefcase"></i>
                            Add Position
                        </h1>

                        <p>Create a new job position.</p>

                    </div>

                    <div class="crud-header-actions">

                        <a href="positions.php" class="crud-back-btn">
                            <i class="fa-solid fa-arrow-left"></i>
                            Back to Positions
                        </a>

                    </div>

                </div>

                <div class="crud-card">

                    <div class="crud-card-header">

                        <div class="crud-icon">
                            <i class="fa-solid fa-briefcase"></i>
                        </div>

                        <div>
                            <h2>Position Information</h2>
                            <p>Fill in position details.</p>
                        </div>

                    </div>

                     <form method="POST" action="position_add.php">
                        <?php csrfField(); ?>

                        <div class="crud-form-grid">

                            <div class="crud-form-group">
                                <label>
                                    <i class="fa-solid fa-briefcase"></i>
                                    Position Name
                                </label>
                                <input type="text" name="position_name" placeholder="Enter position name" required value="<?= htmlspecialchars($_POST['position_name'] ?? ''); ?>">
                            </div>

                            <div class="crud-form-group">
                                <label>
                                    <i class="fa-solid fa-building"></i>
                                    Department
                                </label>
                                <select name="department_id" required>
                                    <option value="">Select Department</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?= $dept['department_id']; ?>" <?= ($_POST['department_id'] ?? '') == $dept['department_id'] ? 'selected' : ''; ?>>
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
                                    <option value="Active" <?= ($_POST['status'] ?? 'Active') === 'Active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="Inactive" <?= ($_POST['status'] ?? '') === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>

                            <div class="crud-form-group crud-full">
                                <label>
                                    <i class="fa-solid fa-align-left"></i>
                                    Description
                                </label>
                                <textarea name="description" rows="5" placeholder="Enter position description"><?= htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                            </div>

                        </div>

                        <div class="crud-actions">

                            <a href="positions.php" class="crud-btn crud-btn-secondary">
                                <i class="fa-solid fa-xmark"></i>
                                Cancel
                            </a>

                            <button type="submit" class="crud-btn crud-btn-primary">
                                <i class="fa-solid fa-save"></i>
                                Save Position
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

        title: "<?= $alert['type'] == 'success' ? 'Position Added!' : 'Unable to Add Position'; ?>",

        text: "<?= htmlspecialchars($alert['message'], ENT_QUOTES, 'UTF-8'); ?>",

        confirmButtonColor: "#003DA5",
        confirmButtonText: "OK",
        allowOutsideClick: false,
        allowEscapeKey: false

    }).then((result) => {

        <?php if ($alert["type"] == "success"): ?>

        if (result.isConfirmed) {
            window.location.href = "positions.php";
        }

        <?php endif; ?>

    });

});

</script>

<?php endif; ?>

<script src="../assets/js/admin.js"></script>

</body>

</html>