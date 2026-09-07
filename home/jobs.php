<?php

session_start();

require_once "../config/db.php";

$application_success = "";

if (isset($_SESSION['application_success'])) {

    $application_success = $_SESSION['application_success'];

    unset($_SESSION['application_success']);

}


$stmt = $conn->prepare("
    SELECT
        jp.*,
        d.department_name,
        p.position_name

    FROM job_postings jp

    LEFT JOIN departments d
        ON jp.department_id = d.department_id

    LEFT JOIN positions p
        ON jp.position_id = p.position_id

    WHERE jp.status = 'Open'

    ORDER BY jp.created_at DESC
");

$stmt->execute();

$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Jobs | Daily Cravings Foods Inc.</title>

    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/jobs.css">

    <link 
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        rel="stylesheet">

</head>

<body class="jobs-page">

<div class="jobs-background">

    <img 
        src="../assets/images/logo_2.png"
        class="jobs-bg-logo"
        alt="Background Logo">

</div>

<header class="jobs-navbar">

    <div class="jobs-nav-container">

        <div class="jobs-brand-wrapper">

        <a 
            href="../home.php"
            class="jobs-home-icon"
            title="Home">

            <i class="fa-solid fa-house"></i>

        </a>

    <div class="jobs-logo-area">

        <img 
            src="../assets/images/logo_2.png"
            class="jobs-nav-logo"
            alt="Logo">

        <div>

            <h2>Daily Cravings Foods Inc.</h2>


            <span>Human Resource Management System</span>

        </div>

    </div>

    </div>

        <a 
            href="../login.php"
            class="jobs-login-btn">

            <i class="fa-solid fa-right-to-bracket"></i>
            Employee Login

        </a>

    </div>

</header>

<?php if (!empty($application_success)): ?>

<div class="jobs-alert-success">
    <i class="fa-solid fa-circle-check"></i>
    <?= htmlspecialchars($application_success); ?>
</div>

<?php endif; ?>

<section class="jobs-header">

    <h1>Available Positions</h1>

    <p>Explore career opportunities and join our growing team.</p>

</section>

<section class="jobs-section">

    <div class="jobs-title">

        <h2>Open Jobs</h2>

        <p>Search and apply for available positions.</p>

    </div>

    <div class="jobs-search">

        <i class="fa-solid fa-magnifying-glass"></i>

        <input
            type="text"
            id="jobSearch"
            placeholder="Search job title, department, location...">

    </div>

    <div class="jobs-container" id="jobsContainer">

        <div 
            class="jobs-empty search-empty"
            id="noJobsFound"
            style="display:none;">

            <i class="fa-solid fa-magnifying-glass"></i>

            <h3>No Jobs Found</h3>

            <p>We couldn't find any available job matching your search.</p>

            <button
                type="button"
                id="clearSearch"
                class="jobs-apply-btn">

                Clear Search

            </button>

        </div>

        <?php if(count($jobs) > 0): ?>

            <?php foreach($jobs as $job): ?>

                <div 
                    class="job-card"

                    data-search="<?= strtolower(
                        $job['job_title'] . " " .
                        ($job['department_name'] ?? '') . " " .
                        ($job['employment_type'] ?? '') . " " .
                        ($job['location'] ?? '')
                    ); ?>">

                    <div class="job-card-header">

                        <h3>
                            <?= htmlspecialchars($job['job_title']); ?>
                        </h3>

                        <span>
                            <?= htmlspecialchars($job['employment_type']); ?>
                        </span>

                    </div>

                    <div class="job-info">

                        <p>

                            <i class="fa-solid fa-building"></i>

                            <strong>
                                Department:
                            </strong>

                            <?= htmlspecialchars(
                                $job['department_name'] ?? 'Not Specified'
                            ); ?>

                        </p>

                        <p>

                            <i class="fa-solid fa-location-dot"></i>

                            <strong>
                                Location:
                            </strong>

                            <?= htmlspecialchars(
                                $job['location'] ?? 'Not Specified'
                            ); ?>

                        </p>

                        <p>

                            <i class="fa-solid fa-money-bill-wave"></i>

                            <strong>
                                Salary:
                            </strong>

                            <?= htmlspecialchars(
                                $job['salary'] ?? 'Negotiable'
                            ); ?>

                        </p>

                        <p>

                            <i class="fa-solid fa-users"></i>

                            <strong>
                                Vacancies:
                            </strong>

                            <?= htmlspecialchars(
                                $job['vacancies'] ?? 1
                            ); ?>

                        </p>

                        <p>

                            <i class="fa-solid fa-calendar"></i>

                            <strong>
                                Posted:
                            </strong>

                            <?= date(
                                "M d, Y",
                                strtotime($job['posted_date'])
                            ); ?>

                        </p>

                    </div>

                    <div class="job-content">

                        <h4>Job Description</h4>

                        <p>

                            <?= nl2br(
                                htmlspecialchars(
                                    $job['description']
                                )
                            ); ?>

                        </p>

                    </div>

                    <div class="job-content">


                        <h4>Requirements</h4>

                        <p>

                            <?= nl2br(
                                htmlspecialchars(
                                    $job['requirements']
                                )
                            ); ?>

                        </p>

                    </div>

                    <div class="job-content">


                        <h4>Responsibilities</h4>

                        <p>

                            <?= nl2br(
                                htmlspecialchars(
                                    $job['responsibilities']
                                )
                            ); ?>

                        </p>

                    </div>

                    <form 
                        action="apply.php"
                        method="POST">

                        <input
                            type="hidden"
                            name="job_id"
                            value="<?= $job['job_id']; ?>"
                            >

                        <button
                            type="submit"
                            class="jobs-apply-btn">

                            <i class="fa-solid fa-paper-plane"></i>
                            Apply Now

                        </button>

                    </form>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <div class="jobs-empty">

                <i class="fa-solid fa-briefcase"></i>

                <h3>No Available Jobs</h3>

                <p>Please check again soon.</p>

            </div>

        <?php endif; ?>

    </div>

</section>

<footer class="jobs-footer">

    <div class="jobs-footer-container">

                <div class="jobs-footer-brand">

            <img
                src="../assets/images/logo_2.png"
                class="jobs-footer-logo"
                alt="Logo">

            <div class="jobs-footer-brand-text">

                <h3>Daily Cravings Foods Inc.</h3>

                <p>Human Resource Management System</p>

            </div>

        </div>

        <div class="jobs-footer-info">

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

        <p class="jobs-copyright">

            © 2026 Daily Cravings Foods Inc.
            All Rights Reserved.

        </p>

    </div>

</footer>

<script src="../assets/js/jobs.js"></script>



</body>

</html>