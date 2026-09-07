<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/mail.php";
require_once "../config/csrf.php";


$page_title = "Reschedule Interview";


if (isset($_POST['interview_id'])) {

    $_SESSION['selected_interview'] = (int) $_POST['interview_id'];

}


if (!isset($_SESSION['selected_interview'])) {

    header("Location: interviews.php");
    exit;

}


$interview_id = (int) $_SESSION['selected_interview'];


$stmt = $conn->prepare("

    SELECT

        i.*,

        a.first_name,

        a.last_name,

        a.email,

        jp.job_title


    FROM interviews i


    LEFT JOIN applications a

        ON i.application_id = a.application_id


    LEFT JOIN job_postings jp

        ON a.job_id = jp.job_id


    WHERE i.interview_id = ?

");


$stmt->execute([

    $interview_id

]);


$interview = $stmt->fetch(PDO::FETCH_ASSOC);



if (!$interview) {

    unset($_SESSION['selected_interview']);

    header("Location: interviews.php");
    exit;

}


$alert = [

    "type" => "",

    "message" => ""

];


if (

    $_SERVER["REQUEST_METHOD"] === "POST"

    &&

    isset($_POST['reschedule'])

) {

    requireCSRFToken("interview_reschedule.php");


    $new_date = $_POST['interview_date'] ?? '';

    $new_time = $_POST['interview_time'] ?? '';

    $reason = trim($_POST['reschedule_reason'] ?? '');


    if (empty($new_date) || empty($new_time)) {


        $alert["type"] = "error";

        $alert["message"] =
            "Please select new interview date and time.";


    } elseif ($new_date < date("Y-m-d")) {


        $alert["type"] = "error";

        $alert["message"] =
            "Interview date cannot be in the past.";


    } elseif (empty($reason)) {


        $alert["type"] = "error";

        $alert["message"] =
            "Please provide reschedule reason.";


    } else {


        try {


            $conn->beginTransaction();


            $update = $conn->prepare("

                UPDATE interviews

                SET

                    previous_date = interview_date,

                    previous_time = interview_time,

                    interview_date = ?,

                    interview_time = ?,

                    reschedule_reason = ?,

                    status = 'Scheduled'


                WHERE interview_id = ?

            ");


            $update->execute([

                $new_date,

                $new_time,

                $reason,

                $interview_id

            ]);


            $conn->commit();



            $applicant_name =

                $interview['first_name']

                . " "

                . $interview['last_name'];



            $formatted_date = date(

                "F d, Y",

                strtotime($new_date)

            );


            $formatted_time = date(

                "h:i A",

                strtotime($new_time)

            );


            $emailSent = sendRescheduleEmail(

                $interview['email'],

                $applicant_name,

                $interview['job_title'] ?? "N/A",

                $formatted_date,

                $formatted_time,

                $interview['interviewer'],

                $reason

            );


        
            if ($emailSent) {


                $alert["type"] = "success";

                $alert["message"] =

                    "Interview rescheduled successfully and applicant notified.";


            } else {


                $alert["type"] = "success";

                $alert["message"] =

                    "Interview rescheduled successfully but email notification failed.";


            }


            $interview['previous_date'] =

                $interview['interview_date'];



            $interview['previous_time'] =

                $interview['interview_time'];



            $interview['interview_date'] =

                $new_date;



            $interview['interview_time'] =

                $new_time;



            $interview['reschedule_reason'] =

                $reason;



        } catch (Exception $e) {


            if ($conn->inTransaction()) {

                $conn->rollBack();

            }


            $alert["type"] = "error";

            $alert["message"] =

                "Unable to reschedule interview.";


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

                            <i class="fa-solid fa-calendar-days"></i>
                            Reschedule Interview

                        </h1>

                        <p>Update applicant interview schedule.</p>

                    </div>

                    <div class="crud-header-actions">

                        <a href="interviews.php"
                           class="crud-back-btn">

                            <i class="fa-solid fa-arrow-left"></i>
                            Back to Interviews

                        </a>

                    </div>

                </div>

                <div class="crud-card">

                    <div class="crud-card-header">

                        <div class="crud-icon">

                            <i class="fa-solid fa-calendar-days"></i>

                        </div>

                        <div>

                            <h2>

                                <?= htmlspecialchars(

                                    $interview['first_name']

                                    . " "

                                    . $interview['last_name']

                                ); ?>

                            </h2>

                            <p>Interview Reschedule Form</p>

                        </div>

                    </div>

                    <div class="crud-info">

                        <div class="crud-info-box">

                            <span>
                                Applicant Email
                            </span>

                            <strong>

                                <?= htmlspecialchars(

                                    $interview['email'] ?? "N/A"

                                ); ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Position
                            </span>

                            <strong>

                                <?= htmlspecialchars(

                                    $interview['job_title'] ?? "N/A"

                                ); ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Interview Stage
                            </span>

                            <strong>

                                <?= htmlspecialchars(

                                    $interview['stage']

                                ); ?>

                                Interview

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Current Interview Date
                            </span>

                            <strong>

                                <?= date(

                                    "F d, Y",

                                    strtotime(
                                        $interview['interview_date']
                                    )

                                ); ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Current Interview Time
                            </span>

                            <strong>

                                <?= date(

                                    "h:i A",

                                    strtotime(
                                        $interview['interview_time']
                                    )

                                ); ?>

                            </strong>

                        </div>

                        <?php if (!empty($interview['previous_date'])): ?>

                            <div class="crud-info-box">

                                <span>
                                    Previous Interview Date
                                </span>

                                <strong>

                                    <?= date(

                                        "F d, Y",

                                        strtotime(
                                            $interview['previous_date']
                                        )

                                    ); ?>

                                </strong>

                            </div>

                        <?php endif; ?>

                        <?php if (!empty($interview['previous_time'])): ?>

                            <div class="crud-info-box">

                                <span>
                                    Previous Interview Time
                                </span>

                                <strong>

                                    <?= date(

                                        "h:i A",

                                        strtotime(
                                            $interview['previous_time']
                                        )

                                    ); ?>

                                </strong>

                            </div>

                        <?php endif; ?>

                        <div class="crud-info-box">

                            <span>
                                Interviewer
                            </span>

                            <strong>

                                <?= htmlspecialchars(

                                    $interview['interviewer']

                                ); ?>

                            </strong>

                        </div>


                    </div>

                    <form method="POST">

                        <?php csrfField(); ?>

                        <input

                            type="hidden"
                            name="interview_id"
                            value="<?= $interview_id; ?>"
                        >

                        <div class="crud-form-grid">

                            <div class="crud-form-group">

                                <label>

                                    <i class="fa-solid fa-calendar"></i>
                                    New Interview Date

                                </label>

                                <input

                                    type="date"
                                    name="interview_date"
                                    min="<?= date('Y-m-d'); ?>"
                                    required
                                >

                            </div>

                            <div class="crud-form-group">

                                <label>

                                    <i class="fa-solid fa-clock"></i>
                                    New Interview Time

                                </label>

                                <input

                                    type="time"
                                    name="interview_time"
                                    required
                                >

                            </div>

                        </div>

                        <div class="crud-form-group">

                            <label>

                                <i class="fa-solid fa-comment"></i>
                                Reschedule Reason

                            </label>

                            <textarea

                                name="reschedule_reason"
                                rows="4"
                                placeholder="Enter reason for rescheduling..."
                                required

                            ></textarea>

                        </div>

                        <div class="crud-actions">

                            <button

                                type="submit"
                                name="reschedule"
                                class="crud-btn crud-btn-primary"

                            >

                                <i class="fa-solid fa-calendar-check"></i>
                                Save Reschedule

                            </button>

                        </div>

                    </form>

                </div>

            </section>

        </main>

    </div>

</div>


<?php if (!empty($alert["message"])): ?>


<script>

document.addEventListener(

    "DOMContentLoaded",

    function () {

        Swal.fire({

            icon:

                "<?= $alert['type']; ?>",

            title:

                "<?= $alert['type'] === 'success'

                    ? 'Rescheduled!'

                    : 'Unable to Reschedule'; ?>",

            text:

                "<?= htmlspecialchars(

                    $alert['message'],

                    ENT_QUOTES,

                    'UTF-8'

                ); ?>",

            confirmButtonColor: "#003DA5",

            confirmButtonText: "OK",

            allowOutsideClick: false,

            allowEscapeKey: false

        }).then((result) => {

            <?php if ($alert["type"] === "success"): ?>

            if (result.isConfirmed) {

                window.location.href = "interviews.php";

            }

            <?php endif; ?>

        });

    }

);

</script>


<?php endif; ?>


<script src="../assets/js/hr.js"></script>


</body>

</html>