<?php

session_start();


if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {

    switch ($_SESSION['role']) {

        case "admin":
            header("Location: admin/dashboard.php");
            exit();

        case "hr":
            header("Location: hr/dashboard.php");
            exit();

        case "employee":
            header("Location: employee/dashboard.php");
            exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Daily Cravings Foods Inc. | HRMS</title>

    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/login.css">

    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        rel="stylesheet">

</head>

<body>

    <div class="background">
        <img
            src="assets/images/logo_2.png"
            class="bg-logo">
    </div>

    <div class="login-wrapper">


        <div class="left-panel">

            <img
                src="assets/images/logo_2.png"
                class="company-logo">

            <h1>Daily Cravings Foods Inc.</h1>

            <h3>Human Resource Management System</h3>

            <p>
                Welcome to the Employee Management Portal.
                Securely manage employees, payroll,
                attendance, recruitment, and reports.
            </p>

        </div>



        <div class="login-card">

            <div class="gold-line"></div>

            <h2>Welcome!</h2>

            <p>Please login to continue.</p>


            <?php if (isset($_SESSION['error'])): ?>

                <div class="alert-error">

                    <div class="alert-icon">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>

                    <div class="alert-content">
                        <?= nl2br(htmlspecialchars($_SESSION['error'])); ?>
                    </div>

                </div>

                <?php unset($_SESSION['error']); ?>

            <?php endif; ?>


            <?php if (isset($_SESSION['success'])): ?>

                <div class="alert-success">

                    <div class="alert-icon">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>

                    <div class="alert-content">
                        <?= nl2br(htmlspecialchars($_SESSION['success'])); ?>
                    </div>

                </div>

                <?php unset($_SESSION['success']); ?>

            <?php endif; ?>

                                    <form
                action="auth/login_process.php"
                method="POST">

                <?php
                    require_once "config/csrf.php";
                    csrfField();
                ?>


                <div class="input-group">

                    <label>Email Address</label>

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
                        class="validation-message">
                    </small>

                </div>


                <div class="input-group">

                    <label>Password</label>

                    <div class="input-box">

                        <i class="fa-solid fa-lock"></i>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter your password"
                            required>

                        <span id="togglePassword">
                            <i class="fa-solid fa-eye"></i>
                        </span>

                    </div>

                </div>


                <div class="options">

                    <label>
                        <input
                            type="checkbox"
                            name="remember">

                        Remember Me
                    </label>

                    <a href="auth/forgot_password.php">
                        Forgot Password?
                    </a>

                </div>


                <button
                    type="submit"
                    class="login-btn">

                    Login

                </button>

            </form>


            <div class="bottom-links">

                                <p>Don't have an employee account?</p>

                <a href="home/jobs.php">
                    Apply for a Job
                </a>

            </div>

        </div>

    </div>

    <script src="assets/js/login.js"></script>

</body>

</html>