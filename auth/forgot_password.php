<?php

session_start();

require_once "../config/db.php";
require_once "../config/mail.php";


$message = "";
$error = "";
$email_error = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST['email']);

    if (empty($email)) {

        $error = "Please enter your email address.";

    }


    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {


        $email_error = "Please enter a valid email address.";

    }


    else {


        $stmt = $conn->prepare("
            SELECT user_id
            FROM users
            WHERE email = ?
            LIMIT 1
        ");


        $stmt->execute([
            $email
        ]);


        $user = $stmt->fetch(PDO::FETCH_ASSOC);



        if ($user) {


            $delete = $conn->prepare("
                DELETE FROM password_resets
                WHERE email = ?
            ");


            $delete->execute([
                $email
            ]);



            $token = bin2hex(
                random_bytes(32)
            );


            $expires_at = date(
                "Y-m-d H:i:s",
                strtotime("+15 minutes")
            );


            $insert = $conn->prepare("

                INSERT INTO password_resets
                (
                    email,
                    token,
                    expires_at
                )

                VALUES
                (
                    ?, ?, ?
                )

            ");



            $insert->execute([
                $email,
                $token,
                $expires_at
            ]);



            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

            $reset_link =
                $protocol . $host . "/daily_cravings_hrms/auth/reset_password.php?token="
                . $token;


            if (sendResetEmail($email, $reset_link)) {


                $message =
                    "Password reset link has been sent to your email.";


            }

            else {


                $error =
                    "Unable to send email. Please try again later.";


            }



        }

        else {


            $message =
                "If your email exists, a reset link has been sent.";


        }


    }


}


?>



<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Forgot Password | Daily Cravings Foods Inc.</title>

    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/login.css">

    <link 
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        rel="stylesheet">

</head>

<body>

<div class="background">

    <img
        src="../assets/images/logo_2.png"
        class="bg-logo">

</div>

<div class="login-wrapper">

    <div class="left-panel">

        <img
            src="../assets/images/logo_2.png"
            class="company-logo">

        <h1>Daily Cravings Foods Inc.</h1>

        <h3>Human Resource Management System </h3>

        <p>Securely recover your employee account password. </p>

    </div>

    <div class="login-card forgot-card">

        <div class="gold-line"></div>

        <h2>Forgot Password</h2>

        <p>Enter your email to receive a reset link.</p>


        <?php if (!empty($error)): ?>

            <div class="alert-error">

                <i class="fa-solid fa-triangle-exclamation"></i>

                <?= htmlspecialchars($error); ?>

            </div>

        <?php endif; ?>

        <?php if (!empty($message)): ?>

            <div class="alert-success" id="successMessage">

                <i class="fa-solid fa-circle-check"></i>

                <?= htmlspecialchars($message); ?>

            </div>

        <?php endif; ?>

        <form method="POST">

            <div class="input-group">

                <label>
                    Email Address
                </label>

                <div class="input-box" id="emailBox">

                    <i class="fa-solid fa-envelope"></i>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter your email"
                        autocomplete="off"
                        required>

                </div>

                <small
                    id="emailMessage"
                    class="email-validation">

                    <?php

                    if (!empty($email_error)) {

                        echo htmlspecialchars($email_error);

                    }

                    ?>

                </small>

            </div>

            <button
                type="submit"
                class="login-btn">

                Send Reset Link

            </button>

        </form>

        <a
            href="../login.php"
            class="back-login-btn">

            <i class="fa-solid fa-arrow-left"></i>
            Back to Login

        </a>

    </div>

</div>

</body>

</html>