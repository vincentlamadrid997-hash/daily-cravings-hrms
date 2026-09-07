<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";

$page_title = "Edit Leave Request";


if (isset($_POST['leave_id'])) {

    $_SESSION['selected_leave'] = (int) $_POST['leave_id'];

}


if (!isset($_SESSION['selected_leave'])) {

    header("Location: leaves.php");
    exit;

}


$leave_id = (int) $_SESSION['selected_leave'];


$hrUsers = $conn->query("
    SELECT 
        user_id,
        full_name
    FROM users
    WHERE role = 'hr'
    AND status = 'active'
    ORDER BY full_name ASC
")->fetchAll(PDO::FETCH_ASSOC);


$stmt = $conn->prepare("

    SELECT

        lr.*,

        lt.leave_type_name,

        e.employee_code,
        e.first_name,
        e.middle_name,
        e.last_name,

        d.department_name,

        p.position_name,

        approver.full_name AS approved_by_name


    FROM leave_requests lr


    INNER JOIN employees e
        ON lr.employee_id = e.employee_id


    LEFT JOIN leave_types lt
        ON lr.leave_type_id = lt.leave_type_id


    LEFT JOIN departments d
        ON e.department_id = d.department_id


    LEFT JOIN positions p
        ON e.position_id = p.position_id


    LEFT JOIN users approver
        ON lr.approved_by = approver.user_id


    WHERE lr.leave_id = ?

");


$stmt->execute([$leave_id]);

$leave = $stmt->fetch(PDO::FETCH_ASSOC);



if (!$leave) {

    unset($_SESSION['selected_leave']);

    header("Location: leaves.php");
    exit;

}


if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST['update_leave'])
) {

    requireCSRFToken("leave_edit.php");


    $approved_by = !empty($_POST['approved_by'])
        ? (int) $_POST['approved_by']
        : null;


    $approved_at = !empty($_POST['approved_at'])
        ? $_POST['approved_at']
        : null;


    $remarks = trim($_POST['remarks']);


    $update = $conn->prepare("

        UPDATE leave_requests

        SET

            approved_by = ?,

            approved_at = ?,

            remarks = ?

        WHERE leave_id = ?

    ");


    $success = $update->execute([

        $approved_by,

        $approved_at,

        $remarks,

        $leave_id

    ]);


    if ($success) {


        $_SESSION['leave_success'] =
            "Leave approval information updated successfully.";


        header("Location: leaves.php");
        exit;


    } else {


        $_SESSION['leave_error'] =
            "Unable to update leave request.";


        header("Location: leave_edit.php");
        exit;

    }


}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">


    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars($page_title); ?> |
        Daily Cravings Foods Inc.
    </title>

    <link
        rel="stylesheet" href="../assets/css/global.css">

    <link
        rel="stylesheet" href="../assets/css/hr.css">

    <link
        rel="stylesheet" href="../assets/css/crud_hr.css">

    <link
        rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

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
                <i class="fa-solid fa-calendar-pen"></i>
                Edit Leave Request
            </h1>

            <p>Update leave approval information only.</p>

        </div>

        <div class="crud-header-actions">

            <a 
                href="leaves.php"
                class="crud-back-btn"
            >

                <i class="fa-solid fa-arrow-left"></i>
                Back to Leave Requests

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
                        $leave['leave_type_name'] ?? "Leave Request"
                    ); ?>

                </h2>

                <p>Approval Information Update</p>

            </div>

        </div>

        <div class="crud-section-title">

            <h3>

                <i class="fa-solid fa-calendar-days"></i>
                Leave Information

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

                            $leave['first_name']
                            . " "
                            .
                            (
                                !empty($leave['middle_name'])
                                ? $leave['middle_name'] . " "
                                : ""
                            )
                            .
                            $leave['last_name']

                        )

                    ); ?>

                </strong>

            </div>

            <div class="crud-info-box">

                <span>
                    Leave Type
                </span>

                <strong>

                    <?= htmlspecialchars(
                        $leave['leave_type_name'] ?? "N/A"
                    ); ?>

                </strong>

            </div>

            <div class="crud-info-box">

                <span>
                    Department
                </span>

                <strong>

                    <?= htmlspecialchars(
                        $leave['department_name'] ?? "N/A"
                    ); ?>

                </strong>

            </div>

            <div class="crud-info-box">

                <span>
                    Position
                </span>

                <strong>

                    <?= htmlspecialchars(
                        $leave['position_name'] ?? "N/A"
                    ); ?>

                </strong>

            </div>

            <div class="crud-info-box">

                <span>
                    Leave Period
                </span>

                <strong>

                    <?= !empty($leave['start_date'])

                        ? date(
                            "F d, Y",
                            strtotime($leave['start_date'])
                        )

                        : "N/A";

                    ?>

                    -

                    <?= !empty($leave['end_date'])

                        ? date(
                            "F d, Y",
                            strtotime($leave['end_date'])
                        )

                        : "N/A";

                    ?>

                </strong>

            </div>

            <div class="crud-info-box">

                <span>
                    Current Status
                </span>

                <strong>

                    <span 
                        class="hr-leave-status <?= strtolower($leave['status']); ?>"
                    >

                        <?php if ($leave['status'] === "Pending"): ?>

                            <i class="fa-solid fa-clock"></i>

                        <?php elseif ($leave['status'] === "Approved"): ?>

                            <i class="fa-solid fa-circle-check"></i>

                        <?php else: ?>

                            <i class="fa-solid fa-circle-xmark"></i>

                        <?php endif; ?>

                        <?= htmlspecialchars($leave['status']); ?>

                    </span>

                </strong>

            </div>

            <div 
                class="crud-info-box"
                style="grid-column:1/-1;"
            >

                <span>
                    Reason
                </span>

                <strong>

                    <?= !empty($leave['reason'])

                        ? nl2br(
                            htmlspecialchars($leave['reason'])
                        )

                        : "No reason provided";

                    ?>

                </strong>

            </div>

        </div>

        <div class="crud-section-title">

            <h3>

                <i class="fa-solid fa-user-check"></i>
                Approval Information

            </h3>

        </div>

                        <form method="POST" id="leaveEditForm" novalidate>

            <?php csrfField(); ?>

            <input
                type="hidden"
                name="update_leave"
                value="1"
            >

            <div class="crud-form-grid">

                <div class="crud-form-group">

                    <label>

                        <i class="fa-solid fa-user-check"></i>
                        Approved By

                    </label>

                    <select name="approved_by">

                        <option value="">
                            Not yet approved

                        </option>

                        <?php foreach ($hrUsers as $hr): ?>

                            <option

                                value="<?= $hr['user_id']; ?>"

                                <?= 
                                    ($leave['approved_by'] == $hr['user_id'])
                                    ? "selected"
                                    : ""
                                ?>

                            >

                                <?= htmlspecialchars(
                                    $hr['full_name']
                                ); ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div class="crud-form-group">

                    <label>

                        <i class="fa-solid fa-calendar-check"></i>
                        Approval Date

                    </label>

                    <input

                        type="datetime-local"
                        name="approved_at"
                        value="<?= 

                            !empty($leave['approved_at'])

                            ? date(
                                "Y-m-d\TH:i",
                                strtotime($leave['approved_at'])
                            )

                            : ""

                        ?>"

                    >

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
                    placeholder="Enter remarks or notes..."

                ><?= htmlspecialchars(
                    $leave['remarks'] ?? ""
                ); ?></textarea>

            </div>

            <div class="crud-actions">

                                <button

                    type="submit"
                    id="leaveEditSaveBtn"
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


