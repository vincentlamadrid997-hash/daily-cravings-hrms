<?php

session_start();

require_once "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT must_change_password
    FROM users
    WHERE user_id = ?
");
$stmt->execute([$user_id]);
$flag = $stmt->fetchColumn();

// If they don't actually need to change their password, don't let them
// land here pointlessly — send them to their real dashboard.
if (empty($flag)) {

    switch ($_SESSION['role']) {
        case "admin":
            header("Location: ../admin/dashboard.php");
            break;
        case "hr":
            header("Location: ../hr/dashboard.php");
            break;
        case "employee":
            header("Location: ../employee/dashboard.php");
            break;
        default:
            header("Location: ../login.php");
    }
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $new_password = $_POST['new_password'] ?? "";
    $confirm_password = $_POST['confirm_password'] ?? "";

    if (empty($new_password) || empty($confirm_password)) {

        $error = "Please fill in both fields.";

    } elseif (strlen($new_password) < 8) {

        $error = "Password must be at least 8 characters.";

    } elseif ($new_password !== $confirm_password) {

        $error = "Passwords do not match.";

    } else {

        $hashed = password_hash($new_password, PASSWORD_DEFAULT);

        $update = $conn->prepare("
            UPDATE users
            SET password = ?, must_change_password = 0
            WHERE user_id = ?
        ");

        $update->execute([$hashed, $user_id]);

        switch ($_SESSION['role']) {
            case "admin":
                header("Location: ../admin/dashboard.php");
                break;
            case "hr":
                header("Location: ../hr/dashboard.php");
                break;
            case "employee":
                header("Location: ../employee/dashboard.php");
                break;
            default:
                header("Location: ../login.php");
        }
        exit();

    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Set Your Password | Daily Cravings Foods Inc.</title>

    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/login.css">

    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        rel="stylesheet">

</head>

<body>

    <div class="background">
        <img src="../assets/images/logo_2.png" class="bg-logo">
    </div>

    <div class="login-wrapper">

        <div class="left-panel">

            <img src="../assets/images/logo_2.png" class="company-logo">

            <h1>Daily Cravings Foods Inc.</h1>
            <h3>Human Resource Management System</h3>

            <p>
                For your security, please set your own password
                before continuing.
            </p>

        </div>

        <div class="login-card">

            <div class="gold-line"></div>

            <h2>Set Your Password</h2>
            <p>This is required before you can continue.</p>

            <?php if (!empty($error)): ?>

                <div class="alert-error">

                    <div class="alert-icon">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>

                    <div class="alert-content">
                        <?= htmlspecialchars($error); ?>
                    </div>

                </div>

            <?php endif; ?>

            <form method="POST">

                <div class="input-group">

                    <label>New Password</label>

                    <div class="input-box">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="new_password" placeholder="Enter new password" required>
                    </div>

                </div>

                <div class="input-group">

                    <label>Confirm New Password</label>

                    <div class="input-box">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="confirm_password" placeholder="Confirm new password" required>
                    </div>

                </div>

                <button type="submit" class="login-btn">
                    Set Password &amp; Continue
                </button>

            </form>

        </div>

    </div>

</body>
</html>