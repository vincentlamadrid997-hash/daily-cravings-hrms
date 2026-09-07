<?php

session_start();

require_once "../config/db.php";

$error = "";
$success = "";

if (!isset($_GET['token']) || empty($_GET['token'])) {
    die("Invalid reset request.");
}

$token = $_GET['token'];

$stmt = $conn->prepare("
    SELECT
        email,
        expires_at
    FROM password_resets
    WHERE token = ?
    LIMIT 1
");

$stmt->execute([$token]);

$reset = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reset) {
    die("Invalid or expired reset link.");
}

if (strtotime($reset['expires_at']) < time()) {
    die("Reset link has expired.");
}

$email = $reset['email'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($password) || empty($confirm_password)) {

        $error = "Please fill in all fields.";

    } elseif ($password !== $confirm_password) {

        $error = "Passwords do not match.";

    } elseif (strlen($password) < 8) {

        $error = "Password must be at least 8 characters.";

    } else {

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $update = $conn->prepare("
            UPDATE users
            SET password = ?
            WHERE email = ?
        ");

        $update->execute([
            $hashed_password,
            $email
        ]);

        $delete = $conn->prepare("
            DELETE FROM password_resets
            WHERE token = ?
        ");

        $delete->execute([$token]);

        $_SESSION['success'] = "Password successfully updated. You can now login using your new password.";

        header("Location: ../login.php");
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Reset Password | Daily Cravings Foods Inc.</title>

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

            <p>Create a new secure password for your account.</p>

        </div>

        <div class="login-card forgot-card">

            <div class="gold-line"></div>

            <h2>Reset Password</h2>

            <p>Create your new password.</p>

            <?php if (!empty($error)): ?>

                <div class="alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <?= htmlspecialchars($error); ?>
                </div>

            <?php endif; ?>

            <?php if (!empty($success)): ?>

                <div class="alert-success" id="successMessage">

                    <i class="fa-solid fa-circle-check"></i>

                    <?= htmlspecialchars($success); ?>

                    <br><br>

                    <a href="../login.php" class="back-login-btn">
                        Login Now
                    </a>

                </div>

            <?php endif; ?>

            <?php if (empty($success)): ?>

                <form method="POST">

                    <div class="input-group">

                        <label>New Password</label>

                        <div class="input-box">

                            <i class="fa-solid fa-lock"></i>

                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Enter your new password"
                                required>

                            <span id="togglePassword">
                                <i class="fa-solid fa-eye"></i>
                            </span>

                        </div>

                        <small
                            id="strength"
                            class="password-strength">
                        </small>

                    </div>

                    <div class="input-group">

                        <label>Confirm Password</label>

                        <div class="input-box">

                            <i class="fa-solid fa-lock"></i>

                            <input
                                type="password"
                                id="confirm_password"
                                name="confirm_password"
                                placeholder="Confirm your password"
                                required>

                            <span id="toggleConfirmPassword">
                                <i class="fa-solid fa-eye"></i>
                            </span>

                        </div>

                        <small
                            id="confirmMessage"
                            class="password-match">
                        </small>

                    </div>

                    <button
                        type="submit"
                        class="login-btn">

                        Reset Password

                    </button>

                </form>

            <?php endif; ?>

            <div class="bottom-links">

                <a
                    href="../login.php"
                    class="back-login-btn">

                    <i class="fa-solid fa-arrow-left"></i>

                    Back to Login

                </a>

            </div>

        </div>

    </div>

    <script src="../assets/js/login.js"></script>

</body>

</html>