<?php if (!empty($_SESSION['leave_success'])): ?>

<script>

document.addEventListener("DOMContentLoaded", function () {

    Swal.fire({

        icon: "success",

        title: "Success!",

        text: <?= json_encode($_SESSION['leave_success']); ?>,

        confirmButtonColor: "#003DA5",

        confirmButtonText: "OK"

    });

});

</script>


<?php unset($_SESSION['leave_success']); ?>

<?php endif; ?>


<?php if (!empty($_SESSION['leave_error'])): ?>

<script>

document.addEventListener("DOMContentLoaded", function () {

    Swal.fire({

        icon: "error",

        title: "Unable to Continue",

        text: <?= json_encode($_SESSION['leave_error']); ?>,

        confirmButtonColor: "#003DA5",

        confirmButtonText: "OK"

    });

});

</script>


<?php unset($_SESSION['leave_error']); ?>

<?php endif; ?>


<script>
(function () {

    const form = document.getElementById("leaveEditForm");
    const saveBtn = document.getElementById("leaveEditSaveBtn");
    const initialState = new URLSearchParams(new FormData(form)).toString();

    function checkForChanges() {
        const currentState = new URLSearchParams(new FormData(form)).toString();
        saveBtn.disabled = (currentState === initialState);
    }

    form.addEventListener("input", checkForChanges);
    form.addEventListener("change", checkForChanges);

})();

document.getElementById("leaveEditForm").addEventListener("submit", function (e) {

    e.preventDefault();

    const form = this;

    Swal.fire({
        icon: "question",
        title: "Save changes?",
        text: "This will update this leave request's record.",
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