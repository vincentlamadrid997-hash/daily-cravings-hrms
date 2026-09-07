<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";
require_once "../config/permissions.php";

requirePermission($conn, 'edit_departments', 'departments.php');

$page_title = "Edit Department";


if (isset($_POST['department_id'])) {

    $_SESSION['selected_department'] = (int) $_POST['department_id'];

}


if (!isset($_SESSION['selected_department'])) {

    header("Location: departments.php");
    exit;

}


$department_id = (int) $_SESSION['selected_department'];


$stmt = $conn->prepare("
    SELECT *
    FROM departments
    WHERE department_id = ?
");


$stmt->execute([
    $department_id
]);


$department = $stmt->fetch(PDO::FETCH_ASSOC);



if (!$department) {

    unset($_SESSION['selected_department']);

    header("Location: departments.php");
    exit;

}



$alert = [

    "type" => "",
    "message" => ""

];


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_department'])) {

    requireCSRFToken("department_edit.php");


    $department_name = trim($_POST['department_name']);
    $description = trim($_POST['description']);
    $status = $_POST['status'];



    if (empty($department_name)) {


        $alert["type"] = "error";
        $alert["message"] = "Department name is required.";


    } else {



        $check = $conn->prepare("
            SELECT department_id
            FROM departments
            WHERE department_name = ?
            AND department_id != ?
        ");



        $check->execute([

            $department_name,
            $department_id

        ]);



        if ($check->fetch()) {


            $alert["type"] = "error";
            $alert["message"] = "Department name already exists.";



        } else {



            $update = $conn->prepare("
                UPDATE departments
                SET
                    department_name = ?,
                    description = ?,
                    status = ?
                WHERE department_id = ?
            ");



            $success = $update->execute([

                $department_name,
                $description,
                $status,
                $department_id

            ]);



            if ($success) {


                $alert["type"] = "success";
                $alert["message"] = "Department updated successfully.";



                $stmt->execute([

                    $department_id

                ]);


                $department = $stmt->fetch(PDO::FETCH_ASSOC);



            } else {


                $alert["type"] = "error";
                $alert["message"] = "Unable to update department.";

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

                            <i class="fa-solid fa-building"></i>
                            Edit Department

                        </h1>

                        <p>Update department information.</p>

                    </div>

                    <div class="crud-header-actions">

                        <a href="departments.php"
                           class="crud-back-btn">

                            <i class="fa-solid fa-arrow-left"></i>
                            Back to Departments

                        </a>

                    </div>

                </div>

                <div class="crud-card">

                    <div class="crud-card-header">

                        <div class="crud-icon">

                            <i class="fa-solid fa-building"></i>

                        </div>

                        <div>

                            <h2>
                                <?= htmlspecialchars($department['department_name']); ?>
                            </h2>

                            <p>Edit Department Information</p>

                        </div>

                    </div>

                                        <form method="POST" id="departmentEditForm" novalidate>

                        <?php csrfField(); ?>

                        <input
                            type="hidden"
                            name="update_department"
                            value="1"
                        >

                        <div class="crud-form-grid">

                            <div class="crud-form-group">

                                <label>

                                    <i class="fa-solid fa-building"></i>
                                    Department Name

                                </label>

                                <input
                                    type="text"
                                    name="department_name"
                                    value="<?= htmlspecialchars($department['department_name']); ?>"
                                    required
                                >

                            </div>

                            <div class="crud-form-group">

                                <label>

                                    <i class="fa-solid fa-circle-check"></i>
                                    Status

                                </label>

                                <select name="status">

                                    <option value="Active"
                                        <?= $department['status'] == "Active" ? "selected" : ""; ?>
                                    >

                                        Active

                                    </option>

                                    <option value="Inactive"
                                        <?= $department['status'] == "Inactive" ? "selected" : ""; ?>
                                    >

                                        Inactive

                                    </option>

                                </select>

                            </div>

                            <div class="crud-form-group"
                                 style="grid-column:1/-1;">

                                <label>

                                    <i class="fa-solid fa-align-left"></i>
                                    Description

                                </label>

                                <textarea
                                    name="description"
                                    rows="5"
                                ><?= htmlspecialchars($department['description']); ?></textarea>

                            </div>

                        </div>

                        <div class="crud-actions">

                                                        <button
                                type="submit"
                                id="departmentEditSaveBtn"
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


document.addEventListener("DOMContentLoaded", function(){

    Swal.fire({

        icon: "<?= $alert['type']; ?>",

        title: "<?= $alert['type'] == 'success' 
            ? 'Success!' 
            : 'Oops!'; ?>",


        text: "<?= htmlspecialchars(
            $alert['message'],
            ENT_QUOTES
        ); ?>",

        confirmButtonColor: "#003DA5"

    }).then(() => {

        <?php if ($alert['type'] == "success"): ?>

            window.location = "departments.php";

        <?php endif; ?>

    });


});


</script>


<?php endif; ?>


<script>
(function () {

    const form = document.getElementById("departmentEditForm");
    const saveBtn = document.getElementById("departmentEditSaveBtn");
    const initialState = new URLSearchParams(new FormData(form)).toString();

    function checkForChanges() {
        const currentState = new URLSearchParams(new FormData(form)).toString();
        saveBtn.disabled = (currentState === initialState);
    }

    form.addEventListener("input", checkForChanges);
    form.addEventListener("change", checkForChanges);

})();

document.getElementById("departmentEditForm").addEventListener("submit", function (e) {

    e.preventDefault();

    const form = this;

    Swal.fire({
        icon: "question",
        title: "Save changes?",
        text: "This will update this department's record.",
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