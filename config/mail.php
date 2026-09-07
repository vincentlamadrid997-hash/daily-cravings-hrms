<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../PHPMailer/src/Exception.php';
require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';
require_once __DIR__ . '/mail_credentials.php';


function createMailer()
{
    $mail = new PHPMailer(true);

    try {

        $mail->isSMTP();
        $mail->Host = MAIL_HOST;
        $mail->SMTPAuth = true;

        $mail->Username = MAIL_USERNAME;
        $mail->Password = MAIL_PASSWORD;

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;


        $mail->setFrom(
            MAIL_USERNAME,
            MAIL_FROM_NAME
        );

        $mail->isHTML(true);

        return $mail;

    } catch (Exception $e) {

        return null;

    }
}



function sendResetEmail($email, $reset_link)
{
    $mail = createMailer();

    if (!$mail) {
        return false;
    }


    try {

        $mail->addAddress($email);

        $mail->Subject = "Password Reset Request";


        $mail->Body = "

        <div style='font-family:Arial,sans-serif;'>

            <h2 style='color:#003DA5;'>
                Daily Cravings Foods Inc.
            </h2>

            <p>
                You requested to reset your HRMS password.
            </p>

            <br>

            <a href='$reset_link'
            style='
                background:#003DA5;
                color:white;
                padding:12px 20px;
                text-decoration:none;
                border-radius:5px;
                display:inline-block;
            '>
                Reset Password
            </a>

            <br><br>

            <p>
                This link will expire in 15 minutes.
            </p>

        </div>

        ";


        $mail->send();

        return true;


    } catch (Exception $e) {

        return false;

    }
}



function sendInterviewEmail(
    $email,
    $applicant_name,
    $position,
    $stage,
    $date,
    $time,
    $interviewer
) {

    $mail = createMailer();

    if (!$mail) {
        return false;
    }


    try {

        $mail->addAddress(
            $email,
            $applicant_name
        );


        $mail->Subject = 
            "Interview Invitation - Daily Cravings Foods Inc.";


        $mail->Body = "

        <div style='font-family:Arial,sans-serif;'>

            <h2 style='color:#003DA5;'>
                Daily Cravings Foods Inc.
            </h2>


            <p>
                Dear <b>$applicant_name</b>,
            </p>


            <p>
                We are pleased to inform you that you are scheduled for a
                <b>$stage Interview</b>.
            </p>


            <hr>


            <h3>
                Interview Details
            </h3>


            <p>
                <b>Position:</b> $position
            </p>

            <p>
                <b>Interview Stage:</b> $stage Interview
            </p>

            <p>
                <b>Date:</b> $date
            </p>

            <p>
                <b>Time:</b> $time
            </p>

            <p>
                <b>Interviewer:</b> $interviewer
            </p>


            <br>


            <p>
                Please be available on the scheduled date and time.
                We look forward to meeting you.
            </p>


            <br>


            <p>
                Regards,
                <br>
                <b>HR Department</b>
                <br>
                Daily Cravings Foods Inc.
            </p>


        </div>

        ";


        $mail->send();

                return true;


    } catch (Exception $e) {

        error_log("Interview email failed: " . $e->getMessage());

        return false;

    }
}



function sendRescheduleEmail(
    $email,
    $applicant_name,
    $position,
    $date,
    $time,
    $interviewer,
    $reason
) {

    $mail = createMailer();

    if (!$mail) {
        return false;
    }


    try {

        $mail->addAddress(
            $email,
            $applicant_name
        );


        $mail->Subject =
            "Interview Rescheduled - Daily Cravings Foods Inc.";


        $mail->Body = "

        <div style='font-family:Arial,sans-serif;'>

            <h2 style='color:#003DA5;'>
                Daily Cravings Foods Inc.
            </h2>


            <p>
                Dear <b>$applicant_name</b>,
            </p>


            <p>
                Your interview schedule has been successfully rescheduled.
            </p>


            <hr>


            <h3>
                Updated Interview Details
            </h3>


            <p>
                <b>Position:</b> $position
            </p>


            <p>
                <b>New Interview Date:</b> $date
            </p>


            <p>
                <b>New Interview Time:</b> $time
            </p>


            <p>
                <b>Interviewer:</b> $interviewer
            </p>


            <p>
                <b>Reason:</b> $reason
            </p>


            <br>


            <p>
                Please be available on the updated schedule.
                We apologize for any inconvenience.
            </p>


            <br>


            <p>
                Regards,
                <br>
                <b>HR Department</b>
                <br>
                Daily Cravings Foods Inc.
            </p>


        </div>

        ";


        $mail->send();

        return true;


    } catch (Exception $e) {

        return false;

    }

}

?>