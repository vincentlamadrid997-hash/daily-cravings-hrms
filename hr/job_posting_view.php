<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";

$page_title = "View Job Posting";


if (isset($_POST['job_id'])) {

    $_SESSION['selected_job'] = (int) $_POST['job_id'];

}


if (!isset($_SESSION['selected_job'])) {

    header("Location: job_postings.php");
    exit;

}


$job_id = (int) $_SESSION['selected_job'];


$conn->exec("
    UPDATE job_postings
    SET status = 'Closed'
    WHERE status = 'Open'
    AND closing_date IS NOT NULL
    AND closing_date < CURDATE()
");


$stmt = $conn->prepare("
    SELECT

        jp.job_id,
        jp.job_title,
        jp.salary,
        jp.employment_type,
        jp.description,
        jp.location,
        jp.vacancies,
        jp.requirements,
        jp.responsibilities,
        jp.status,
        jp.posted_date,
        jp.closing_date,
        jp.created_at,

        d.department_name,
        p.position_name

    FROM job_postings jp

    LEFT JOIN departments d
        ON jp.department_id = d.department_id

    LEFT JOIN positions p
        ON jp.position_id = p.position_id

    WHERE jp.job_id = ?

");


$stmt->execute([$job_id]);

$job = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$job) {

    unset($_SESSION['selected_job']);

    header("Location: job_postings.php");
    exit;

}


$salary = number_format($job['salary'], 2);

$status_class = strtolower($job['status']);

$posted_date = !empty($job['posted_date'])
    ? date("F d, Y", strtotime($job['posted_date']))
    : "N/A";

$closing_date = !empty($job['closing_date'])
    ? date("F d, Y", strtotime($job['closing_date']))
    : "N/A";

$department_name = $job['department_name'] ?? "N/A";
$position_name   = $job['position_name'] ?? "N/A";

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($page_title); ?> | Daily Cravings Foods Inc.</title>

    <link rel="stylesheet" href="../assets/css/global.css">

    <link rel="stylesheet" href="../assets/css/hr.css">

    <link rel="stylesheet" href="../assets/css/job_posting_add.css" >

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>

<body>

<div class="hr-wrapper">

    <?php require_once "includes/sidebar.php"; ?>

    <div class="hr-content-wrapper">

        <?php require_once "includes/header.php"; ?>

        <main class="hr-main">

            <section class="hr-job-add-page">

                <div class="hr-job-add-header">

                    <div class="hr-job-add-title">

                        <h1>

                            <i class="fa-solid fa-briefcase"></i>

                            View Job Posting

                        </h1>

                        <p>
                            View complete information about this job vacancy.
                        </p>

                    </div>

                    
                        href="job_postings.php"
                        class="hr-job-back-btn"
                    >

                        <i class="fa-solid fa-arrow-left"></i>

                        Back to Job Postings

                    </a>

                </div>

                <div class="hr-job-add-card">

                    <div class="hr-job-card-header">

                        <div class="hr-job-card-icon">

                            <i class="fa-solid fa-file-lines"></i>

                        </div>

                        <div>

                            <h2>
                                <?= htmlspecialchars($job['job_title']); ?>
                            </h2>

                            <p>
                                <?= htmlspecialchars($position_name); ?>
                            </p>

                        </div>

                        <span class="hr-job-status <?= $status_class; ?>">

                            <i class="fa-solid fa-circle"></i>

                            <?= htmlspecialchars($job['status']); ?>

                        </span>

                    </div>

                    <div class="hr-job-grid">

                                                <div class="hr-job-group">

                            <label>
                                <i class="fa-solid fa-building"></i>
                                Department
                            </label>

                            <input
                                type="text"
                                value="<?= htmlspecialchars($department_name); ?>"
                                readonly
                            >

                        </div>

                        <div class="hr-job-group">

                            <label>
                                <i class="fa-solid fa-user-tie"></i>
                                Position
                            </label>

                            <input
                                type="text"
                                value="<?= htmlspecialchars($position_name); ?>"
                                readonly
                            >

                        </div>

                        <div class="hr-job-group">

                            <label>
                                <i class="fa-solid fa-money-bill-wave"></i>
                                Salary
                            </label>

                            <div class="hr-job-input-icon">

                                <span>₱</span>

                                <input
                                    type="text"
                                    value="<?= $salary; ?>"
                                    readonly
                                >

                            </div>

                        </div>

                        <div class="hr-job-group">

                            <label>
                                <i class="fa-solid fa-user-clock"></i>
                                Employment Type
                            </label>

                            <input
                                type="text"
                                value="<?= htmlspecialchars($job['employment_type']); ?>"
                                readonly
                            >

                        </div>

                        <div class="hr-job-group">

                            <label>
                                <i class="fa-solid fa-location-dot"></i>
                                Location
                            </label>

                            <input
                                type="text"
                                value="<?= htmlspecialchars($job['location']); ?>"
                                readonly
                            >

                        </div>

                        <div class="hr-job-group">

                            <label>
                                <i class="fa-solid fa-users"></i>
                                Number of Vacancies
                            </label>

                            <input
                                type="text"
                                value="<?= (int) $job['vacancies']; ?> Position(s)"
                                readonly
                            >

                        </div>

                        <div class="hr-job-group">

                            <label>
                                <i class="fa-solid fa-calendar-plus"></i>
                                Posted Date
                            </label>

                            <input
                                type="text"
                                value="<?= $posted_date; ?>"
                                readonly
                            >

                        </div>

                        <div class="hr-job-group">

                            <label>
                                <i class="fa-solid fa-calendar-xmark"></i>
                                Closing Date
                            </label>

                            <input
                                type="text"
                                value="<?= $closing_date; ?>"
                                readonly
                            >

                        </div>

                        <div class="hr-job-group hr-job-full">

                            <label>
                                <i class="fa-solid fa-align-left"></i>
                                Job Description
                            </label>

                            <textarea readonly><?= htmlspecialchars($job['description']); ?></textarea>

                        </div>

                        <div class="hr-job-group hr-job-full">

                            <label>
                                <i class="fa-solid fa-list-check"></i>
                                Requirements
                            </label>

                            <textarea readonly><?= htmlspecialchars($job['requirements']); ?></textarea>

                        </div>

                        <div class="hr-job-group hr-job-full">

                            <label>
                                <i class="fa-solid fa-tasks"></i>
                                Responsibilities
                            </label>

                            <textarea readonly><?= htmlspecialchars($job['responsibilities']); ?></textarea>

                        </div>

                    </div>

                    <div class="hr-job-actions">

                        <form
                            action="job_posting_edit.php"
                            method="POST"
                        >

                            <?php csrfField(); ?>

                            <input
                                type="hidden"
                                name="job_id"
                                value="<?= $job['job_id']; ?>"
                            >

                            <button
                                type="submit"
                                class="hr-job-btn save"
                            >

                                <i class="fa-solid fa-pen"></i>

                                Edit Job Posting

                            </button>

                        </form>

                    </div>

                </div>

            </section>

        </main>

    </div>

</div>

<script src="../assets/js/hr.js"></script>

</body>

</html>