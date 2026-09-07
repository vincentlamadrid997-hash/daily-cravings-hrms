<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";
require_once "../config/permissions.php";

requirePermission($conn, 'add_departments', 'departments.php');

$page_title = "Add Department";

$alert = [
    "type" => "",
    "message" => ""
];


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    requireCSRFToken("department_add.php");

    $department_name = trim($_POST['department_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'Active';


    if (empty($department_name)) {

        $alert["type"] = "error";
        $alert["message"] = "Department name is required.";

    } else {

        $check = $conn->prepare("
            SELECT department_id
            FROM departments
            WHERE department_name = ?
        ");

        $check->execute([
            $department_name
        ]);


        if ($check->fetch()) {

            $alert["type"] = "error";
            $alert["message"] = "Department name already exists.";

        } else {

            $stmt = $conn->prepare("
                INSERT INTO departments
                (
                    department_name,
                    description,
                    status
                )
                VALUES
                (
                    ?,
                    ?,
                    ?
                )
            ");

            $stmt->execute([
                $department_name,
                $description,
                $status
            ]);


            $alert["type"] = "success";
            $alert["message"] = "Department added successfully.";

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
                            <i class="fa-solid fa-building"></i>
                            Add Department
                        </h1>

                        <p>Create new company department.</p>

                    </div>

                    <div class="crud-header-actions">

                        <a href="departments.php" class="crud-back-btn">

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

                            <h2>Department Information</h2>

                            <p>Fill in department details.</p>

                        </div>

                    </div>

                                        <form method="POST" id="departmentAddForm" novalidate>

                        <?php csrfField(); ?>

                        <div class="crud-form-grid">

                            <div class="crud-form-group">

                                <label>

                                    <i class="fa-solid fa-building"></i>
                                    Department Name

                                </label>

                                <input
                                    type="text"
                                    name="department_name"
                                    placeholder="Enter department name"
                                    required
                                >

                            </div>

                            <div class="crud-form-group">

                                <label>

                                    <i class="fa-solid fa-circle-check"></i>
                                    Status

                                </label>

                                <select name="status">

                                    <option value="Active">
                                        Active
                                    </option>

                                    <option value="Inactive">
                                        Inactive
                                    </option>

                                </select>

                            </div>

                            <div class="crud-form-group crud-full">

                                <label>

                                    <i class="fa-solid fa-align-left"></i>
                                    Description

                                </label>

                                <textarea
                                    name="description"
                                    rows="5"
                                    placeholder="Enter department description"
                                ></textarea>

                            </div>

                        </div>

                        <div class="crud-actions">

                            <button
                                type="submit"
                                class="crud-btn crud-btn-primary"
                            >

                                <i class="fa-solid fa-save"></i>
                                Save Department

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

        title:
        "<?= $alert['type'] == 'success'
            ? 'Department Added!'
            : 'Unable to Add Department'; ?>",

        text:
        "<?= htmlspecialchars(
            $alert['message'],
            ENT_QUOTES,
            'UTF-8'
        ); ?>",

        confirmButtonColor:"#003DA5",

        confirmButtonText:"OK",

        allowOutsideClick:false,

        allowEscapeKey:false


    }).then((result)=>{


        <?php if($alert["type"]=="success"): ?>

        if(result.isConfirmed){

            window.location.href="departments.php";

        }

        <?php endif; ?>


    });


});


</script>

<?php endif; ?>


<script>
document.getElementById("departmentAddForm").addEventListener("submit", function (e) {

    e.preventDefault();

    const form = this;

    Swal.fire({
        icon: "question",
        title: "Add this department?",
        text: "This will create a new department record.",
        showCancelButton: true,
        confirmButtonText: "Yes, Add",
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