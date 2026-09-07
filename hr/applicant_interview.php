<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";


$page_title = "Schedule Interview";


if (isset($_POST['application_id'])) {

    $_SESSION['selected_application'] = (int) $_POST['application_id'];

}


if (!isset($_SESSION['selected_application'])) {

    header("Location: applicants.php");
    exit;

}


$application_id = (int) $_SESSION['selected_application'];


$stmt = $conn->prepare("

    SELECT

        a.*,

        jp.job_title


    FROM applications a


    LEFT JOIN job_postings jp

        ON a.job_id = jp.job_id


    WHERE a.application_id = ?


    LIMIT 1

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


$interview_stage = null;


switch ($applicant['status']) {


    case "Shortlisted":


        $interview_stage = "Initial";


    break;


    case "Initial Interview":


        $interview_stage = "Final";


    break;


    case "Final Interview":

        $interview_stage = null;

    break;


    case "Accepted":

        $interview_stage = null;

    break;


    case "Rejected":

        $interview_stage = null;

    break;


    default:

        $interview_stage = null;

    break;


}


$existingInterview = null;


if ($interview_stage !== null) {


    $check = $conn->prepare("

        SELECT *

        FROM interviews

        WHERE application_id = ?

        AND stage = ?

        LIMIT 1

    ");


    $check->execute([

        $application_id,

        $interview_stage

    ]);


    $existingInterview = $check->fetch(PDO::FETCH_ASSOC);


}


$alert = [

    "type" => "",

    "message" => ""

];


if (

    $_SERVER["REQUEST_METHOD"] === "POST"

    && isset($_POST['schedule_interview'])

) {

    requireCSRFToken("applicant_interview.php");


    $interview_date = $_POST['interview_date'] ?? "";

    $interview_time = $_POST['interview_time'] ?? "";

    $interviewer = trim($_POST['interviewer'] ?? "");


    if (!$interview_stage) {


        $alert["type"] = "error";

        $alert["message"] =
        "Applicant is no longer eligible for interview scheduling.";


    } elseif ($existingInterview) {


        $alert["type"] = "warning";

        $alert["message"] =
        "This interview stage is already scheduled.";


    } elseif (

        empty($interview_date)

        ||

        empty($interview_time)

        ||

        empty($interviewer)

    ) {


        $alert["type"] = "warning";

        $alert["message"] =
        "Please complete all interview details.";


    } elseif ($interview_date < date("Y-m-d")) {


        $alert["type"] = "error";

        $alert["message"] =
        "Interview date cannot be earlier than today.";


    } else {


        try {


            $conn->beginTransaction();

            $insert = $conn->prepare("

                INSERT INTO interviews

                (

                    application_id,

                    interview_date,

                    interview_time,

                    stage,

                    interviewer,

                    status

                )


                VALUES

                (

                    ?, ?, ?, ?, ?, 'Scheduled'

                )

            ");


            $insert->execute([


                $application_id,

                $interview_date,

                $interview_time,

                $interview_stage,

                $interviewer


            ]);


            if ($interview_stage === "Initial") {


                $new_status = "Initial Interview";


            } else {


                $new_status = "Final Interview";


            }


            $update = $conn->prepare("

                UPDATE applications

                SET status = ?

                WHERE application_id = ?

            ");


            $update->execute([


                $new_status,

                $application_id


            ]);


            $conn->commit();


            $alert["type"] = "success";

            $alert["message"] =
            "Interview scheduled successfully.";


        } catch(Exception $e) {


            if($conn->inTransaction()){

                $conn->rollBack();

            }


            $alert["type"] = "error";

            $alert["message"] =
            "Failed to schedule interview.";


        }


    }


}

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

    <link rel="stylesheet" href="../assets/css/crud_hr.css">


    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">


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
                            <i class="fa-solid fa-calendar-check"></i>
                            Schedule Interview
                        </h1>

                        <p>Manage applicant interview schedule and recruitment stage.</p>

                    </div>

                    <div class="crud-header-actions">

                        <a href="applicants.php"
                           class="crud-back-btn">

                            <i class="fa-solid fa-arrow-left"></i>
                            Back to Applicants

                        </a>

                    </div>

                </div>

                <div class="crud-card">

                    <div class="crud-card-header">

                        <div class="crud-icon">

                            <i class="fa-solid fa-user-tie"></i>

                        </div>

                        <div>

                            <h2>

                                <?= htmlspecialchars(
                                    $applicant['first_name'] . " " .
                                    $applicant['last_name']
                                ); ?>

                            </h2>

                            <p>Interview Scheduling Information</p>

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
                                Applicant Name
                            </span>

                            <strong>

                                <?= htmlspecialchars(
                                    $applicant['first_name'] . " " .
                                    $applicant['last_name']
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
                                    $applicant['status']
                                ); ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Interview Stage
                            </span>

                            <strong>

                                <?php if ($interview_stage): ?>

                                    <?= htmlspecialchars($interview_stage); ?>
                                    Interview

                                <?php else: ?>

                                    Not Available

                                <?php endif; ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Application Date
                            </span>

                            <strong>

                                <?= date(
                                    "F d, Y",
                                    strtotime($applicant['created_at'])
                                ); ?>

                            </strong>

                        </div>


                    </div>

                                                            <form method="POST" id="interviewForm" novalidate>

                        <?php csrfField(); ?>

                        <input 
                            type="hidden"
                            name="application_id"
                            value="<?= $application_id; ?>"
                        >

                        <div class="crud-form-grid">

                            <div class="crud-group">

                                <label>

                                    <i class="fa-solid fa-layer-group"></i>

                                    Interview Stage

                                </label>

                                <input

                                    type="text"
                                    class="crud-control"

                                    value="<?= htmlspecialchars(
                                        $interview_stage
                                        ? $interview_stage . " Interview"
                                        : "Not Available"
                                    ); ?>"

                                    readonly
                                >

                            </div>

                            <div class="crud-group">

                                <label>

                                    <i class="fa-solid fa-calendar"></i>
                                    Interview Date

                                </label>

                                <input

                                    type="date"
                                    name="interview_date"
                                    class="crud-control"

                                    min="<?= date('Y-m-d'); ?>"

                                    <?= (!$interview_stage || $existingInterview)
                                        ? "disabled"
                                        : "";
                                    ?>

                                    <?= (!$interview_stage || $existingInterview)
                                        ? ""
                                        : "required";
                                    ?>
                                >

                            </div>

                            <div class="crud-group">

                                <label>

                                    <i class="fa-solid fa-clock"></i>

                                    Interview Time

                                </label>

                                <input

                                    type="time"
                                    name="interview_time"
                                    class="crud-control"

                                    <?= (!$interview_stage || $existingInterview)
                                        ? "disabled"
                                        : "";
                                    ?>

                                    <?= (!$interview_stage || $existingInterview)
                                        ? ""
                                        : "required";
                                    ?>
                                >

                            </div>

                            <div class="crud-group">

                                <label>

                                    <i class="fa-solid fa-user-tie"></i>
                                    Interviewer

                                </label>

                                <input

                                    type="text"
                                    name="interviewer"
                                    class="crud-control"
                                    placeholder="Enter interviewer name"

                                    <?= (!$interview_stage || $existingInterview)
                                        ? "disabled"
                                        : "";
                                    ?>

                                    <?= (!$interview_stage || $existingInterview)
                                        ? ""
                                        : "required";
                                    ?>
                                >

                            </div>

                        </div>

                        <div class="crud-actions">

                            <?php if (!$interview_stage): ?>

                                <button

                                    type="button"
                                    class="crud-btn crud-btn-secondary"
                                    disabled

                                >

                                    <i class="fa-solid fa-ban"></i>
                                    Not Eligible

                                </button>

                            <?php elseif ($existingInterview): ?>


                                <button

                                    type="button"
                                    class="crud-btn crud-btn-secondary"
                                    disabled

                                >

                                    <i class="fa-solid fa-calendar-xmark"></i>
                                    Interview Already Scheduled

                                </button>

                            <?php else: ?>

                                <button

                                    type="submit"
                                    name="schedule_interview"
                                    class="crud-btn crud-btn-primary"

                                >

                                    <i class="fa-solid fa-calendar-plus"></i>
                                    Schedule Interview

                                </button>

                            <?php endif; ?>

                        </div>

                    </form>

                </div>

            </section>

        </main>

    </div>

</div>

<?php if (!empty($alert["message"])): ?>

<script>

document.addEventListener("DOMContentLoaded", function () {

    Swal.fire({

        icon:
        "<?= $alert['type']; ?>",

        title:
        "<?= $alert['type'] === 'success'
            ? 'Success!'
            : 'Unable to Continue';
        ?>",

        text:
        "<?= htmlspecialchars($alert['message']); ?>",

        confirmButtonColor:"#003DA5",

        confirmButtonText:"OK",

        allowOutsideClick:false,

        allowEscapeKey:false

    }).then((result)=>{

        <?php if ($alert['type'] === "success"): ?>


            if(result.isConfirmed){


                window.location.href = "applicants.php";

            }

        <?php endif; ?>

    });

});

document.getElementById("interviewForm").addEventListener("submit", function (e) {

    e.preventDefault();

    const form = this;

    Swal.fire({

        icon: "question",
        title: "Schedule this interview?",
        text: "This will save the interview date, time, and interviewer for this applicant.",
        showCancelButton: true,
        confirmButtonText: "Yes, Schedule",
        cancelButtonText: "Cancel",
        confirmButtonColor: "#003DA5",
        reverseButtons: true

    }).then(function (result) {

        if (result.isConfirmed) {
            form.submit();
        }

    });

}); 

</script>


<?php endif; ?>


<script src="../assets/js/hr.js"></script>


</body>

</html>