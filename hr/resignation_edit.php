<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";

$page_title = "Edit Resignation Approval";


if (isset($_POST['resignation_id'])) {
    $_SESSION['selected_resignation'] = (int) $_POST['resignation_id'];
}

if (!isset($_SESSION['selected_resignation'])) {
    header("Location: resignations.php");
    exit;
}

$resignation_id = (int) $_SESSION['selected_resignation'];


$hrUsers = $conn->query("
    SELECT
        user_id,
        full_name
    FROM users
    WHERE role = 'hr'
    AND status = 'active'
    ORDER BY full_name ASC
")->fetchAll(PDO::FETCH_ASSOC);


$stmt = $conn->prepare("
    SELECT
        r.resignation_id,
        r.reason,
        r.resignation_date,
        r.last_working_day,
        r.status,
        r.approved_by,
        r.approved_date,
        r.remarks,
        r.created_at,

        e.employee_code,
        e.first_name,
        e.middle_name,
        e.last_name,

        d.department_name,
        p.position_name,

        approver.full_name AS approved_by_name

    FROM resignations r

    INNER JOIN employees e
        ON r.employee_id = e.employee_id

    LEFT JOIN departments d
        ON e.department_id = d.department_id

    LEFT JOIN positions p
        ON e.position_id = p.position_id

    LEFT JOIN users approver
        ON r.approved_by = approver.user_id

    WHERE r.resignation_id = ?
");

$stmt->execute([$resignation_id]);

$resignation = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$resignation) {
    unset($_SESSION['selected_resignation']);

    header("Location: resignations.php");
    exit;
}


$isLocked = (
    $resignation['status'] === "Approved" ||
    $resignation['status'] === "Rejected"
);


if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST['update_resignation'])
) {

    requireCSRFToken("resignation_edit.php");

    if ($isLocked) {
        $_SESSION['resignation_error'] =
            "This resignation request is already finalized and cannot be edited.";

        header("Location: resignation_edit.php");
        exit;
    }

    $approved_by = !empty($_POST['approved_by'])
        ? (int) $_POST['approved_by']
        : null;

    $approved_date = !empty($_POST['approved_date'])
        ? $_POST['approved_date']
        : null;

    $remarks = trim($_POST['remarks']);

    $update = $conn->prepare("
        UPDATE resignations
        SET
            approved_by = ?,
            approved_date = ?,
            remarks = ?
        WHERE resignation_id = ?
    ");

    $success = $update->execute([
        $approved_by,
        $approved_date,
        $remarks,
        $resignation_id
    ]);

    if ($success) {
        $_SESSION['resignation_success'] =
            "Resignation information updated successfully.";

        header("Location: resignations.php");
        exit;
    } else {
        $_SESSION['resignation_error'] =
            "Unable to update resignation request.";

        header("Location: resignation_edit.php");
        exit;
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

    <link rel="stylesheet" href="../assets/css/hr.css">

    <link rel="stylesheet" href="../assets/css/crud_hr.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>

    <div class="hr-wrapper">

        <?php require_once "includes/sidebar.php"; ?>

        <div class="hr-content-wrapper">

            <?php require_once "includes/header.php"; ?>

            <main class="hr-main">

                <section class="crud-page">

                    <div class="crud-header">

                        <div class="crud-title">

                            <h1>
                                <i class="fa-solid fa-person-walking-arrow-right"></i>
                                Edit Resignation Request
                            </h1>

                            <p>Update resignation approval information and remarks.</p>

                        </div>

                        <div class="crud-header-actions">

                            <a
                                href="resignations.php"
                                class="crud-back-btn"
                            >
                                <i class="fa-solid fa-arrow-left"></i>
                                Back to Resignations
                            </a>

                        </div>

                    </div>

                    <div class="crud-card">

                        <div class="crud-card-header">

                            <div class="crud-icon">
                                <i class="fa-solid fa-person-circle-exclamation"></i>
                            </div>

                            <div>

                                <h2>Resignation Information</h2>

                                <p>Employee resignation details</p>

                            </div>

                        </div>

                        <div class="crud-section-title">

                            <h3>
                                <i class="fa-solid fa-user"></i>
                                Employee Information
                            </h3>

                        </div>

                        <div class="crud-info">

                            <div class="crud-info-box">

                                <span>Employee</span>

                                <strong>

                                    <?= htmlspecialchars(
                                        trim(
                                            $resignation['first_name'] . " " .
                                            (
                                                !empty($resignation['middle_name'])
                                                    ? $resignation['middle_name'] . " "
                                                    : ""
                                            ) .
                                            $resignation['last_name']
                                        )
                                    ); ?>

                                </strong>

                            </div>

                            <div class="crud-info-box">

                                <span>Employee Code</span>

                                <strong>

                                    <?= htmlspecialchars($resignation['employee_code']); ?>

                                </strong>

                            </div>

                            <div class="crud-info-box">

                                <span>Department</span>

                                <strong>

                                    <?= htmlspecialchars($resignation['department_name'] ?? "N/A"); ?>

                                </strong>

                            </div>

                            <div class="crud-info-box">

                                <span>Position</span>

                                <strong>

                                    <?= htmlspecialchars($resignation['position_name'] ?? "N/A"); ?>

                                </strong>

                            </div>

                            <div class="crud-info-box">

                                <span>Resignation Date</span>

                                <strong>

                                    <?= !empty($resignation['resignation_date'])
                                        ? date("F d, Y", strtotime($resignation['resignation_date']))
                                        : "N/A"; ?>

                                </strong>

                            </div>

                            <div class="crud-info-box">

                                <span>Last Working Day</span>

                                <strong>

                                    <?= !empty($resignation['last_working_day'])
                                        ? date("F d, Y", strtotime($resignation['last_working_day']))
                                        : "N/A"; ?>

                                </strong>

                            </div>

                            <div class="crud-info-box">

                                <span>Current Status</span>

                                <strong>

                                    <span class="hr-res-status <?= strtolower($resignation['status']); ?>">

                                        <?php if ($resignation['status'] == "Pending"): ?>

                                            <i class="fa-solid fa-clock"></i>

                                        <?php elseif ($resignation['status'] == "Approved"): ?>

                                            <i class="fa-solid fa-circle-check"></i>

                                        <?php else: ?>

                                            <i class="fa-solid fa-circle-xmark"></i>

                                        <?php endif; ?>

                                        <?= htmlspecialchars($resignation['status']); ?>

                                    </span>

                                </strong>

                            </div>

                            <div
                                class="crud-info-box"
                                style="grid-column:1/-1;"
                            >

                                <span>Reason</span>

                                <strong>

                                    <?= !empty($resignation['reason'])
                                        ? nl2br(htmlspecialchars($resignation['reason']))
                                        : "No reason provided"; ?>

                                </strong>

                            </div>

                        </div>

                        <div class="crud-section-title">

                            <h3>
                                <i class="fa-solid fa-user-check"></i>
                                Approval Information
                            </h3>

                        </div>

                                               <form method="POST" id="resignationEditForm" novalidate>

                            <?php csrfField(); ?>

                            <input
                                type="hidden"
                                name="update_resignation"
                                value="1"
                            >

                            <div class="crud-form-grid">

                                <div class="crud-form-group">

                                    <label>
                                        <i class="fa-solid fa-user-check"></i>
                                        Approved By
                                    </label>

                                    <?php if ($isLocked): ?>

                                        <input
                                            type="hidden"
                                            name="approved_by"
                                            value="<?= $resignation['approved_by']; ?>"
                                        >

                                    <?php endif; ?>

                                    <select
                                        name="approved_by"
                                        <?= $isLocked ? "disabled" : ""; ?>
                                    >

                                        <option value="">
                                            Not yet approved
                                        </option>

                                        <?php foreach ($hrUsers as $hr): ?>

                                            <option
                                                value="<?= $hr['user_id']; ?>"
                                                <?= ($resignation['approved_by'] == $hr['user_id']) ? "selected" : ""; ?>
                                            >

                                                <?= htmlspecialchars($hr['full_name']); ?>

                                            </option>

                                        <?php endforeach; ?>

                                    </select>

                                    <?php if ($isLocked): ?>

                                        <small class="crud-note">

                                            <i class="fa-solid fa-lock"></i>

                                            Approval information is locked because this request is already <?= strtolower($resignation['status']); ?>.

                                        </small>

                                    <?php endif; ?>

                                </div>

                                <div class="crud-form-group">

                                    <label>
                                        <i class="fa-solid fa-calendar-check"></i>
                                        Approval Date
                                    </label>

                                    <input
                                        type="datetime-local"
                                        name="approved_date"
                                        value="<?= !empty($resignation['approved_date'])
                                            ? date("Y-m-d\TH:i", strtotime($resignation['approved_date']))
                                            : ""; ?>"
                                        <?= $isLocked ? "disabled" : ""; ?>
                                    >

                                </div>

                            </div>

                            <div class="crud-form-group">

                                <label>
                                    <i class="fa-solid fa-comment"></i>
                                    Remarks
                                </label>

                                <textarea
                                    name="remarks"
                                    rows="5"
                                    placeholder="Enter remarks or notes..."
                                    <?= $isLocked ? "readonly" : ""; ?>
                                ><?= htmlspecialchars($resignation['remarks'] ?? ""); ?></textarea>

                            </div>

                            <div class="crud-actions">

                                <?php if (!$isLocked): ?>

                                    <button
                                        type="submit"
                                        id="resignationEditSaveBtn"
                                        disabled
                                        class="crud-btn crud-btn-primary"
                                    >

                                        <i class="fa-solid fa-floppy-disk"></i>
                                        Update Resignation

                                    </button>

                                <?php else: ?>

                                    <button
                                        type="button"
                                        class="crud-btn crud-btn-disabled"
                                        disabled
                                    >

                                        <i class="fa-solid fa-lock"></i>
                                        Already <?= htmlspecialchars($resignation['status']); ?>

                                    </button>

                                <?php endif; ?>

                            </div>

                        </form>

                    </div>


                    <?php if (!empty($_SESSION['resignation_success'])): ?>

                        <script>

                            document.addEventListener("DOMContentLoaded", function () {

                                Swal.fire({

                                    icon: "success",

                                    title: "Success!",

                                    text: <?= json_encode($_SESSION['resignation_success']); ?>,

                                    confirmButtonColor: "#003DA5",

                                    confirmButtonText: "OK"

                                });

                            });

                        </script>

                        <?php unset($_SESSION['resignation_success']); ?>

                    <?php endif; ?>

                    <?php if (!empty($_SESSION['resignation_error'])): ?>

                        <script>

                            document.addEventListener("DOMContentLoaded", function () {

                                Swal.fire({

                                    icon: "error",

                                    title: "Unable to Continue",

                                    text: <?= json_encode($_SESSION['resignation_error']); ?>,

                                    confirmButtonColor: "#003DA5",

                                    confirmButtonText: "OK"

                                });

                            });

                        </script>

                        <?php unset($_SESSION['resignation_error']); ?>

                    <?php endif; ?>

                </section>

            </main>

        </div>

    </div>

    
<script>
(function () {

    const form = document.getElementById("resignationEditForm");
    const saveBtn = document.getElementById("resignationEditSaveBtn");

    if (!form || !saveBtn) {
        return;
    }

    const initialState = new URLSearchParams(new FormData(form)).toString();

    function checkForChanges() {
        const currentState = new URLSearchParams(new FormData(form)).toString();
        saveBtn.disabled = (currentState === initialState);
    }

    form.addEventListener("input", checkForChanges);
    form.addEventListener("change", checkForChanges);

})();

document.getElementById("resignationEditForm").addEventListener("submit", function (e) {

    e.preventDefault();

    const form = this;

    Swal.fire({
        icon: "question",
        title: "Save changes?",
        text: "This will update this resignation's record.",
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
</script>

<script src="../assets/js/hr.js"></script>


</body>

</html>