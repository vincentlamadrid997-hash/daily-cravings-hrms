<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";

$page_title = "Edit Job Posting";

$job_error = $_SESSION['job_error'] ?? "";
$job_success = $_SESSION['job_success'] ?? "";

unset($_SESSION['job_error']);
unset($_SESSION['job_success']);

if (
    isset($_POST['job_id']) &&
    !isset($_POST['job_title'])
) {

    $_SESSION['selected_job'] =
        (int) $_POST['job_id'];


    header(
        "Location: job_posting_edit.php"
    );

    exit;

}

if (
    !isset($_SESSION['selected_job'])
) {

    header(
        "Location: job_postings.php"
    );

    exit;

}

$job_id =
    $_SESSION['selected_job'];

$conn->exec("
    UPDATE job_postings
    SET status = 'Closed'
    WHERE status = 'Open'
    AND closing_date IS NOT NULL
    AND closing_date < CURDATE()
");

$departments = $conn->query("
    SELECT
        department_id,
        department_name
    FROM departments
    WHERE status = 'Active'
    ORDER BY department_name ASC
")
->fetchAll(PDO::FETCH_ASSOC);

$positions = $conn->query("
    SELECT
        position_id,
        position_name
    FROM positions
    WHERE status = 'Active'
    ORDER BY position_name ASC
")
->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("
    SELECT *
    FROM job_postings
    WHERE job_id = ?
");

$stmt->execute([
    $job_id
]);

$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {

    unset(
        $_SESSION['selected_job']
    );

    header(
        "Location: job_postings.php"
    );

    exit;

}

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
) {

    requireCSRFToken("job_posting_edit.php", "job_error");

    $department_id =
        $_POST['department_id'] ?? "";

    $position_id =
        $_POST['position_id'] ?? "";

    $job_title =
        trim(
            $_POST['job_title'] ?? ""
        );

    $salary =
        str_replace(
            ",",
            "",
            $_POST['salary'] ?? ""
        );

    $employment_type =
        $_POST['employment_type'] ?? "";

    $description =
        trim(
            $_POST['description'] ?? ""
        );

    $location =
        trim(
            $_POST['location'] ?? ""
        );

    $vacancies =
        $_POST['vacancies'] ?? "";

    $requirements =
        trim(
            $_POST['requirements'] ?? ""
        );

    $responsibilities =
        trim(
            $_POST['responsibilities'] ?? ""
        );

    $posted_date =
        $_POST['posted_date'] ?? "";

    $closing_date =
        $_POST['closing_date'] ?? "";

    if (

        empty($department_id) ||
        empty($position_id) ||
        empty($job_title) ||
        empty($salary) ||
        empty($employment_type) ||
        empty($description) ||
        empty($location) ||
        empty($vacancies) ||
        empty($requirements) ||
        empty($responsibilities) ||
        empty($posted_date) ||
        empty($closing_date)

    ) {

        $_SESSION['job_error'] =
            "Please complete all required fields.";

    }

    elseif (
        !is_numeric($salary)
    ) {

        $_SESSION['job_error'] =
            "Salary must contain numbers only.";

    }

    elseif (
        !ctype_digit($vacancies)
    ) {

        $_SESSION['job_error'] =
            "Vacancies must contain numbers only.";

    }

    elseif (
        $closing_date < $posted_date
    ) {

        $_SESSION['job_error'] =
            "Closing date cannot be earlier than posted date.";

    }

    else {

        $status = (
            $closing_date >= date("Y-m-d")
        )

        ? "Open"

        : "Closed";

                $update = $conn->prepare("
            UPDATE job_postings SET

                position_id = ?,
                department_id = ?,
                job_title = ?,
                salary = ?,
                employment_type = ?,
                description = ?,
                location = ?,
                vacancies = ?,
                requirements = ?,
                responsibilities = ?,
                status = ?,
                posted_date = ?,
                closing_date = ?

            WHERE job_id = ?
        ");

        $update->execute([

            $position_id,
            $department_id,
            $job_title,
            $salary,
            $employment_type,
            $description,
            $location,
            $vacancies,
            $requirements,
            $responsibilities,
            $status,
            $posted_date,
            $closing_date,
            $job_id

        ]);

        $_SESSION['job_success'] =
            "Job posting updated successfully.";

        unset(
            $_SESSION['selected_job']
        );

        header(
            "Location: job_postings.php"
        );

        exit;

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
                            <i class="fa-solid fa-pen-to-square"></i>
                            Edit Job Posting
                        </h1>

                        <p>Update company job vacancy information.</p>

                    </div>

                    <div>

                        
                            href="job_postings.php"
                            class="hr-job-back-btn"
                        >

                            <i class="fa-solid fa-arrow-left"></i>
                            Back to Job Postings
                        </a>

                    </div>

                </div>

                <div class="hr-job-add-card">

                    <div class="hr-job-card-header">

                        <div class="hr-job-card-icon">
                            <i class="fa-solid fa-file-pen"></i>
                        </div>

                        <div>

                            <h2>Job Information</h2>

                            <p>Edit existing job posting details.</p>

                        </div>

                    </div>

                    <form
                        method="POST"
                        id="jobForm"
                        novalidate
                    >

                        <?php csrfField(); ?>

                        <div class="hr-job-grid">

                            <div class="hr-job-group">

                                <label>
                                    <i class="fa-solid fa-heading"></i>
                                    Job Title *
                                </label>

                                <input
                                    type="text"
                                    name="job_title"
                                    class="hr-job-input"
                                    value="<?= htmlspecialchars($job['job_title']); ?>"
                                    placeholder="Enter job title"
                                    required
                                >

                            </div>

                            <div class="hr-job-group">

                                <label>
                                    <i class="fa-solid fa-building"></i>
                                    Department *
                                </label>

                                <select
                                    name="department_id"
                                    class="hr-job-input"
                                    required
                                >

                                    <option value="">
                                        Select Department
                                    </option>

                                    <?php foreach($departments as $department): ?>

                                        <option
                                            value="<?= $department['department_id']; ?>"
                                            <?=
                                            $department['department_id'] == $job['department_id']
                                            ? "selected"
                                            : ""
                                            ?>
                                        >

                                            <?= htmlspecialchars($department['department_name']); ?>
                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>

                            <div class="hr-job-group">

                                <label>
                                    <i class="fa-solid fa-user-tie"></i>
                                    Position *
                                </label>

                                <select
                                    name="position_id"
                                    class="hr-job-input"
                                    required
                                >

                                    <option value="">
                                        Select Position
                                    </option>

                                    <?php foreach($positions as $position): ?>

                                        <option
                                            value="<?= $position['position_id']; ?>"
                                            <?=
                                            $position['position_id'] == $job['position_id']
                                            ? "selected"
                                            : ""
                                            ?>
                                        >

                                            <?= htmlspecialchars($position['position_name']); ?>
                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>

                            <div class="hr-job-group hr-job-salary">

                                <label>
                                    <i class="fa-solid fa-money-bill-wave"></i>
                                    Salary *
                                </label>

                                <div class="hr-job-input-icon">

                                    <span>
                                        ₱
                                    </span>

                                    <input
                                        type="text"
                                        name="salary"
                                        id="salary"
                                        class="hr-job-input"
                                        value="<?= number_format($job['salary'],2,'.',''); ?>"
                                        placeholder="Enter salary"
                                        required
                                    >

                                </div>

                            </div>

                            <div class="hr-job-group">

                                <label>
                                    <i class="fa-solid fa-user-clock"></i>
                                    Employment Type *
                                </label>

                                <select
                                    name="employment_type"
                                    class="hr-job-input"
                                    required
                                >

                                    <option value="">
                                        Select Type
                                    </option>

                                    <?php

                                    $employment_types = [

                                        "Full-Time",
                                        "Part-Time",
                                        "Contractual",
                                        "Probationary",
                                        "Internship"

                                    ];

                                    ?>

                                    <?php foreach($employment_types as $type): ?>

                                        <option
                                            value="<?= $type; ?>"
                                            <?=
                                            $job['employment_type'] == $type
                                            ? "selected"
                                            : ""
                                            ?>
                                        >

                                            <?= $type; ?>
                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>

                            <div class="hr-job-group">

                                <label>
                                    <i class="fa-solid fa-location-dot"></i>
                                    Location *
                                </label>

                                <input
                                    type="text"
                                    name="location"
                                    class="hr-job-input"
                                    value="<?= htmlspecialchars($job['location']); ?>"
                                    placeholder="Enter job location"
                                    required
                                >

                            </div>

                            <div class="hr-job-group">

                                <label>
                                    <i class="fa-solid fa-users"></i>
                                    Number of Vacancies *
                                </label>

                                <input
                                    type="number"
                                    name="vacancies"
                                    class="hr-job-input"
                                    min="1"
                                    value="<?= $job['vacancies']; ?>"
                                    required
                                >

                            </div>

                            <div class="hr-job-group">

                                <label>
                                    <i class="fa-solid fa-calendar-plus"></i>
                                    Posted Date *
                                </label>

                                <input
                                    type="date"
                                    name="posted_date"
                                    class="hr-job-input"
                                    value="<?= $job['posted_date']; ?>"
                                    required
                                >

                            </div>

                            <div class="hr-job-group">

                                <label>
                                    <i class="fa-solid fa-calendar-xmark"></i>
                                    Closing Date *
                                </label>

                                <input
                                    type="date"
                                    name="closing_date"
                                    class="hr-job-input"
                                    value="<?= $job['closing_date']; ?>"
                                    required
                                >

                            </div>

                            <div class="hr-job-group hr-job-full">

                                <label>
                                    <i class="fa-solid fa-align-left"></i>
                                    Job Description 
                                </label>

                                <textarea
                                    name="description"
                                    class="hr-job-input"
                                    rows="5"
                                    required
                                ><?= htmlspecialchars($job['description']); ?></textarea>

                            </div>

                            <div class="hr-job-group hr-job-full">

                                <label>
                                    <i class="fa-solid fa-list-check"></i>
                                    Requirements *
                                </label>

                                <textarea
                                    name="requirements"
                                    class="hr-job-input"
                                    rows="5"
                                    required
                                ><?= htmlspecialchars($job['requirements']); ?></textarea>

                            </div>

                            <div class="hr-job-group hr-job-full">

                                <label>
                                    <i class="fa-solid fa-tasks"></i>
                                    Responsibilities *
                                </label>

                                <textarea
                                    name="responsibilities"
                                    class="hr-job-input"
                                    rows="5"
                                    required
                                ><?= htmlspecialchars($job['responsibilities']); ?></textarea>

                            </div>

                        </div>

                        <div class="hr-job-actions">

                            <button
                                type="submit"
                                class="hr-job-btn save"

                            >

                                <i class="fa-solid fa-save"></i>
                                Update Job Posting
                            </button>

                        </div>

                    </form>

                </div>

            </section>

        </main>

    </div>

</div>

<div
    id="jobAlertData"
    data-error="<?= htmlspecialchars($job_error); ?>"
    data-success="<?= htmlspecialchars($job_success); ?>"
></div>


<script src="../assets/js/hr.js"></script>


</body>

</html>