<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";

$page_title = "Edit Attendance Record";


if (isset($_POST['attendance_id'])) {

    $_SESSION['selected_attendance'] = (int) $_POST['attendance_id'];

}


if (!isset($_SESSION['selected_attendance'])) {

    header("Location: attendance_logs.php");
    exit;

}


$attendance_id = (int) $_SESSION['selected_attendance'];


$stmt = $conn->prepare("

    SELECT
        a.*,

        e.employee_code,
        e.first_name,
        e.middle_name,
        e.last_name,
        e.email,
        e.phone,

        d.department_name,

        p.position_name

    FROM attendance a

    INNER JOIN employees e
        ON a.employee_id = e.employee_id

    LEFT JOIN departments d
        ON e.department_id = d.department_id

    LEFT JOIN positions p
        ON e.position_id = p.position_id

    WHERE a.attendance_id = ?

");


$stmt->execute([$attendance_id]);

$attendance = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$attendance) {

    unset($_SESSION['selected_attendance']);

    header("Location: attendance_logs.php");
    exit;

}


if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    &&
    isset($_POST['update_attendance'])
) {

    requireCSRFToken("attendance_edit.php");


    $time_in = !empty($_POST['time_in'])
        ? $_POST['time_in']
        : null;


    $time_out = !empty($_POST['time_out'])
        ? $_POST['time_out']
        : null;


    $status = $_POST['status'] ?? "Present";


    $remarks = trim($_POST['remarks'] ?? "");


    if (!empty($time_in) && !empty($time_out)) {


        if ($time_out <= $time_in) {

            $_SESSION['attendance_error'] =
                "Time Out must be later than Time In.";


            header("Location: attendance_edit.php");
            exit;

        }

    }


    $update = $conn->prepare("

        UPDATE attendance

        SET

            time_in = ?,
            time_out = ?,
            status = ?,
            remarks = ?

        WHERE attendance_id = ?

    ");


    $success = $update->execute([

        $time_in,
        $time_out,
        $status,
        $remarks,
        $attendance_id

    ]);


    if ($success) {


        $_SESSION['attendance_success'] =
            "Attendance record updated successfully.";


        unset($_SESSION['selected_attendance']);


        header("Location: attendance_logs.php");
        exit;


    } else {


        $_SESSION['attendance_error'] =
            "Unable to update attendance record.";


        header("Location: attendance_edit.php");
        exit;

    }


}


$current_time_in = !empty($attendance['time_in'])
    ? date("H:i", strtotime($attendance['time_in']))
    : "";


$current_time_out = !empty($attendance['time_out'])
    ? date("H:i", strtotime($attendance['time_out']))
    : "";

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($page_title); ?> | Daily Cravings Foods Inc.</title>

    <link rel="stylesheet" href="../assets/css/global.css">
    
    <link rel="stylesheet" href="../assets/css/hr.css">

    <link rel="stylesheet" href="../assets/css/crud_hr.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

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

                            <i class="fa-solid fa-clock-rotate-left"></i>
                            Edit Attendance Record

                        </h1>

                        <p>Update employee attendance information.</p>

                    </div>

                    <div class="crud-header-actions">

                        <a 
                            href="attendance_logs.php"
                            class="crud-back-btn"
                        >

                            <i class="fa-solid fa-arrow-left"></i>
                            Back to Attendance History

                        </a>

                    </div>

                </div>

                <div class="crud-card">

                    <div class="crud-card-header">

                        <div class="crud-icon">

                            <i class="fa-solid fa-calendar-check"></i>

                        </div>

                        <div>

                            <h2>Attendance Record</h2>

                            <p>Attendance information update</p>

                        </div>

                    </div>

                    <div class="crud-section-title">

                        <h3>

                            <i class="fa-solid fa-calendar-days"></i>
                            Attendance Information

                        </h3>

                    </div>

                    <div class="crud-info">

                        <div class="crud-info-box">

                            <span>
                                Employee
                            </span>

                            <strong>

                                <?= htmlspecialchars(

                                    trim(

                                        $attendance['first_name']
                                        . " "
                                        .
                                        (
                                            !empty($attendance['middle_name'])
                                            ? $attendance['middle_name'] . " "
                                            : ""
                                        )
                                        .
                                        $attendance['last_name']

                                    )

                                ); ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Employee Code
                            </span>

                            <strong>

                                <?= htmlspecialchars(
                                    $attendance['employee_code']
                                ); ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Department
                            </span>

                            <strong>

                                <?= htmlspecialchars(
                                    $attendance['department_name'] ?? "N/A"
                                ); ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Position
                            </span>

                            <strong>

                                <?= htmlspecialchars(
                                    $attendance['position_name'] ?? "N/A"
                                ); ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Attendance Date
                            </span>

                            <strong>

                                <?= !empty($attendance['attendance_date'])

                                    ? date(
                                        "F d, Y",
                                        strtotime(
                                            $attendance['attendance_date']
                                        )
                                    )

                                    : "N/A";

                                ?>

                            </strong>

                        </div>

                        <div class="crud-info-box">

                            <span>
                                Created Date
                            </span>

                            <strong>

                                <?= !empty($attendance['created_at'])

                                    ? date(
                                        "F d, Y h:i A",
                                        strtotime(
                                            $attendance['created_at']
                                        )
                                    )

                                    : "N/A";

                                ?>

                            </strong>

                        </div>

                    </div>

                    <div class="crud-section-title">

                        <h3>

                            <i class="fa-solid fa-pen-to-square"></i>
                            Attendance Update

                        </h3>

                    </div>

                                                            <form method="POST" id="attendanceEditForm" novalidate>

                        <?php csrfField(); ?>

                        <input 
                            type="hidden"
                            name="update_attendance"
                            value="1"
                        >

                        <div class="crud-form-grid">

                            <div class="crud-form-group">

                                <label>

                                    <i class="fa-solid fa-right-to-bracket"></i>
                                    Time In

                                </label>

                                <input

                                    type="time"
                                    id="time_in"
                                    name="time_in"
                                    value="<?= $current_time_in; ?>"
                                >

                            </div>

                            <div class="crud-form-group">

                                <label>

                                    <i class="fa-solid fa-right-from-bracket"></i>
                                    Time Out

                                </label>

                                <input

                                    type="time"
                                    id="time_out"
                                    name="time_out"
                                    value="<?= $current_time_out; ?>"
                                >

                            </div>

                            <div class="crud-form-group">

                                <label>

                                    <i class="fa-solid fa-list-check"></i>
                                    Status

                                </label>

                                <select name="status">

                                    <option
                                        value="Present"
                                        <?= $attendance['status'] == "Present" ? "selected" : ""; ?>
                                    >

                                        Present
                                    </option>

                                    <option
                                        value="Absent"
                                        <?= $attendance['status'] == "Absent" ? "selected" : ""; ?>
                                    >

                                        Absent
                                    </option>

                                    <option
                                        value="Late"
                                        <?= $attendance['status'] == "Late" ? "selected" : ""; ?>
                                    >

                                        Late
                                    </option>

                                    <option
                                        value="Leave"
                                        <?= $attendance['status'] == "Leave" ? "selected" : ""; ?>
                                    >

                                        Leave
                                    </option>

                                </select>

                            </div>

                        </div>

                        <div class="crud-form-group">

                            <label>

                                <i class="fa-solid fa-comment"></i>
                                Remarks

                            </label>

                            <textarea

                                name="remarks"
                                rows="5"
                                placeholder="Enter attendance remarks..."

                            ><?= htmlspecialchars(
                                $attendance['remarks'] ?? ""
                            ); ?></textarea>

                        </div>

                        <div class="crud-actions">

                                                        <button

                                type="submit"
                                id="attendanceEditSaveBtn"
                                disabled
                                class="crud-btn crud-btn-primary"

                            >

                                <i class="fa-solid fa-floppy-disk"></i>
                                Save Changes

                            </button>

                        </div>

                    </form>

                </div>

            </section>

        </main>

    </div>

</div>


<?php if (!empty($_SESSION['attendance_success'])): ?>


<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {

        Swal.fire({

            icon: "success",

            title: "Success!",

            text: <?= json_encode(
                $_SESSION['attendance_success']
            ); ?>,

            confirmButtonColor: "#003DA5",

            confirmButtonText: "OK"

        });

    }
);

