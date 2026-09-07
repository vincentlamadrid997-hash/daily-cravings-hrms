<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";
require_once "../config/permissions.php";

requirePermission($conn, 'edit_positions', 'positions.php');

$page_title = "Edit Position";


if (isset($_POST['position_id'])) {

    $_SESSION['selected_position'] = (int) $_POST['position_id'];

}

if (!isset($_SESSION['selected_position'])) {

    header("Location: positions.php");
    exit;

}

$position_id = (int) $_SESSION['selected_position'];


$stmt = $conn->prepare("

    SELECT *

    FROM positions

    WHERE position_id = ?

");

$stmt->execute([
    $position_id
]);

$position = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$position) {

    unset($_SESSION['selected_position']);

    header("Location: positions.php");
    exit;

}


$departments = $conn->query("

    SELECT

        department_id,
        department_name

    FROM departments

    WHERE status = 'Active'

    ORDER BY department_name ASC

")->fetchAll(PDO::FETCH_ASSOC);


$alert = [

    "type" => "",
    "message" => ""

];


if (

    $_SERVER["REQUEST_METHOD"] === "POST"

    &&

    isset($_POST['update_position'])

) {

    requireCSRFToken("position_edit.php");

    $department_id = (int) ($_POST['department_id'] ?? 0);

    $position_name = trim($_POST['position_name'] ?? "");

    $description = trim($_POST['description'] ?? "");

    $status = $_POST['status'] ?? "Active";


    if (

        empty($department_id)

        ||

        empty($position_name)

    ) {

        $alert["type"] = "error";

        $alert["message"] =
        "Department and Position Name are required.";

    } else {


        $check = $conn->prepare("

            SELECT position_id

            FROM positions

            WHERE

                department_id = ?

                AND

                position_name = ?

                AND

                position_id != ?

            LIMIT 1

        ");

        $check->execute([

            $department_id,

            $position_name,

            $position_id

        ]);

        if ($check->fetch()) {

            $alert["type"] = "error";

            $alert["message"] =
            "Position already exists in this department.";

        } else {


            $update = $conn->prepare("

                UPDATE positions

                SET

                    department_id = ?,

                    position_name = ?,

                    description = ?,

                    status = ?

                WHERE position_id = ?

            ");

            $success = $update->execute([

                $department_id,

                $position_name,

                $description,

                $status,

                $position_id

            ]);

            if ($success) {

                $alert["type"] = "success";

                $alert["message"] =
                "Position updated successfully.";

                $stmt->execute([
                    $position_id
                ]);

                $position = $stmt->fetch(PDO::FETCH_ASSOC);

            } else {

                $alert["type"] = "error";

                $alert["message"] =
                "Unable to update position.";

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

                            <i class="fa-solid fa-briefcase"></i>
                            Edit Position

                        </h1>

                        <p>Update position information.</p>

                    </div>

                    <div class="crud-header-actions">

                        
                            href="positions.php"
                            class="crud-back-btn"
                        >

                            <i class="fa-solid fa-arrow-left"></i>
                            Back to Positions

                        </a>

                    </div>

                </div>

                <div class="crud-card">

                    <div class="crud-card-header">

                        <div class="crud-icon">

                            <i class="fa-solid fa-briefcase"></i>

                        </div>

                        <div>

                            <h2>

                                <?= htmlspecialchars($position['position_name']); ?>

                            </h2>

                            <p>Edit Position Information</p>

                        </div>

                    </div>

                                        <form method="POST" id="positionEditForm" novalidate>

                        <?php csrfField(); ?>

                        <input
                            type="hidden"
                            name="update_position"
                            value="1"
                        >

                        <div class="crud-form-grid">

                            <div class="crud-form-group">

                                <label>

                                    <i class="fa-solid fa-building"></i>
                                    Department

                                </label>

                                <select
                                    name="department_id"
                                    required
                                >

                                    <option value="">

                                        Select Department

                                    </option>

                                    <?php foreach ($departments as $department): ?>

                                        <option
                                            value="<?= $department['department_id']; ?>"
                                            <?= $position['department_id'] == $department['department_id']
                                                ? "selected"
                                                : ""; ?>
                                        >

                                            <?= htmlspecialchars($department['department_name']); ?>

                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>

                            <div class="crud-form-group">

                                <label>

                                    <i class="fa-solid fa-briefcase"></i>
                                    Position Name

                                </label>

                                <input
                                    type="text"
                                    name="position_name"
                                    value="<?= htmlspecialchars($position['position_name']); ?>"
                                    required
                                >

                            </div>

                            <div class="crud-form-group">

                                <label>

                                    <i class="fa-solid fa-circle-check"></i>
                                    Status

                                </label>

                                <select name="status">

                                    <option
                                        value="Active"
                                        <?= $position['status'] == "Active"
                                            ? "selected"
                                            : ""; ?>
                                    >

                                        Active

                                    </option>

                                    <option
                                        value="Inactive"
                                        <?= $position['status'] == "Inactive"
                                            ? "selected"
                                            : ""; ?>
                                    >

                                        Inactive

                                    </option>

                                </select>

                            </div>

                            <div
                                class="crud-form-group"
                                style="grid-column:1/-1;"
                            >

                                <label>

                                    <i class="fa-solid fa-align-left"></i>
                                    Description

                                </label>

                                <textarea
                                    name="description"
                                    rows="5"
                                ><?= htmlspecialchars($position['description']); ?></textarea>

                            </div>

                        </div>

                        <div class="crud-actions">

                                                        <button
                                type="submit"
                                id="positionEditSaveBtn"
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

<?php if (!empty($alert["message"])): ?>

<script>

document.addEventListener("DOMContentLoaded", function () {

    Swal.fire({

        icon: "<?= $alert['type']; ?>",

        title: "<?= $alert['type'] === 'success'
            ? 'Success!'
            : 'Oops!'; ?>",

        text: <?= json_encode($alert['message']); ?>,

        confirmButtonColor: "#003DA5",

        confirmButtonText: "OK",

        allowOutsideClick: false,

        allowEscapeKey: false

    }).then((result) => {

        <?php if ($alert['type'] === "success"): ?>

        if (result.isConfirmed) {

            window.location.href = "positions.php";

        }

        <?php endif; ?>

    });

});

</script>

<?php endif; ?>


<script>
(function () {

    const form = document.getElementById("positionEditForm");
    const saveBtn = document.getElementById("positionEditSaveBtn");
    const initialState = new URLSearchParams(new FormData(form)).toString();

    function checkForChanges() {
        const currentState = new URLSearchParams(new FormData(form)).toString();
        saveBtn.disabled = (currentState === initialState);
    }

    form.addEventListener("input", checkForChanges);
    form.addEventListener("change", checkForChanges);

})();

document.getElementById("positionEditForm").addEventListener("submit", function (e) {

    e.preventDefault();

    const form = this;

    Swal.fire({
        icon: "question",
        title: "Save changes?",
        text: "This will update this position's record.",
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