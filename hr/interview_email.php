<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/mail.php";
require_once "../config/csrf.php";


$page_title = "Send Interview Email";


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
    isset($_POST['send_email'])
) {

    requireCSRFToken("interview_email.php");


    if (
        ($interview['email_sent'] ?? "No") === "Yes"
    ) {

        $alert["type"] = "error";

        $alert["message"] =
            "Interview invitation email was already sent.";

    }


    elseif (
        $interview['status'] !== "Scheduled"
    ) {

        $alert["type"] = "error";

        $alert["message"] =
            "Only scheduled interviews can send email.";

    }


    elseif (
        empty($interview['email'])
    ) {

        $alert["type"] = "error";

        $alert["message"] =
            "Applicant email address is missing.";

    }


    else {


        $applicant_name =
            $interview['first_name']
            . " "
            . $interview['last_name'];



        $date = date(
            "F d, Y",
            strtotime($interview['interview_date'])
        );



        $time = date(
            "h:i A",
            strtotime($interview['interview_time'])
        );


        $sent = sendInterviewEmail(

            $interview['email'],

            $applicant_name,

            $interview['job_title'] ?? "N/A",

            $interview['stage'],

            $date,

            $time,

            $interview['interviewer']

        );


        if ($sent) {


            $updateMail = $conn->prepare("

                UPDATE interviews

                SET
                    email_sent = 'Yes',
                    email_sent_at = NOW()

                WHERE interview_id = ?

            ");


            $updateMail->execute([
                $interview_id
            ]);


            $alert["type"] = "success";

            $alert["message"] =
                "Interview invitation email sent successfully.";


            $interview['email_sent'] = "Yes";

            $interview['email_sent_at'] =
                date("Y-m-d H:i:s");


        }


        else {


            $alert["type"] = "error";

            $alert["message"] =
                "Unable to send email. Please check SMTP configuration.";

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
                            <i class="fa-solid fa-envelope"></i>
                            Send Interview Email
                        </h1>

                        <p>Send interview invitation to applicant.</p>

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

                            <i class="fa-solid fa-paper-plane"></i>

                        </div>

                        <div>

                            <h2>

                                <?= htmlspecialchars(
                                    $interview['first_name']
                                    . " "
                                    . $interview['last_name']
                                ); ?>

                            </h2>

                            <p>Interview Email Confirmation</p>

                        </div>

                    </div>

                    <div class="crud-info">

                        <div class="crud-info-box">

                            <span>Email Address</span>

                            <strong>

                                <?= htmlspecialchars(
                                    $interview['email'] ?? "N/A"
                                ); ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>Position</span>

                            <strong>

                                <?= htmlspecialchars(
                                    $interview['job_title'] ?? "N/A"
                                ); ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>Interview Stage</span>

                            <strong>

                                <?= htmlspecialchars(
                                    $interview['stage']
                                ); ?>

                                Interview

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>Interview Status</span>

                            <strong>

                                <?= htmlspecialchars(
                                    $interview['status']
                                ); ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>Email Status</span>

                            <strong>

                                <?= htmlspecialchars(
                                    $interview['email_sent'] ?? "No"
                                ); ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>Email Sent Date</span>

                            <strong>

                                <?php if (!empty($interview['email_sent_at'])): ?>

                                    <?= date(
                                        "F d, Y h:i A",
                                        strtotime($interview['email_sent_at'])
                                    ); ?>

                                <?php else: ?>

                                    Not Sent

                                <?php endif; ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>Interview Date</span>

                            <strong>

                                <?= date(
                                    "F d, Y",
                                    strtotime($interview['interview_date'])
                                ); ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>Interview Time</span>

                            <strong>

                                <?= date(
                                    "h:i A",
                                    strtotime($interview['interview_time'])
                                ); ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>Interviewer</span>

                            <strong>

                                <?= htmlspecialchars(
                                    $interview['interviewer']
                                ); ?>

                            </strong>

                        </div>

                    </div>

                 <form method="POST" id="emailForm" novalidate>

                        <?php csrfField(); ?>

                        <input
                            type="hidden"
                            name="interview_id"
                            value="<?= $interview_id; ?>"
                        >

                        <div class="crud-actions">

                            <?php if (
                                $interview['status'] === "Scheduled"
                                &&
                                ($interview['email_sent'] ?? "No") !== "Yes"
                            ): ?>

                                <button
                                    type="submit"
                                    name="send_email"
                                    class="crud-btn crud-btn-primary">

                                    <i class="fa-solid fa-paper-plane"></i>
                                    Send Email

                                </button>

                            <?php elseif (
                                ($interview['email_sent'] ?? "No") === "Yes"
                            ): ?>

                                <button
                                    type="button"
                                    class="crud-btn crud-btn-secondary"
                                    disabled>

                                    <i class="fa-solid fa-circle-check"></i>

                                    Email Already Sent

                                </button>

                            <?php else: ?>

                                <button
                                    type="button"
                                    class="crud-btn crud-btn-secondary"
                                    disabled>

                                    <i class="fa-solid fa-ban"></i>

                                    Email Not Available

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

document.addEventListener(
    "DOMContentLoaded",
    function () {

        Swal.fire({

            icon:
                "<?= $alert['type']; ?>",


            title:
                "<?= $alert['type'] === 'success'
                    ? 'Email Sent!'
                    : 'Unable to Send'; ?>",


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

                    window.location.href =
                        "interviews.php";

                }


            <?php endif; ?>


        });


    }
);

document.getElementById("emailForm").addEventListener("submit", function (e) {

    e.preventDefault();

    const form = this;

    Swal.fire({

        icon: "question",
        title: "Send this interview email?",
        text: "The applicant will receive an email with their interview details.",
        showCancelButton: true,
        confirmButtonText: "Yes, Send",
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