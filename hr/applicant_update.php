<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";


$page_title = "Update Applicant";


if (isset($_POST['application_id'])) {

    $_SESSION['selected_application'] = (int) $_POST['application_id'];

}


if (!isset($_SESSION['selected_application'])) {

    header("Location: applicants.php");
    exit;

}


$application_id = (int) $_SESSION['selected_application'];


$status_flow = [

    "Pending" => [
        "Reviewed",
        "Rejected"
    ],

    "Reviewed" => [
        "Shortlisted",
        "Rejected"
    ],

    "Shortlisted" => [
        "Initial Interview",
        "Rejected"
    ],

    "Initial Interview" => [
        "Final Interview",
        "Rejected"
    ],

    "Final Interview" => [
        "Accepted",
        "Rejected"
    ],

    "Accepted" => [],

    "Rejected" => []

];


$alert_type = "";
$alert_message = "";


$stmt = $conn->prepare("

    SELECT 
        a.*,
        jp.job_title

    FROM applications a

    LEFT JOIN job_postings jp

    ON a.job_id = jp.job_id

    WHERE a.application_id = ?

");


$stmt->execute([
    $application_id
]);


$applicant = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$applicant) {

    unset($_SESSION['selected_application']);

    header("Location: applicants.php");
    exit;

}


if (

    $_SERVER["REQUEST_METHOD"] === "POST"

    && isset($_POST['update_status'])

) {

    requireCSRFToken("applicant_update.php");


    $new_status = trim($_POST['status']);

    $current_status = $applicant['status'];

    $allowed_next = $status_flow[$current_status] ?? [];


    if (in_array($new_status, $allowed_next, true)) {


        $update = $conn->prepare("

            UPDATE applications

            SET status = ?

            WHERE application_id = ?

        ");


        $update->execute([

            $new_status,

            $application_id

        ]);


        $alert_type = "success";

        $alert_message = "Applicant status updated successfully!";


        $stmt->execute([
            $application_id
        ]);


        $applicant = $stmt->fetch(PDO::FETCH_ASSOC);


    } else {


        $alert_type = "error";

        $alert_message = 
        "Invalid status flow. Previous stages are not allowed.";


    }


}


$current_status = $applicant['status'];


$available_status = $status_flow[$current_status] ?? [];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">


    <title>
        <?= htmlspecialchars($page_title); ?> |
        Daily Cravings Foods Inc.
    </title>


    <link rel="stylesheet" href="../assets/css/global.css">

    <link rel="stylesheet" href="../assets/css/hr.css">

    <link rel="stylesheet" href="../assets/css/crud_hr.css">


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

            <section class="crud-page">

                <div class="crud-header">

                    <div class="crud-title">

                        <h1>

                            <i class="fa-solid fa-user-pen"></i>
                            Update Applicant

                        </h1>

                        <p>Update applicant recruitment progress.</p>

                    </div>

                    <div class="crud-header-actions">

                        <a 
                            href="applicants.php"
                            class="crud-back-btn"
                        >

                            <i class="fa-solid fa-arrow-left"></i>
                            Back to Applicants

                        </a>

                    </div>

                </div>

                <div class="crud-card">

                    <div class="crud-card-header">

                        <div class="crud-icon">

                            <i class="fa-solid fa-user-pen"></i>

                        </div>

                        <div>

                            <h2>

                                <?= htmlspecialchars(
                                    $applicant['first_name']
                                    . " "
                                    . $applicant['last_name']
                                ); ?>


                            </h2>

                            <p>Recruitment Status Management</p>

                        </div>

                    </div>

                    <div class="crud-info">

                        <div class="crud-info-box">

                            <span>
                                Position Applied
                            </span>

                            <strong>

                                <?= htmlspecialchars(
                                    $applicant['job_title'] ?? "N/A"
                                ); ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Full Name
                            </span>

                            <strong>

                                <?= htmlspecialchars(
                                    $applicant['first_name']
                                    . " "
                                    . $applicant['middle_name']
                                    . " "
                                    . $applicant['last_name']
                                ); ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Email Address
                            </span>

                            <strong>

                                <?= htmlspecialchars(
                                    $applicant['email']
                                ); ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Phone Number
                            </span>

                            <strong>

                                <?= htmlspecialchars(
                                    $applicant['phone'] ?? "N/A"
                                ); ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Current Status
                            </span>

                            <strong>

                                <?= htmlspecialchars(
                                    $current_status
                                ); ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Date Applied
                            </span>

                            <strong>

                                <?= date(
                                    "F d, Y",
                                    strtotime(
                                        $applicant['created_at']
                                    )
                                ); ?>

                            </strong>

                        </div>

                    </div>

                                        <form method="POST">

                        <?php csrfField(); ?>

                        <input 
                            type="hidden"
                            name="application_id"
                            value="<?= $application_id; ?>"
                        >

                        <div class="crud-form-grid">

                            <div class="crud-group crud-full">

                                <label for="status">

                                    Next Recruitment Status

                                </label>

                                <?php if (!empty($available_status)): ?>

                                    <select

                                        id="status"
                                        name="status"
                                        class="crud-control"
                                        required

                                    >

                                        <option 
                                            value=""
                                            selected
                                            disabled
                                        >

                                            Current Status:

                                            <?= htmlspecialchars(
                                                $current_status
                                            ); ?>

                                        </option>

                                        <?php foreach ($available_status as $next_status): ?>

                                            <option 
                                                value="<?= htmlspecialchars($next_status); ?>"
                                            >

                                                <?= htmlspecialchars(
                                                    $next_status
                                                ); ?>

                                            </option>

                                        <?php endforeach; ?>

                                    </select>

                                <?php else: ?>

                                    <input

                                        type="text"
                                        class="crud-control"

                                        value="<?= htmlspecialchars(
                                            $current_status
                                        ); ?>"

                                        readonly
                                    >

                                    <small class="crud-note">

                                        Recruitment process has already ended.
                                        No further status changes are allowed.

                                    </small>

                                <?php endif; ?>

                            </div>

                        </div>

                        <div class="crud-actions">

                            <?php if (!empty($available_status)): ?>

                                <button

                                    type="submit"
                                    name="update_status"
                                    class="crud-btn crud-btn-primary"

                                >

                                    <i class="fa-solid fa-floppy-disk"></i>
                                    Save Changes

                                </button>

                            <?php endif; ?>

                        </div>

                    </form>

                    <?php if (!empty($alert_type)): ?>

                        <script>

                            Swal.fire({

                                icon: 
                                "<?= $alert_type; ?>",


                                title:
                                "<?= $alert_type == 'success'
                                    ? 'Success!'
                                    : 'Error!'; ?>",

                                text:
                                "<?= htmlspecialchars(
                                    $alert_message
                                ); ?>",

                                confirmButtonColor:
                                "#003DA5"

                            }).then(() => {

                                <?php if ($alert_type == "success"): ?>

                                    window.location.href =
                                    "applicants.php";

                                <?php endif; ?>

                            });

                        </script>

                    <?php endif; ?>

                </div>

            </section>

        </main>

    </div>

</div>


<script src="../assets/js/hr.js"></script>


</body>

</html>