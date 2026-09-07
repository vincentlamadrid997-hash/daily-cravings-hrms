<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";

$page_title = "Update Interview";   


if (isset($_POST['interview_id'])) {

    $_SESSION['selected_interview'] = (int) $_POST['interview_id'];

}


if (!isset($_SESSION['selected_interview'])) {

    header("Location: interviews.php");
    exit;

}


$interview_id = (int) $_SESSION['selected_interview'];


$alert = [
    "type" => "",
    "message" => ""
];


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


$status_flow = [

    "Scheduled" => [
        "Completed",
        "Cancelled"
    ],

    "Completed" => [],

    "Cancelled" => []

];


if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    &&
    isset($_POST['update_interview'])
) {

    requireCSRFToken("interview_update.php");


    $date = $_POST['interview_date'] ?? "";

    $time = $_POST['interview_time'] ?? "";

    $interviewer = trim(
        $_POST['interviewer'] ?? ""
    );

    $status = $_POST['status'] ?? "";


    if (
        $interview['status'] === "Completed"
        ||
        $interview['status'] === "Cancelled"
    ) {

        $alert["type"] = "error";

        $alert["message"] =
            "This interview is already closed and cannot be modified.";

    }


    elseif (
        empty($date)
        ||
        empty($time)
        ||
        empty($interviewer)
    ) {

        $alert["type"] = "warning";

        $alert["message"] =
            "Please complete all interview information.";

    }


    elseif ($date < date("Y-m-d")) {

        $alert["type"] = "error";

        $alert["message"] =
            "Interview date cannot be earlier than today.";

    }


    else {


        $current_status = $interview['status'];

        $allowed_status =
            $status_flow[$current_status] ?? [];



        if ($status === $current_status) {


            $alert["type"] = "warning";

            $alert["message"] =
                "No changes detected in interview status.";

        }


        elseif (
            !in_array(
                $status,
                $allowed_status,
                true
            )
        ) {


            $alert["type"] = "error";

            $alert["message"] =
                "Invalid interview status flow.";

        }


        else {


            try {


                $conn->beginTransaction();


                $update = $conn->prepare("
                    UPDATE interviews

                    SET
                        interview_date = ?,
                        interview_time = ?,
                        interviewer = ?,
                        status = ?

                    WHERE interview_id = ?
                ");


                $update->execute([

                    $date,

                    $time,

                    $interviewer,

                    $status,

                    $interview_id

                ]);


                $conn->commit();


                $alert["type"] = "success";

                $alert["message"] =
                    "Interview updated successfully.";


                $interview['interview_date'] = $date;

                $interview['interview_time'] = $time;

                $interview['interviewer'] = $interviewer;

                $interview['status'] = $status;


            } catch (Exception $e) {


                $conn->rollBack();


                $alert["type"] = "error";

                $alert["message"] =
                    "Something went wrong while updating interview.";

            }

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
        <?= htmlspecialchars($page_title); ?> |
        Daily Cravings Foods Inc.
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
                            <i class="fa-solid fa-pen-to-square"></i>
                            Update Interview
                        </h1>

                        <p>Modify interview schedule information.</p>

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

                            <i class="fa-solid fa-calendar-check"></i>

                        </div>

                        <div>

                            <h2>
                                <?= htmlspecialchars(
                                    $interview['first_name']
                                    . " "
                                    . $interview['last_name']
                                ); ?>
                            </h2>

                            <p>Interview Schedule Update</p>

                        </div>

                    </div>

                    <div class="crud-info">

                        <div class="crud-info-box">

                            <span>
                                Position Applied
                            </span>

                            <strong>
                                <?= htmlspecialchars(
                                    $interview['job_title'] ?? "N/A"
                                ); ?>
                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Email Address
                            </span>

                            <strong>
                                <?= htmlspecialchars(
                                    $interview['email']
                                ); ?>
                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Current Interview Status
                            </span>

                            <strong>
                                <?= htmlspecialchars(
                                    $interview['status']
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

                value="<?= htmlspecialchars(
                    $interview['interview_date']
                ); ?>"

                <?= (
                    $interview['status'] !== "Scheduled"
                )
                    ? "disabled"
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

                value="<?= htmlspecialchars(
                    $interview['interview_time']
                ); ?>"

                <?= (
                    $interview['status'] !== "Scheduled"
                )
                    ? "disabled"
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

                value="<?= htmlspecialchars(
                    $interview['interviewer']
                ); ?>"

                <?= (
                    $interview['status'] !== "Scheduled"
                )
                    ? "disabled"
                    : "required";
                ?>
            >

        </div>

        <div class="crud-group">

            <label>

                <i class="fa-solid fa-list-check"></i>
                Interview Status

            </label>

            <select

                name="status"
                class="crud-control"
                required

            >

                <option value="<?= htmlspecialchars(
                    $interview['status']
                ); ?>">

                    <?= htmlspecialchars(
                        $interview['status']
                    ); ?>

                </option>

                <?php foreach(
                    $status_flow[$interview['status']] ?? []

                    as $next_status
                ): ?>

                    <option

                        value="<?= htmlspecialchars(
                            $next_status
                        ); ?>"

                    >

                        <?= htmlspecialchars(
                            $next_status
                        ); ?>

                    </option>

                <?php endforeach; ?>

            </select>

            <?php if(
                $interview['status'] !== "Scheduled"
            ): ?>

                <small class="crud-note">

                    <i class="fa-solid fa-lock"></i>

                    This interview is already closed.
                    No further changes are allowed.

                </small>

            <?php endif; ?>

        </div>

    </div>

    <div class="crud-actions">

        <?php if(
            $interview['status'] === "Scheduled"
        ): ?>

            <button

                type="submit"
                name="update_interview"
                class="crud-btn crud-btn-primary"

            >

                <i class="fa-solid fa-save"></i>
                Save Changes

            </button>

        <?php else: ?>

            <button

                type="button"
                class="crud-btn crud-btn-secondary"
                disabled

            >

                <i class="fa-solid fa-lock"></i>
                Interview Locked

            </button>

        <?php endif; ?>

    </div>

</form>

            </div>

        </section>

    </main>

    </div>

</div>

<?php if(!empty($alert["message"])): ?>

<script>

document.addEventListener(
    "DOMContentLoaded",
    function(){

        Swal.fire({

            icon:
            "<?= $alert['type']; ?>",

            title:

            "<?= $alert['type'] === 'success'

                ? 'Success!'

                : 'Unable to Continue';

            ?>",

            text:

            "<?= htmlspecialchars(
                $alert['message']
            ); ?>",

            confirmButtonColor:"#003DA5",

            confirmButtonText:"OK",

            allowOutsideClick:false,

            allowEscapeKey:false

        })


        .then((result)=>{

            <?php if(
                $alert['type'] === "success"
            ): ?>

                if(result.isConfirmed){

                    window.location.href =
                    "interviews.php";

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