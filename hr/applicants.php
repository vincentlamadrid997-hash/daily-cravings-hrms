<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";

$page_title = "Applicants";


$totalApplicants = $conn->query("
    SELECT COUNT(*)
    FROM applications
")->fetchColumn();


$pendingApplicants = $conn->query("
    SELECT COUNT(*)
    FROM applications
    WHERE status = 'Pending'
")->fetchColumn();


$interviewApplicants = $conn->query("
    SELECT COUNT(*)
    FROM interviews
    WHERE status = 'Scheduled'
")->fetchColumn();


$hiredApplicants = $conn->query("
    SELECT COUNT(*)
    FROM applications
    WHERE is_hired = 'Yes'
")->fetchColumn();


$search = trim($_GET['search'] ?? '');


$sql = "

SELECT
    a.application_id,
    a.first_name,
    a.middle_name,
    a.last_name,
    a.email,
    a.phone,
    a.status,
    a.is_hired,
    a.employee_id,
    a.created_at,
    jp.job_title

FROM applications a

LEFT JOIN job_postings jp
ON a.job_id = jp.job_id

WHERE 1

";


$params = [];


if (!empty($search)) {

    $sql .= "

    AND (
        a.first_name LIKE ?
        OR a.middle_name LIKE ?
        OR a.last_name LIKE ?
        OR a.email LIKE ?
        OR jp.job_title LIKE ?
        OR a.status LIKE ?
    )

    ";

    $keyword = "%" . $search . "%";

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

ORDER BY a.created_at DESC

";


$stmt = $conn->prepare($sql);

$stmt->execute($params);

$applicants = $stmt->fetchAll(PDO::FETCH_ASSOC);


$success = $_SESSION['hire_success'] ?? "";

$error = $_SESSION['hire_error'] ?? "";


unset($_SESSION['hire_success']);

unset($_SESSION['hire_error']);

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


<link 
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


</head>

<body>

<div class="hr-wrapper">

    <?php require_once "includes/sidebar.php"; ?>

    <div class="hr-content-wrapper">

        <?php require_once "includes/header.php"; ?>

        <main class="hr-main">

            <section class="hr-app-page">

                <div class="hr-app-summary-grid">

                    <div class="hr-app-summary-card blue">

                        <div class="hr-app-summary-icon">
                            <i class="fa-solid fa-users"></i>
                        </div>


                        <div class="hr-app-summary-content">

                            <span>Total Applicants</span>

                            <h2>
                                <?= $totalApplicants; ?>
                            </h2>

                            <p>All submitted applications</p>

                        </div>

                    </div>

                    <div class="hr-app-summary-card yellow">

                        <div class="hr-app-summary-icon">
                            <i class="fa-solid fa-clock"></i>
                        </div>


                        <div class="hr-app-summary-content">

                            <span>Pending Review</span>

                            <h2>
                                <?= $pendingApplicants; ?>
                            </h2>

                            <p>Waiting for evaluation</p>

                        </div>

                    </div>

                    <div class="hr-app-summary-card green">

                        <div class="hr-app-summary-icon">
                            <i class="fa-solid fa-calendar-check"></i>
                        </div>


                        <div class="hr-app-summary-content">

                            <span>For Interview</span>

                            <h2>
                                <?= $interviewApplicants; ?>
                            </h2>

                            <p>Scheduled interviews</p>

                        </div>

                    </div>

                    <div class="hr-app-summary-card purple">

                        <div class="hr-app-summary-icon">
                            <i class="fa-solid fa-user-check"></i>
                        </div>

                        <div class="hr-app-summary-content">

                            <span>Hired Applicants</span>

                            <h2>
                                <?= $hiredApplicants; ?>
                            </h2>

                            <p>Successfully hired employees</p>

                        </div>

                    </div>

                </div>

                <div class="hr-app-header">

                    <div class="hr-app-title">

                        <h1>
                            <i class="fa-solid fa-users"></i>
                            Applicants
                        </h1>

                        <p>Manage applicant records and recruitment process.</p>

                    </div>

                </div>

                <div class="hr-app-table-card">

                    <div class="hr-app-search">

                        <form method="GET">

                            <div class="hr-app-search-box">

                                <i class="fa-solid fa-magnifying-glass"></i>

                                <input
                                    type="text"
                                    name="search"
                                    placeholder="Search applicant..."
                                    value="<?= htmlspecialchars($search); ?>"
                                    autocomplete="off"
                                >

                            </div>

                        </form>

                    </div>

                    <div class="hr-app-table-wrapper">

                        <table class="hr-app-table">

                            <thead>

                                <tr>

                                    <th>Applicant</th>
                                    <th>Position</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Date Applied</th>
                                    <th width="230">
                                        Action
                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                            <?php if (!empty($applicants)): ?>

                                <?php foreach ($applicants as $applicant): ?>

                                    <?php

                                    $isHired = (
                                        $applicant['is_hired'] === "Yes"
                                    );

                                    $canInterview = (
                                        $applicant['status'] === "Shortlisted"
                                        ||
                                        $applicant['status'] === "Initial Interview"
                                    );

                                    $canHire = (
                                        $applicant['status'] === "Accepted"
                                        &&
                                        !$isHired
                                    );

                                    $status = $isHired
                                        ? "Hired"
                                        : $applicant['status'];

                                    $statusIcons = [

                                        "Pending" => "fa-clock",

                                        "Reviewed" => "fa-file-circle-check",

                                        "Shortlisted" => "fa-list-check",

                                        "Initial Interview" => "fa-calendar-check",

                                        "Final Interview" => "fa-user-clock",

                                        "Accepted" => "fa-circle-check",

                                        "Rejected" => "fa-circle-xmark",

                                        "Hired" => "fa-user-check"

                                    ];

                                    ?>

                                    <tr>

                                        <td>

                                            <div class="hr-applicant-name">

                                                <div class="hr-applicant-avatar">

                                                    <i class="fa-solid fa-user"></i>

                                                </div>

                                                <span>

                                                    <?= htmlspecialchars(
                                                        $applicant['first_name']
                                                        . " "
                                                        .
                                                        $applicant['last_name']
                                                    ); ?>

                                                </span>

                                            </div>

                                        </td>

                                        <td>

                                            <?= htmlspecialchars(
                                                $applicant['job_title'] ?? "N/A"
                                            ); ?>

                                        </td>

                                        <td>

                                            <?= htmlspecialchars(
                                                $applicant['email']
                                            ); ?>

                                        </td>

                                        <td>

                                            <span class="
                                                hr-app-status
                                                <?= strtolower(
                                                    str_replace(
                                                        ' ',
                                                        '-',
                                                        $status
                                                    )
                                                ); ?>
                                            ">

                                                <i class="fa-solid 
                                                    <?= $statusIcons[$status] ?? 'fa-circle'; ?>
                                                "></i>


                                                <?= htmlspecialchars($status); ?>

                                            </span>

                                        </td>

                                        <td>

                                            <?= date(
                                                "F d, Y",
                                                strtotime(
                                                    $applicant['created_at']
                                                )
                                            ); ?>

                                        </td>

                                        <td>

                                            <div class="hr-app-actions">

                                                <form
                                                    action="applicant_view.php"
                                                    method="POST"
                                                >

                                                    <?php csrfField(); ?>

                                                    <input
                                                        type="hidden"
                                                        name="application_id"
                                                        value="<?= $applicant['application_id']; ?>"
                                                    >

                                                    <button
                                                        type="submit"
                                                        class="hr-app-action view"
                                                        title="View Applicant"
                                                    >

                                                        <i class="fa-solid fa-eye"></i>

                                                    </button>

                                                </form>

                                                                                                <form
                                                    action="applicant_update.php"
                                                    method="POST"
                                                >

                                                    <?php csrfField(); ?>

                                                    <input
                                                        type="hidden"
                                                        name="application_id"
                                                        value="<?= $applicant['application_id']; ?>"
                                                    >

                                                    <button
                                                        type="submit"
                                                        class="hr-app-action update"
                                                        title="Update Status"
                                                    >

                                                        <i class="fa-solid fa-pen"></i>

                                                    </button>

                                                </form>

                                                                                                <form
                                                    action="applicant_interview.php"
                                                    method="POST"
                                                >

                                                    <?php csrfField(); ?>
                                                    <input
                                                        type="hidden"
                                                        name="application_id"
                                                        value="<?= $applicant['application_id']; ?>"
                                                    >

                                                    <button

                                                        type="submit"

                                                        class="
                                                            hr-app-action 
                                                            interview
                                                            <?= !$canInterview ? 'disabled' : ''; ?>
                                                        "

                                                        title="<?= $canInterview
                                                            ? 'Schedule Interview'
                                                            : 'Interview Closed';
                                                        ?>"

                                                        <?= !$canInterview ? "disabled" : ""; ?>

                                                    >

                                                        <i class="fa-solid 
                                                            <?= $canInterview
                                                                ? 'fa-calendar-plus'
                                                                : 'fa-lock';
                                                            ?>
                                                        "></i>

                                                    </button>

                                                </form>

                                                                                                <form
                                                    action="applicant_hire.php"
                                                    method="POST"
                                                >

                                                    <?php csrfField(); ?>

                                                    <input
                                                        type="hidden"
                                                        name="application_id"
                                                        value="<?= $applicant['application_id']; ?>"
                                                    >


                                                    <button

                                                        type="submit"

                                                        class="
                                                            hr-app-action 
                                                            hire
                                                            <?= !$canHire ? 'disabled' : ''; ?>
                                                        "

                                                        title="<?= $canHire
                                                            ? 'Hire Applicant'
                                                            : (
                                                                $isHired
                                                                ? 'Already Hired'
                                                                : 'Applicant must be Accepted first'
                                                            );
                                                        ?>"

                                                        <?= !$canHire ? "disabled" : ""; ?>

                                                    >

                                                        <i class="fa-solid 
                                                            <?= $canHire
                                                                ? 'fa-user-check'
                                                                : 'fa-lock';
                                                            ?>
                                                        "></i>

                                                    </button>

                                                </form>

                                            </div>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <tr>

                                    <td colspan="6">

                                        <div class="hr-app-empty">

                                            <i class="fa-solid fa-user-slash"></i>


                                            <h3>No Applicants Found</h3>

                                            <p>

                                                <?php if (!empty($search)): ?>

                                                    No records matched your search for:

                                                    <strong>
                                                        <?= htmlspecialchars($search); ?>
                                                    </strong>

                                                <?php else: ?>

                                                    There are currently no applicant records available.

                                                <?php endif; ?>

                                            </p>

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

<?php if (!empty($success)): ?>

<script>

Swal.fire({

    icon: "success",

    title: "Applicant Hired!",

    text: <?= json_encode($success); ?>,

    confirmButtonColor: "#003DA5",

    confirmButtonText: "OK"

});

</script>


<?php endif; ?>


<?php if (!empty($error)): ?>


<script>

Swal.fire({

    icon: "error",

    title: "Cannot Hire Applicant",

    text: <?= json_encode($error); ?>,

    confirmButtonColor: "#003DA5",

    confirmButtonText: "OK"

});

</script>

<?php endif; ?>


<script src="../assets/js/hr.js"></script>


</body>

</html>