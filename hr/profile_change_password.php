<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";

$page_title = "Change Password";

$user_id = $_SESSION['user_id'];

$success = $_SESSION['password_success'] ?? "";
$error   = $_SESSION['password_error'] ?? "";

unset($_SESSION['password_success']);
unset($_SESSION['password_error']);

$stmt = $conn->prepare("
    SELECT user_id, password FROM users WHERE user_id = ?
");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header("Location: /daily_cravings_hrms/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    requireCSRFToken("profile_change_password.php", "password_error");

    $current_password = $_POST['current_password'] ?? "";
    $new_password = $_POST['new_password'] ?? "";
    $confirm_password = $_POST['confirm_password'] ?? "";

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "All password fields are required.";
    } elseif (!password_verify($current_password, $user['password'])) {
        $error = "Current password is incorrect.";
    } elseif ($new_password === $current_password) {
        $error = "New password cannot be the same as your current password.";
    } elseif (strlen($new_password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New password and confirmation password do not match.";
    } else {

        $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        try {

            $update = $conn->prepare("
                UPDATE users SET password = ? WHERE user_id = ?
            ");

            $update->execute([$new_hashed_password, $user_id]);

                        $_SESSION['password_success'] = "Password changed successfully.";
            header("Location: profile_change_password.php");
            exit;

        } catch (Exception $e) {
            error_log("HR password change error: " . $e->getMessage());
            $error = "Unable to change password. Please try again.";
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
                        <i class="fa-solid fa-key"></i>
                        Change Password
                    </h1>

                    <p>Update your account password securely.</p>

                </div>

                <div class="crud-header-actions">

                    <a href="profile.php" class="crud-back-btn">
                        <i class="fa-solid fa-arrow-left"></i>
                        Back to Profile
                    </a>

                </div>

            </div>

            <?php if ($error): ?>
                <div class="crud-alert error">
                    <i class="fa-solid fa-exclamation-circle"></i>
                    <?= htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="crud-card">

                <div class="crud-card-header">

                    <div class="crud-icon">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>

                    <div>
                        <h2>Account Security</h2>
                        <p>Change your password to keep your account protected.</p>
                    </div>

                </div>

                                <form method="POST" action="profile_change_password.php" id="passwordForm" novalidate>

                    <?php csrfField(); ?>

                    <div class="crud-form-grid">

                                                <div class="crud-form-group">
                            <label>
                                <i class="fa-solid fa-lock"></i>
                                Current Password
                            </label>
                            <input type="password" name="current_password" class="password-tracked" required>
                        </div>

                        <div class="crud-form-group">
                            <label>
                                <i class="fa-solid fa-key"></i>
                                New Password
                            </label>
                            <input type="password" name="new_password" class="password-tracked" minlength="8" required>
                        </div>

                        <div class="crud-form-group">
                            <label>
                                <i class="fa-solid fa-check"></i>
                                Confirm New Password
                            </label>
                            <input type="password" name="confirm_password" class="password-tracked" minlength="8" required>
                        </div>

                    </div>

                                        <div class="crud-actions">

                        <button type="submit" class="crud-btn crud-btn-primary" id="passwordSaveBtn" disabled>
                            <i class="fa-solid fa-save"></i>
                            Update Password
                        </button>

                    </div>

                </form>

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
        title: "Success",
        text: <?= json_encode($success); ?>,
        confirmButtonColor: "#003DA5"
    });
});
</script>
<?php endif; ?>

<script>

(function () {

    const saveBtn = document.getElementById("passwordSaveBtn");
    const trackedFields = document.querySelectorAll(".password-tracked");

    function checkForChanges() {

        let allFilled = true;

        trackedFields.forEach(function (field) {
            if (field.value.trim() === "") {
                allFilled = false;
            }
        });

        saveBtn.disabled = !allFilled;

    }

    trackedFields.forEach(function (field) {
        field.addEventListener("input", checkForChanges);
    });

})();

document.getElementById("passwordForm").addEventListener("submit", function (e) {

    e.preventDefault();

    const form = this;

    const current = form.querySelector('[name="current_password"]').value;
    const newPass = form.querySelector('[name="new_password"]').value;
    const confirm = form.querySelector('[name="confirm_password"]').value;

    if (!current || !newPass || !confirm) {

        Swal.fire({
            icon: "warning",
            title: "Missing Information",
            text: "All password fields are required.",
            confirmButtonColor: "#F9A825"
        });

        return;
    }

    if (newPass.length < 8) {

        Swal.fire({
            icon: "warning",
            title: "Password Too Short",
            text: "New password must be at least 8 characters long.",
            confirmButtonColor: "#F9A825"
        });

        return;
    }

    if (newPass !== confirm) {

        Swal.fire({
            icon: "warning",
            title: "Passwords Don't Match",
            text: "New password and confirmation password do not match.",
            confirmButtonColor: "#F9A825"
        });

        return;
    }

    Swal.fire({
        icon: "question",
        title: "Update your password?",
        text: "You'll need to use your new password the next time you log in.",
        showCancelButton: true,
        confirmButtonText: "Yes, Update",
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