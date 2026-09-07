<?php

session_start();

require_once "config/db.php";

$totalEmployees = $conn->query("
    SELECT COUNT(*)
    FROM employees
    WHERE employment_status = 'Active'
")->fetchColumn();

$totalDepartments = $conn->query("
    SELECT COUNT(*)
    FROM departments
    WHERE status = 'Active'
")->fetchColumn();

$totalApplications = $conn->query("
    SELECT COUNT(*)
    FROM applications
")->fetchColumn();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Daily Cravings Foods Inc. | Home</title>

    <link
        rel="stylesheet"
        href="assets/css/global.css">

    <link
        rel="stylesheet"
        href="assets/css/home.css">

    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        rel="stylesheet">

</head>

<body>

    <div class="background">

        <img
            src="assets/images/logo_2.png"
            class="bg-logo"
            alt="Background Logo">

    </div>

    <header class="navbar">

        <div class="nav-container">

            <div class="logo-area">

                <img
                    src="assets/images/logo_2.png"
                    class="nav-logo"
                    alt="Company Logo">

                <div>

                    <h2>Daily Cravings Foods Inc.</h2>

                    <span>
                        Human Resource Management System
                    </span>

                </div>

            </div>

            <a
                href="login.php"
                class="login-btn-nav">

                <i class="fa-solid fa-right-to-bracket"></i>

                Employee Login

            </a>

        </div>

    </header>

    <section class="hero">

        <div class="hero-container">

            <div class="hero-left">

                <span class="hero-tag">
                    Welcome to
                </span>

                <h1>Daily Cravings Foods Inc.</h1>

                <h3>Human Resource Management System</h3>

                <p>
                    Build Your Future with Daily Cravings Foods Inc. <br>
                    Join a passionate team dedicated to quality food, career growth, and professional development.
                </p>

                                     <div class="hero-buttons">

                    
                        <a href="home/jobs.php" class="btn-secondary">

                        <i class="fa-solid fa-user-plus"></i>
                        Apply Now

                    </a>

                </div>

                                <div class="hero-info">

                    <div>

                        <h2><?= number_format($totalEmployees); ?></h2>
                        <span>Employees</span>

                    </div>

                    <div>

                        <h2><?= number_format($totalDepartments); ?></h2>
                        <span>Departments</span>

                    </div>

                    <div>

                        <h2><?= number_format($totalApplications); ?></h2>
                        <span>Applications</span>

                    </div>

                </div>

            </div>

            <div class="hero-right">

                <div class="hero-image">

                    <img
                        src="assets/images/logo_2.png"
                        alt="Company Logo">

                </div>

            </div>

        </div>

    </section>

    <section class="why-join">

        <div class="section-title">

            <h2>Why Join Daily Cravings?</h2>

            <p>
                We value our employees by providing a supportive workplace,
                competitive benefits, and opportunities for continuous growth.
            </p>

        </div>

        <div class="cards-container">

            <div class="feature-card">

                <div class="feature-icon">
                    <i class="fa-solid fa-wallet"></i>
                </div>

                <h3>Competitive Benefits</h3>

                <ul>

                    <li>Competitive Salary</li>
                    <li>Government Benefits</li>
                    <li>Performance Incentives</li>
                    <li>Employee Rewards</li>

                </ul>

            </div>

            <div class="feature-card">

                <div class="feature-icon">
                    <i class="fa-solid fa-users"></i>
                </div>

                <h3>Positive Workplace</h3>

                <ul>

                    <li>Friendly Environment</li>
                    <li>Team Collaboration</li>
                    <li>Respect & Inclusion</li>
                    <li>Employee Recognition</li>

                </ul>

            </div>

            <div class="feature-card">

                <div class="feature-icon">
                    <i class="fa-solid fa-chart-line"></i>
                </div>

                <h3>Career Development</h3>

                <ul>

                    <li>Training Programs</li>
                    <li>Career Advancement</li>
                    <li>Promotion Opportunities</li>
                    <li>Continuous Learning</li>

                </ul>

            </div>

        </div>

    </section>

    <section class="about-section">

        <div class="about-card">

            <div class="gold-line"></div>

            <h2>About Daily Cravings Foods Inc.</h2>

            <p>
                Daily Cravings Foods Inc. is committed to delivering quality
                food while building a workplace where employees can grow
                professionally and achieve long-term success.
            </p>

            <p>
                Our Human Resource Management System provides a secure and
                centralized platform for managing recruitment, employee
                records, attendance, payroll, leave requests, interviews,
                and organizational reports.
            </p>

        </div>

    </section>

    <footer>

        <div class="footer-container">

                        <div class="footer-brand">

                <img
                    src="assets/images/logo_2.png"
                    class="footer-logo"
                    alt="Footer Logo">

                <div class="footer-brand-text">

                    <h3>Daily Cravings Foods Inc.</h3>

                    <p>Human Resource Management System</p>

                </div>

            </div>

            <div class="footer-info">

                <div>

                    <i class="fa-solid fa-location-dot"></i>
                    Dasmariñas City, Cavite

                </div>

                <div>

                    <i class="fa-solid fa-envelope"></i>
                    dailycravings.hrms@gmail.com

                </div>

                <div>

                    <i class="fa-solid fa-phone"></i>
                    +63 46 123 4567

                </div>

            </div>

                        <hr>

            <p class="copyright">
                © <?= date("Y"); ?> Daily Cravings Foods Inc.
                All Rights Reserved.
            </p>

        </div>  

    </footer>

    <script src="assets/js/home.js"></script>

</body>

</html>