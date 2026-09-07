<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";
require_once "../config/permissions.php";

requirePermission($conn, 'add_positions', 'positions.php');

$page_title = "Add Position";


$departments = $conn->query("

    SELECT

        department_id,
        department_name

    FROM departments

    WHERE status='Active'

    ORDER BY department_name ASC

")->fetchAll(PDO::FETCH_ASSOC);


$department_id = "";
$position_name = "";
$description = "";
$status = "Active";

$alert = [

    "type" => "",
    "message" => ""

];


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    requireCSRFToken("position_add.php");

    $department_id = (int) ($_POST['department_id'] ?? 0);

    $position_name = trim($_POST['position_name'] ?? "");

    $description = trim($_POST['description'] ?? "");

    $status = $_POST['status'] ?? "Active";


    if (

        empty($department_id) ||

        empty($position_name)

    ) {

        $alert = [

            "type" => "warning",

            "message" => "Please complete all required fields."

        ];

    } else {


        $check = $conn->prepare("

            SELECT position_id

            FROM positions

            WHERE

                department_id = ?

            AND

                position_name = ?

            LIMIT 1

        ");

        $check->execute([

            $department_id,

            $position_name

        ]);

        if ($check->fetch()) {

            $alert = [

                "type" => "error",

                "message" => "Position already exists in this department."

            ];

        } else {


            $insert = $conn->prepare("

                INSERT INTO positions(

                    department_id,

                    position_name,

                    description,

                    status

                )

                VALUES(

                    ?,?,?,?

                )

            ");

            if (

                $insert->execute([

                    $department_id,

                    $position_name,

                    $description,

                    $status

                ])

            ) {

                $_SESSION['position_success'] =
                    "Position added successfully.";

                header("Location: positions.php");

                exit;

            }

            $alert = [

                "type" => "error",

                "message" => "Unable to save position."

            ];

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
                            Add Position

                        </h1>

                        <p>Create a new company position.</p>

                    </div>

                    <div class="crud-header-actions">

                        
                         <a   href="positions.php"
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

                            <h2>Position Information</h2>

                            <p>Complete all required fields.</p>

                        </div>

                    </div>

                                        <form method="POST" id="positionAddForm" novalidate>

                        <?php csrfField(); ?>

                        <div class="crud-form-grid">

                            <div class="crud-group">

                                <label>

                                    <i class="fa-solid fa-building"></i>
                                    Department

                                </label>

                                <select
                                    name="department_id"
                                    class="crud-control"
                                    required
                                >

                                    <option value="">

                                        Select Department

                                    </option>

                                    <?php foreach ($departments as $department): ?>

                                        <option

                                            value="<?= $department['department_id']; ?>"

                                            <?= ($department_id == $department['department_id'])
                                                ? "selected"
                                                : "";
                                            ?>

                                        >

                                            <?= htmlspecialchars(
                                                $department['department_name']
                                            ); ?>

                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>

                            <div class="crud-group">

                                <label>

                                    <i class="fa-solid fa-briefcase"></i>
                                    Position Name

                                </label>

                                <input

                                    type="text"
                                    name="position_name"
                                    class="crud-control"
                                    maxlength="100"
                                    required
                                    value="<?= htmlspecialchars($position_name); ?>"
                                    placeholder="Enter position name"
                                >

                            </div>

                            <div class="crud-group crud-full">

                                <label>

                                    <i class="fa-solid fa-align-left"></i>
                                    Description

                                </label>

                                <textarea

                                    name="description"
                                    class="crud-control"
                                    rows="5"
                                    placeholder="Enter position description"
                                ><?= htmlspecialchars($description); ?></textarea>

                            </div>

                            <div class="crud-group">

                                <label>

                                    <i class="fa-solid fa-toggle-on"></i>
                                    Status

                                </label>

                                <select

                                    name="status"
                                    class="crud-control"
                                >

                                    <option
                                        value="Active"
                                        <?= $status == "Active"
                                            ? "selected"
                                            : "";
                                        ?>

                                    >

                                        Active

                                    </option>

                                    <option
                                        value="Inactive"
                                        <?= $status == "Inactive"
                                            ? "selected"
                                            : "";
                                        ?>

                                    >

                                        Inactive

                                    </option>

                                </select>

                            </div>

                        </div>

                        <div class="crud-actions">

                            <button

                                type="submit"
                                class="crud-btn crud-btn-primary"

                            >

                                <i class="fa-solid fa-floppy-disk"></i>
                                Save Position

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

        title:

            <?php if ($alert['type'] === "success"): ?>

                "Success!"

            <?php elseif ($alert['type'] === "warning"): ?>

                "Incomplete Information"

            <?php else: ?>

                "Unable to Save"

            <?php endif; ?>,

        text: <?= json_encode($alert["message"]); ?>,

        confirmButtonColor: "#003DA5",

        confirmButtonText: "OK",

        allowOutsideClick: false,

        allowEscapeKey: false

    });

});

</script>

<?php endif; ?>

<script>
document.getElementById("positionAddForm").addEventListener("submit", function (e) {

    e.preventDefault();

    const form = this;

    Swal.fire({
        icon: "question",
        title: "Add this position?",
        text: "This will create a new position record.",
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