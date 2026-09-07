<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";


$page_title = "Interviews";


$totalInterviews = $conn->query("
    SELECT COUNT(*)
    FROM interviews
")->fetchColumn();


$initialInterviews = $conn->query("
    SELECT COUNT(*)
    FROM interviews
    WHERE stage = 'Initial'
")->fetchColumn();


$finalInterviews = $conn->query("
    SELECT COUNT(*)
    FROM interviews
    WHERE stage = 'Final'
")->fetchColumn();


$completedInterviews = $conn->query("
    SELECT COUNT(*)
    FROM interviews
    WHERE status = 'Completed'
")->fetchColumn();


$search = trim($_GET['search'] ?? '');


$sql = "

SELECT

    i.interview_id,
    i.application_id,
    i.interview_date,
    i.interview_time,
    i.stage,
    i.interviewer,
    i.status,
    i.email_sent,
    i.email_sent_at,
    i.created_at,

    a.first_name,
    a.middle_name,
    a.last_name,
    a.email,

    jp.job_title

FROM interviews i

LEFT JOIN applications a
ON i.application_id = a.application_id

LEFT JOIN job_postings jp
ON a.job_id = jp.job_id

WHERE 1

";


$params = [];


if (!empty($search)) {

    $sql .= "

    AND (

        a.first_name LIKE ?
        OR a.last_name LIKE ?
        OR a.email LIKE ?
        OR jp.job_title LIKE ?
        OR i.stage LIKE ?
        OR i.status LIKE ?
        OR i.interviewer LIKE ?

    )

    ";


    $keyword = "%" . $search . "%";


    $params = [

        $keyword,
        $keyword,
        $keyword,
        $keyword,
        $keyword,
        $keyword,
        $keyword

    ];

}


$sql .= "

ORDER BY

    i.interview_date ASC,
    i.interview_time ASC

";


$stmt = $conn->prepare($sql);

$stmt->execute($params);


$interviews = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>


<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">


<title>
<?= htmlspecialchars($page_title); ?> | Daily Cravings Foods Inc.
</title>


<link rel="stylesheet" href="../assets/css/global.css">

<link rel="stylesheet" href="../assets/css/hr.css">


<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">


</head>

<body>

<div class="hr-wrapper">

<?php require_once "includes/sidebar.php"; ?>

<div class="hr-content-wrapper">

<?php require_once "includes/header.php"; ?>

<main class="hr-main">

<section class="hr-int-page">

<div class="hr-int-summary-grid">

    <div class="hr-int-summary-card blue">

        <div class="hr-int-summary-icon">
            <i class="fa-solid fa-calendar-days"></i>
        </div>

        <div class="hr-int-summary-content">

            <span>Total Interviews</span>

            <h2>
                <?= $totalInterviews; ?>
            </h2>

            <p>All interview schedules</p>

        </div>

    </div>

    <div class="hr-int-summary-card yellow">

        <div class="hr-int-summary-icon">
            <i class="fa-solid fa-comments"></i>
        </div>

        <div class="hr-int-summary-content">

            <span>Initial Interview</span>

            <h2>
                <?= $initialInterviews; ?>
            </h2>

            <p>First recruitment stage</p>

        </div>

    </div>

    <div class="hr-int-summary-card purple">

        <div class="hr-int-summary-icon">
            <i class="fa-solid fa-user-tie"></i>
        </div>

        <div class="hr-int-summary-content">

            <span>Final Interview</span>

            <h2>
                <?= $finalInterviews; ?>
            </h2>

            <p>Final evaluation stage</p>

        </div>

    </div>

    <div class="hr-int-summary-card green">

        <div class="hr-int-summary-icon">
            <i class="fa-solid fa-circle-check"></i>
        </div>

        <div class="hr-int-summary-content">

            <span>Completed</span>

            <h2>
                <?= $completedInterviews; ?>
            </h2>

            <p>Finished interviews</p>

        </div>

    </div>


</div>

<div class="hr-int-header">

    <div class="hr-int-title">

        <h1>
            <i class="fa-solid fa-calendar-check"></i>
            Interviews
        </h1>

        <p>Manage applicant interviews and recruitment schedules.</p>

    </div>

</div>

<div class="hr-int-table-card">

    <div class="hr-int-search">

        <form method="GET">

            <div class="hr-int-search-box">

                <i class="fa-solid fa-magnifying-glass"></i>

                <input
                    type="text"
                    name="search"
                    id="interviewSearch"
                    placeholder="Search interview..."
                    value="<?= htmlspecialchars($search); ?>"
                >

            </div>

        </form>

    </div>

    <div class="hr-int-table-wrapper">

        <table class="hr-int-table">

            <thead>

                <tr>

                    <th>Applicant</th>
                    <th>Position</th>
                    <th>Interview Date</th>
                    <th>Stage</th>
                    <th>Interviewer</th>
                    <th>Status</th>
                    <th>Email</th>
                    <th>Action</th>

                </tr>

            </thead>

            <tbody id="interviewTableBody">

            <?php if (!empty($interviews)): ?>

                <?php foreach ($interviews as $interview): ?>

                <tr class="interview-row">

                    <td>

                        <div class="hr-int-applicant">

                            <div class="hr-int-avatar">

                                <i class="fa-solid fa-user"></i>

                            </div>

                            <div>

                                <strong>

                                    <?= htmlspecialchars(
                                        $interview['first_name']
                                        . " "
                                        . $interview['last_name']
                                    ); ?>

                                </strong>

                                <small>

                                    <?= htmlspecialchars(
                                        $interview['email']
                                    ); ?>

                                </small>

                            </div>

                        </div>

                    </td>

                    <td>

                        <?= htmlspecialchars(
                            $interview['job_title'] ?? "N/A"
                        ); ?>

                    </td>

                    <td>

                        <?= date(
                            "F d, Y",
                            strtotime($interview['interview_date'])
                        ); ?>

                        <br>

                        <small>

                            <?= date(
                                "h:i A",
                                strtotime($interview['interview_time'])
                            ); ?>

                        </small>

                    </td>

                    <td>

                        <span class="hr-int-stage">

                            <?= htmlspecialchars(
                                $interview['stage']
                            ); ?>

                        </span>

                    </td>

                    <td>

                        <?= htmlspecialchars(
                            $interview['interviewer']
                        ); ?>

                    </td>

                    <td>

                        <span class="hr-int-status <?= strtolower($interview['status']); ?>">


                            <?= htmlspecialchars(
                                $interview['status']
                            ); ?>

                        </span>

                    </td>

                    <td>

                        <?php if (($interview['email_sent'] ?? "No") === "Yes"): ?>

                            <span class="hr-int-status completed">

                                <i class="fa-solid fa-check"></i>
                                Sent

                            </span>

                            <br>

                            <small>

                                <?= date(
                                    "F d, Y",
                                    strtotime($interview['email_sent_at'])
                                ); ?>

                            </small>

                        <?php else: ?>

                            <span class="hr-int-status pending">

                                <i class="fa-solid fa-clock"></i>
                                Not Sent

                            </span>

                        <?php endif; ?>

                    </td>

                    <td>

                        <div class="hr-int-actions">

                                                        <form action="interview_view.php" method="POST">

                                <?php csrfField(); ?>

                                <input
                                    type="hidden"
                                    name="interview_id"
                                    value="<?= $interview['interview_id']; ?>"
                                >

                                <button
                                    type="submit"
                                    class="hr-int-action view"
                                    title="View Interview Details"
                                >

                                    <i class="fa-solid fa-eye"></i>

                                </button>

                            </form>

                                                        <form action="interview_update.php" method="POST">

                                <?php csrfField(); ?>

                                <input
                                    type="hidden"
                                    name="interview_id"
                                    value="<?= $interview['interview_id']; ?>"
                                >

                                <button
                                    type="submit"
                                    class="hr-int-action update"
                                    title="Update Interview"
                                >

                                    <i class="fa-solid fa-pen"></i>

                                </button>

                            </form>

                                                        <form action="interview_email.php" method="POST">

                                <?php csrfField(); ?>

                                <input
                                    type="hidden"
                                    name="interview_id"
                                    value="<?= $interview['interview_id']; ?>"
                                >

                                <button
                                    type="submit"
                                    class="hr-int-action email"
                                    title="Send Interview Email"
                                >

                                    <i class="fa-solid fa-envelope"></i>

                                </button>

                            </form>

                                                        <form action="interview_reschedule.php" method="POST">

                                <?php csrfField(); ?>

                                <input
                                    type="hidden"
                                    name="interview_id"
                                    value="<?= $interview['interview_id']; ?>"
                                >

                                <button
                                    type="submit"
                                    class="hr-int-action reschedule"
                                    title="Reschedule Interview"
                                >

                                    <i class="fa-solid fa-calendar-days"></i>

                                </button>

                            </form>

                        </div>

                    </td>

                </tr>

                <?php endforeach; ?>

            <?php else: ?>

                <tr>

                    <td colspan="8">

                        <div class="hr-int-empty">

                            <i class="fa-solid fa-calendar-xmark"></i>

                            <h3>No Interview Records Found</h3>

                            <p>There are currently no scheduled interviews.</p>

                        </div>

                    </td>

                </tr>

            <?php endif; ?>

            <tr id="noInterviewResult" style="display:none;">

                <td colspan="8">

                    <div class="hr-int-empty">

                        <i class="fa-solid fa-calendar-xmark"></i>

                        <h3>No Matching Interview Found</h3>

                        <p>Try searching another keyword.</p>

                    </div>

                </td>

            </tr>

            </tbody>

        </table>

    </div>

</div>

</section>

</main>

</div>

</div>


<script src="../assets/js/hr.js"></script>


</body>

</html>