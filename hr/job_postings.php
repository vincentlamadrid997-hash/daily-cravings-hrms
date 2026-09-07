<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";

$page_title = "Job Postings";


$conn->exec("
    UPDATE job_postings
    SET status = 'Closed'
    WHERE status = 'Open'
    AND closing_date IS NOT NULL
    AND closing_date < CURDATE()
");


$success = $_SESSION['job_success'] ?? "";
$error   = $_SESSION['job_error'] ?? "";

unset($_SESSION['job_success']);
unset($_SESSION['job_error']);


$totalJobs = $conn->query("
    SELECT COUNT(*)
    FROM job_postings
")->fetchColumn();


$openJobs = $conn->query("
    SELECT COUNT(*)
    FROM job_postings
    WHERE status = 'Open'
")->fetchColumn();


$closedJobs = $conn->query("
    SELECT COUNT(*)
    FROM job_postings
    WHERE status = 'Closed'
")->fetchColumn();


$departmentsHiring = $conn->query("
    SELECT COUNT(DISTINCT department_id)
    FROM job_postings
    WHERE status = 'Open'
")->fetchColumn();


$search = trim($_GET['search'] ?? "");


$sql = "

SELECT

    j.job_id,
    j.job_title,
    j.salary,
    j.employment_type,
    j.location,
    j.vacancies,
    j.status,
    j.posted_date,
    j.closing_date,
    j.created_at,

    d.department_name,

    p.position_name

FROM job_postings j

LEFT JOIN departments d
    ON j.department_id = d.department_id

LEFT JOIN positions p
    ON j.position_id = p.position_id

WHERE 1

";


$params = [];


if ($search !== "") {

    $sql .= "

        AND (

            j.job_title LIKE ?

            OR d.department_name LIKE ?

            OR p.position_name LIKE ?

            OR j.location LIKE ?

            OR j.employment_type LIKE ?

            OR j.status LIKE ?

        )

    ";


    $keyword = "%{$search}%";


    $params = [

        $keyword,
        $keyword,
        $keyword,
        $keyword,
        $keyword,
        $keyword

    ];

}


$sql .= "

ORDER BY j.created_at DESC

";


$stmt = $conn->prepare($sql);

$stmt->execute($params);


$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>



<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($page_title); ?> | Daily Cravings Foods Inc.</title>

    <link rel="stylesheet" href="../assets/css/global.css">

    <link rel="stylesheet" href="../assets/css/hr.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>


<body>

<div class="hr-wrapper">

    <?php require_once "includes/sidebar.php"; ?>

    <div class="hr-content-wrapper">

        <?php require_once "includes/header.php"; ?>

        <main class="hr-main">

            <section class="hr-job-page">

            <div class="hr-job-summary-grid">

                <div class="hr-job-summary-card blue">

                    <div class="hr-job-summary-icon">
                        <i class="fa-solid fa-bullhorn"></i>
                    </div>

                    <div class="hr-job-summary-content">

                        <span>Total Job Postings</span>

                        <h2>
                            <?= $totalJobs; ?>
                        </h2>

                        <p>All published job postings</p>

                    </div>

                </div>

                <div class="hr-job-summary-card green">

                    <div class="hr-job-summary-icon">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>

                    <div class="hr-job-summary-content">

                        <span>Open Jobs</span>

                        <h2>
                            <?= $openJobs; ?>
                        </h2>

                        <p>Currently accepting applicants</p>

                    </div>

                </div>

                <div class="hr-job-summary-card red">

                    <div class="hr-job-summary-icon">
                        <i class="fa-solid fa-circle-xmark"></i>
                    </div>

                    <div class="hr-job-summary-content">

                        <span>Closed Jobs</span>

                        <h2>
                            <?= $closedJobs; ?>
                        </h2>

                        <p>Hiring completed</p>

                    </div>

                </div>

                <div class="hr-job-summary-card purple">

                    <div class="hr-job-summary-icon">
                        <i class="fa-solid fa-building"></i>
                    </div>

                    <div class="hr-job-summary-content">

                        <span>Departments Hiring</span>

                        <h2>
                            <?= $departmentsHiring; ?>
                        </h2>

                        <p>Departments with open positions</p>

                    </div>

                </div>

            </div>

            <div class="hr-job-header">

                <div class="hr-job-title">

                    <h1>
                        <i class="fa-solid fa-bullhorn"></i>
                        Job Postings
                    </h1>

                    <p>Manage company job openings and recruitment postings.</p>

                </div>

                <a 
                    href="job_posting_add.php"
                    class="hr-job-add-btn"
                >

                    <i class="fa-solid fa-plus"></i>
                    Add Job Posting

                </a>

            </div>

            <div class="hr-job-table-card">

                <div class="hr-job-search">

                    <div class="hr-job-search-box">

                        <i class="fa-solid fa-magnifying-glass"></i>


                        <input

                            type="text"
                            id="jobSearch"
                            placeholder="Search job posting..."
                            autocomplete="off"
                        >

                    </div>

                </div>

                <div class="hr-job-table-wrapper">

                    <table class="hr-job-table">

                        <thead>

                            <tr>

                                <th>Job Title</th>
                                <th>Department</th>
                                <th>Employment</th>
                                <th>Salary</th>
                                <th>Vacancies</th>
                                <th>Status</th>
                                <th width="170">
                                    Action
                                </th>

                            </tr>

                        </thead>

                        <tbody id="jobTableBody">

                            <?php if (!empty($jobs)): ?>

                                <?php foreach ($jobs as $job): ?>

                                    <tr class="job-row">

                                        <td>

                                            <div class="hr-job-name">

                                                <div class="hr-job-avatar">

                                                    <i class="fa-solid fa-briefcase"></i>

                                                </div>

                                                <div>

                                                    <strong>

                                                        <?= htmlspecialchars($job['job_title']); ?>

                                                    </strong>

                                                    <br>

                                                    <small>

                                                        <?= htmlspecialchars($job['position_name'] ?? "No Position"); ?>

                                                    </small>

                                                </div>

                                            </div>

                                        </td>

                                        <td>

                                            <?= htmlspecialchars($job['department_name'] ?? "N/A"); ?>

                                        </td>

                                        <td>

                                            <span class="hr-job-type">

                                                <?= htmlspecialchars($job['employment_type']); ?>

                                            </span>

                                        </td>

                                        <td>

                                            ₱<?= number_format($job['salary'], 2); ?>

                                        </td>

                                        <td>

                                            <span class="hr-job-vacancy">

                                                <i class="fa-solid fa-users"></i>

                                                <?= (int)$job['vacancies']; ?>

                                            </span>

                                        </td>

                                        <td>

                                            <span class="hr-job-status <?= strtolower($job['status']); ?>">

                                                <?php if ($job['status'] === "Open"): ?>

                                                    <i class="fa-solid fa-circle-check"></i>

                                                <?php else: ?>

                                                    <i class="fa-solid fa-circle-xmark"></i>

                                                <?php endif; ?>

                                                <?= htmlspecialchars($job['status']); ?>

                                            </span>

                                        </td>

                                        <td>

                                            <div class="hr-job-actions">

                                                <form 
                                                    action="job_posting_view.php" 
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
                                                        class="hr-job-action view"
                                                        title="View Job Posting"
                                                    >

                                                        <i class="fa-solid fa-eye"></i>

                                                    </button>

                                                </form>

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
                                                        class="hr-job-action edit"
                                                        title="Edit Job Posting"
                                                    >

                                                        <i class="fa-solid fa-pen"></i>

                                                    </button>

                                                </form>

                                                <form
                                                    action="job_posting_status.php"
                                                    method="POST"
                                                    class="hr-job-status-form"
                                                >

                                                    <?php csrfField(); ?>

                                                    <input
                                                        type="hidden"
                                                        name="job_id"
                                                        value="<?= $job['job_id']; ?>"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="new_closing_date"
                                                        value=""
                                                    >

                                                    <button

                                                        type="submit"
                                                        class="hr-job-action status <?= strtolower($job['status']); ?>"

                                                        title="<?= $job['status'] === 'Open'
                                                            ? 'Close Job Posting'
                                                            : 'Reopen Job Posting'; ?>"

                                                    >

                                                        <?php if ($job['status'] === "Open"): ?>

                                                            <i class="fa-solid fa-toggle-on"></i>

                                                        <?php else: ?>

                                                            <i class="fa-solid fa-toggle-off"></i>

                                                        <?php endif; ?>

                                                    </button>

                                                </form>

                                            </div>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            <?php endif; ?>

                            <tr 
                                id="noJobResult"
                                style="display:none;"
                            >

                                <td colspan="7">

                                    <div class="hr-job-empty">

                                        <i class="fa-solid fa-magnifying-glass"></i>

                                        <h3>No Job Posting Found</h3>

                                        <p>No records matched your search.</p>

                                    </div>

                                </td>

                            </tr>

                            <?php if (empty($jobs)): ?>

                                <tr>

                                    <td colspan="7">

                                        <div class="hr-job-empty">

                                            <i class="fa-solid fa-bullhorn"></i>

                                            <h3>No Job Postings Found</h3>

                                            <p>There are currently no job postings available.</p>

                                        </div>

                                    </td>

                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </section>

    </main>

</div>

</div>

<div
    id="jobAlertData"
    data-success="<?= htmlspecialchars($success); ?>"
    data-error="<?= htmlspecialchars($error); ?>"
></div>

<script>

(function () {

    var resultsWrap = document;

    document.querySelectorAll(".hr-job-status-form").forEach(function (form) {

        form.addEventListener("submit", function (e) {

            e.preventDefault();

            const button = form.querySelector("button");
            const dateField = form.querySelector('input[name="new_closing_date"]');
            const isClosed = button.classList.contains("closed");

            if (isClosed) {

                // STEP 1: pick the new closing date
                Swal.fire({

                    title: "Set New Closing Date",
                    text: "Pick when this job posting should close.",
                    icon: "question",
                    input: "date",
                    inputAttributes: {
                        min: new Date().toISOString().split("T")[0]
                    },
                    showCancelButton: true,
                    confirmButtonColor: "#003DA5",
                    cancelButtonColor: "#90A4AE",
                    confirmButtonText: "Next",
                    inputValidator: (value) => {
                        if (!value) {
                            return "Please select a date.";
                        }
                    }

                }).then(function (dateResult) {

                    if (!dateResult.isConfirmed) {
                        return;
                    }

                    const chosenDate = dateResult.value;

                    // STEP 2: confirm before actually submitting
                    Swal.fire({

                        title: "Reopen this job posting?",
                        text: "It will be marked Open with a closing date of " + chosenDate + ".",
                        icon: "question",
                        showCancelButton: true,
                        confirmButtonColor: "#2E7D32",
                        cancelButtonColor: "#90A4AE",
                        confirmButtonText: "Yes, Reopen"

                    }).then(function (confirmResult) {

                        if (confirmResult.isConfirmed) {

                            dateField.value = chosenDate;
                            form.submit();

                        }

                    });

                });

            } else {

                // Closing needs no date — just confirm
                Swal.fire({

                    title: "Close this job posting?",
                    text: "Applicants will no longer be able to apply.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#C62828",
                    cancelButtonColor: "#90A4AE",
                    confirmButtonText: "Yes, Close"

                }).then(function (result) {

                    if (result.isConfirmed) {
                        form.submit();
                    }

                });

            }

        });

    });

})();

</script>

<script src="../assets/js/hr.js"></script>


</body>

</html>