</script>


<?php unset($_SESSION['attendance_success']); ?>

<?php endif; ?>


<?php if (!empty($_SESSION['attendance_error'])): ?>


<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {

        Swal.fire({

            icon: "error",

            title: "Unable to Continue",

            text: <?= json_encode(
                $_SESSION['attendance_error']
            ); ?>,

            confirmButtonColor: "#003DA5",

            confirmButtonText: "OK"

        });


    }
);

</script>


<?php unset($_SESSION['attendance_error']); ?>

<?php endif; ?>


<script>

const timeIn = document.getElementById("time_in");

const timeOut = document.getElementById("time_out");

if (timeIn && timeOut) {

    timeIn.addEventListener(
        "change",
        function () {

            timeOut.min = this.value;

            if (
                timeOut.value &&
                timeOut.value <= this.value
            ) {

                timeOut.value = "";

                Swal.fire({

                    icon: "warning",

                    title: "Invalid Time",

                    text:
                    "Time Out must be later than Time In.",

                    confirmButtonColor: "#003DA5"

                });

            }

        }
    );

    timeOut.addEventListener(
        "change",
        function () {

            if (
                timeIn.value &&
                this.value <= timeIn.value
            ) {

                this.value = "";

                Swal.fire({

                    icon: "warning",

                    title: "Invalid Time",

                    text:
                    "Time Out must be later than Time In.",

                    confirmButtonColor: "#003DA5"

                });

            }

        }
    );

}

</script>


<script>
(function () {

    const form = document.getElementById("attendanceEditForm");
    const saveBtn = document.getElementById("attendanceEditSaveBtn");
    const initialState = new URLSearchParams(new FormData(form)).toString();

    function checkForChanges() {
        const currentState = new URLSearchParams(new FormData(form)).toString();
        saveBtn.disabled = (currentState === initialState);
    }

    form.addEventListener("input", checkForChanges);
    form.addEventListener("change", checkForChanges);

})();

document.getElementById("attendanceEditForm").addEventListener("submit", function (e) {

    e.preventDefault();

    const form = this;

    Swal.fire({
        icon: "question",
        title: "Save changes?",
        text: "This will update this attendance record.",
        showCancelButton: true,
        confirmButtonText: "Yes, Save",
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

<script src="../assets/js/hr.js"></script>


</body>


</html>