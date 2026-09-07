<?php

session_start();

require_once "../auth/admin_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";

$page_title = "My Profile";

$user_id = $_SESSION['user_id'];

$success = $_SESSION['profile_success'] ?? "";
$error   = $_SESSION['profile_error'] ?? "";

unset($_SESSION['profile_success']);
unset($_SESSION['profile_error']);

$stmt = $conn->prepare("
    SELECT user_id, full_name, email, gender, profile_picture, role, created_at
    FROM users
    WHERE user_id = ?
");
$stmt->execute([$user_id]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    session_destroy();
    header("Location: /daily_cravings_hrms/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_profile'])) {

    requireCSRFToken("profile.php", "profile_error");

    $full_name = trim($_POST['full_name'] ?? "");
    $email = trim($_POST['email'] ?? "");
    $gender = $_POST['gender'] ?? "";

    if (empty($full_name) || empty($email) || empty($gender)) {
        $_SESSION['profile_error'] = "Please complete all required fields.";
        header("Location: profile.php");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['profile_error'] = "Invalid email address.";
        header("Location: profile.php");
        exit;
    }

    $check = $conn->prepare("
        SELECT user_id FROM users WHERE email = ? AND user_id != ?
    ");
    $check->execute([$email, $user_id]);

    if ($check->fetch()) {
        $_SESSION['profile_error'] = "That email address is already in use.";
        header("Location: profile.php");
        exit;
    }

    $profile_picture = $admin['profile_picture'];

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {

        $file = $_FILES['profile_picture'];
        $allowedExtensions = ["jpg", "jpeg", "png", "webp"];
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($fileExtension, $allowedExtensions)) {
            $_SESSION['profile_error'] = "Invalid profile picture format.";
            header("Location: profile.php");
            exit;
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            $_SESSION['profile_error'] = "Profile picture must not exceed 5MB.";
            header("Location: profile.php");
            exit;
        }

        $uploadDirectory = "../assets/images/profiles/";

        if (!is_dir($uploadDirectory)) {
            mkdir($uploadDirectory, 0777, true);
        }

        $newFileName = "admin_" . $user_id . "_" . time() . "." . $fileExtension;
        $uploadPath = $uploadDirectory . $newFileName;

        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {

            $oldPicture = $admin['profile_picture'];

            if (!empty($oldPicture) && file_exists($uploadDirectory . $oldPicture)) {
                unlink($uploadDirectory . $oldPicture);
            }

            $profile_picture = $newFileName;

        } else {
            $_SESSION['profile_error'] = "Failed to upload profile picture.";
            header("Location: profile.php");
            exit;
        }
    }

    try {

        $update = $conn->prepare("
            UPDATE users
            SET full_name = ?, email = ?, gender = ?, profile_picture = ?
            WHERE user_id = ?
        ");

        $update->execute([
            $full_name,
            $email,
            $gender,
            $profile_picture,
            $user_id
        ]);

        $_SESSION['full_name'] = $full_name;
        $_SESSION['email'] = $email;
        $_SESSION['gender'] = $gender;
        $_SESSION['profile_picture'] = $profile_picture;

        $_SESSION['profile_success'] = "Profile updated successfully.";
        header("Location: profile.php");
        exit;

    } catch (PDOException $e) {
        error_log("Admin profile update error: " . $e->getMessage());
        $_SESSION['profile_error'] = "Failed to update profile.";
        header("Location: profile.php");
        exit;
    }
}

$avatarPath = "";
if (!empty($admin['profile_picture']) && file_exists("../assets/images/profiles/" . $admin['profile_picture'])) {
    $avatarPath = "../assets/images/profiles/" . $admin['profile_picture'];
} else {
    $avatarPath = $admin['gender'] === 'Male'
        ? "../assets/images/avatar/admin_boy.png"
        : "../assets/images/avatar/admin_girl.png";
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
                        <i class="fa-solid fa-user-shield"></i>
                        My Profile
                    </h1>

                    <p>View and update your account information.</p>

                </div>

                <div class="crud-header-actions">

                    <a href="profile_change_password.php" class="crud-back-btn">
                        <i class="fa-solid fa-key"></i>
                        Change Password
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
                        <i class="fa-solid fa-id-badge"></i>
                    </div>

                    <div>
                        <h2>Account Information</h2>
                        <p>Role: <?= htmlspecialchars(ucfirst($admin['role'])); ?> &middot; Member since <?= date("M Y", strtotime($admin['created_at'])); ?></p>
                    </div>

                </div>

                <form method="POST" action="profile.php" enctype="multipart/form-data" id="profileForm" novalidate>
                    <?php csrfField(); ?>

                    <input type="hidden" name="update_profile" value="1">

                    <div style="text-align:center; padding: 20px 24px 0;">

                        <img
                            src="<?= htmlspecialchars($avatarPath); ?>"
                            alt="Profile"
                            style="width:110px; height:110px; border-radius:50%; object-fit:cover; border:4px solid #FFC107;"
                        >

                    </div>

                    <div class="crud-form-grid">

                                                <div class="crud-form-group">
                            <label>
                                <i class="fa-solid fa-user"></i>
                                Full Name
                            </label>
                            <input type="text" name="full_name" class="profile-tracked" data-initial="<?= htmlspecialchars($admin['full_name']); ?>" required value="<?= htmlspecialchars($admin['full_name']); ?>">
                        </div>

                        <div class="crud-form-group">
                            <label>
                                <i class="fa-solid fa-envelope"></i>
                                Email Address
                            </label>
                            <input type="email" name="email" class="profile-tracked" data-initial="<?= htmlspecialchars($admin['email']); ?>" required value="<?= htmlspecialchars($admin['email']); ?>">
                        </div>

                        <div class="crud-form-group">
                            <label>
                                <i class="fa-solid fa-venus-mars"></i>
                                Gender
                            </label>
                            <select name="gender" class="profile-tracked" data-initial="<?= htmlspecialchars($admin['gender']); ?>" required>
                                <option value="Male" <?= $admin['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?= $admin['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                            </select>
                        </div>

                        <div class="crud-form-group">
                            <label>
                                <i class="fa-solid fa-image"></i>
                                Profile Picture
                            </label>
                            <input type="file" name="profile_picture" id="profilePictureInput" accept=".jpg,.jpeg,.png,.webp">
                        </div>

                    </div>

                     <div class="crud-actions">

                        <button type="submit" class="crud-btn crud-btn-primary" id="profileSaveBtn" disabled>
                            <i class="fa-solid fa-save"></i>
                            Save Changes
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

    const saveBtn = document.getElementById("profileSaveBtn");
    const trackedFields = document.querySelectorAll(".profile-tracked");
    const pictureInput = document.getElementById("profilePictureInput");

    function checkForChanges() {

        let changed = false;

        trackedFields.forEach(function (field) {
            if (field.value !== field.getAttribute("data-initial")) {
                changed = true;
            }
        });

        if (pictureInput && pictureInput.files.length > 0) {
            changed = true;
        }

        saveBtn.disabled = !changed;

    }

    trackedFields.forEach(function (field) {
        field.addEventListener("input", checkForChanges);
        field.addEventListener("change", checkForChanges);
    });

    if (pictureInput) {
        pictureInput.addEventListener("change", checkForChanges);
    }

})();

document.getElementById("profileForm").addEventListener("submit", function (e) {

    e.preventDefault();

    const form = this;

    const fullName = form.querySelector('[name="full_name"]').value.trim();
    const email = form.querySelector('[name="email"]').value.trim();

    if (!fullName || !email) {

        Swal.fire({
            icon: "warning",
            title: "Missing Information",
            text: "Full Name and Email Address cannot be left empty.",
            confirmButtonColor: "#F9A825"
        });

        return;
    }

    Swal.fire({
        icon: "question",
        title: "Save changes?",
        text: "This will update your profile information.",
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

<script src="../assets/js/admin.js"></script>

</body>

</html>