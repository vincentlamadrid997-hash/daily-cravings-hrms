<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";

$page_title = "Add Contract";


$employees = $conn->query("
    SELECT
        e.employee_id,
        e.employee_code,
        CONCAT(
            e.first_name,
            ' ',
            e.last_name
        ) AS employee_name,
        p.position_name

    FROM employees e

    LEFT JOIN positions p
        ON e.position_id = p.position_id

    WHERE e.employment_status = 'Active'

    AND e.employee_id NOT IN (
        SELECT employee_id
        FROM contracts
        WHERE status = 'Active'
    )

    ORDER BY e.first_name ASC

")->fetchAll(PDO::FETCH_ASSOC);


$alert = [
    "type" => "",
    "message" => ""
];


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    requireCSRFToken("contract_add.php");

    $employee_id = (int) ($_POST['employee_id'] ?? 0);

    $contract_type = trim(
        $_POST['contract_type'] ?? ""
    );

    $start_date = $_POST['start_date'] ?? "";

    $end_date = $_POST['end_date'] ?? "";

    $salary = trim(
        $_POST['salary'] ?? ""
    );

    $status = $_POST['status'] ?? "Active";


    if (
        empty($employee_id) ||
        empty($contract_type) ||
        empty($start_date) ||
        empty($end_date) ||
        empty($salary)
    ) {

        $alert["type"] = "error";

        $alert["message"] =
            "Please complete all required fields.";

    }


    elseif (!is_numeric($salary)) {

        $alert["type"] = "error";

        $alert["message"] =
            "Salary must be a valid number.";

    }


    elseif ($end_date <= $start_date) {

        $alert["type"] = "error";

        $alert["message"] =
            "End date must be later than start date.";

    }

    else {


        $check = $conn->prepare("
            SELECT contract_id
            FROM contracts
            WHERE employee_id = ?
            AND status = 'Active'
            LIMIT 1
        ");

        $check->execute([
            $employee_id
        ]);


        if ($check->fetch()) {

            $alert["type"] = "error";

            $alert["message"] =
                "This employee already has an ongoing active contract.";

        }


        else {


            $fileName = null;


            if (
                isset($_FILES['file']) &&
                $_FILES['file']['error'] === UPLOAD_ERR_OK
            ) {


                $allowed = [
                    "pdf",
                    "doc",
                    "docx"
                ];


                $extension = strtolower(
                    pathinfo(
                        $_FILES['file']['name'],
                        PATHINFO_EXTENSION
                    )
                );


                if (!in_array($extension, $allowed)) {

                    $alert["type"] = "error";

                    $alert["message"] =
                        "Only PDF, DOC, and DOCX files are allowed.";

                }


                else {


                    $uploadPath = "../uploads/contracts/";


                    if (!is_dir($uploadPath)) {

                        mkdir(
                            $uploadPath,
                            0777,
                            true
                        );

                    }


                    $fileName =
                        time() . "_" .
                        preg_replace(
                            "/[^A-Za-z0-9._-]/",
                            "",
                            $_FILES['file']['name']
                        );


                    move_uploaded_file(
                        $_FILES['file']['tmp_name'],
                        $uploadPath . $fileName
                    );

                }

            }


            if (empty($alert["message"])) {

                $stmt = $conn->prepare("
                    INSERT INTO contracts
                    (
                        employee_id,
                        contract_type,
                        start_date,
                        end_date,
                        salary,
                        status,
                        file
                    )

                    VALUES
                    (
                        ?, ?, ?, ?, ?, ?, ?
                    )
                ");


                $success = $stmt->execute([
                    $employee_id,
                    $contract_type,
                    $start_date,
                    $end_date,
                    $salary,
                    $status,
                    $fileName
                ]);


                if ($success) {

                    $alert["type"] = "success";

                    $alert["message"] =
                        "Contract added successfully.";

                }


                else {

                    $alert["type"] = "error";

                    $alert["message"] =
                        "Unable to save contract.";

                }

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

                            <i class="fa-solid fa-file-signature"></i>
                            Add Contract

                        </h1>

                        <p>Create a new employee employment contract.</p>

                    </div>

                    <div class="crud-header-actions">

                        <a 
                            href="contracts.php"
                            class="crud-back-btn"
                        >

                            <i class="fa-solid fa-arrow-left"></i>
                            Back to Contracts

                        </a>

                    </div>

                </div>

                <div class="crud-card">

                    <div class="crud-card-header">

                        <div class="crud-icon">

                            <i class="fa-solid fa-file-contract"></i>

                        </div>

                        <div>

                            <h2>Contract Information</h2>

                            <p>Fill in all required contract details.</p>

                        </div>

                    </div>

                                             <form 
                        method="POST"
                        enctype="multipart/form-data"
                        id="contractAddForm"
                        novalidate
                    >

                        <?php csrfField(); ?>

                        <div class="crud-form-grid">

                            <div class="crud-form-group">

                                <label>

                                    <i class="fa-solid fa-user"></i>
                                    Employee

                                </label>

                                <select 
                                    name="employee_id"
                                    required
                                >

                                    <option value="">
                                        Select Employee
                                    </option>

                                    <?php if (!empty($employees)): ?>

                                        <?php foreach ($employees as $employee): ?>

                                            <option 
                                                value="<?= $employee['employee_id']; ?>"
                                            >

                                                <?= htmlspecialchars(
                                                    $employee['employee_code']
                                                    . " - "
                                                    . $employee['employee_name']
                                                    . " ("
                                                    . ($employee['position_name'] ?? "No Position")
                                                    . ")"
                                                ); ?>


                                            </option>

                                        <?php endforeach; ?>

                                    <?php else: ?>

                                        <option disabled>
                                            No available employees
                                        </option>

                                    <?php endif; ?>

                                </select>

                            </div>

                            <div class="crud-form-group">

                                <label>

                                    <i class="fa-solid fa-file-contract"></i>
                                    Contract Type

                                </label>

                                <select
                                    name="contract_type"
                                    required
                                >

                                    <option value="">
                                        Select Contract Type
                                    </option>

                                    <option value="Probationary">
                                        Probationary
                                    </option>

                                    <option value="Regular">
                                        Regular
                                    </option>

                                    <option value="Project-Based">
                                        Project-Based
                                    </option>

                                    <option value="Part-Time">
                                        Part-Time
                                    </option>

                                    <option value="Seasonal">
                                        Seasonal
                                    </option>

                                </select>

                            </div>

                            <div class="crud-form-group">

                                <label>

                                    <i class="fa-solid fa-peso-sign"></i>
                                    Monthly Salary

                                </label>

                                <div class="crud-input-icon">

                                    <span>
                                        ₱
                                    </span>

                                    <input

                                        type="number"
                                        name="salary"
                                        step="0.01"
                                        min="0"
                                        placeholder="Enter monthly salary"
                                        required
                                    >

                                </div>

                            </div>

                            <div class="crud-form-group">

                                <label>

                                    <i class="fa-solid fa-calendar-days"></i>
                                    Start Date

                                </label>

                                <input

                                    type="date"
                                    name="start_date"
                                    min="<?= date('Y-m-d'); ?>"
                                    required
                                >

                            </div>

                            <div class="crud-form-group">

                                <label>

                                    <i class="fa-solid fa-calendar-xmark"></i>
                                    End Date

                                </label>

                                <input

                                    type="date"
                                    name="end_date"
                                    min="<?= date('Y-m-d'); ?>"
                                    required
                                >

                            </div>

                            <div class="crud-form-group">

                                <label>

                                    <i class="fa-solid fa-circle-check"></i>
                                    Contract Status

                                </label>

                                <select

                                    name="status"
                                    required

                                >

                                    <option value="Active">
                                        Active
                                    </option>

                                    <option value="Expired">
                                        Expired
                                    </option>

                                </select>

                            </div>

                            <div 
                                class="crud-form-group"
                                style="grid-column:1/-1;"
                            >

                                <label>

                                    <i class="fa-solid fa-paperclip"></i>
                                    Contract File

                                </label>

                                <input

                                    type="file"
                                    name="file"
                                    accept=".pdf,.doc,.docx"
                                >

                                <small
                                    style="
                                        color:#78909C;
                                        margin-top:8px;
                                        display:block;
                                    "
                                >

                                    Accepted formats:
                                    PDF, DOC, DOCX

                                </small>

                            </div>

                        </div>

                        <div class="crud-actions">

                            <button

                                type="submit"
                                class="crud-btn crud-btn-primary"

                            >

                                <i class="fa-solid fa-floppy-disk"></i>
                                Save Contract

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

            "<?= $alert['type'] === 'success'
                ? 'Contract Added!'
                : 'Unable to Add Contract'; ?>",

        text:

            <?= json_encode($alert["message"]); ?>,

        confirmButtonColor: "#003DA5",

        confirmButtonText: "OK",

        allowOutsideClick: false,

        allowEscapeKey: false

    }).then((result) => {

        <?php if ($alert["type"] === "success"): ?>

        if (result.isConfirmed) {

            window.location.href = "contracts.php";

        }

        <?php endif; ?>

    });

});

</script>


<?php endif; ?>

<script>

document.addEventListener("DOMContentLoaded", function () {

    const startDate = document.querySelector(
        'input[name="start_date"]'
    );

    const endDate = document.querySelector(
        'input[name="end_date"]'
    );

    if (startDate && endDate) {

        startDate.addEventListener("change", function () {

            endDate.min = this.value;

        });

        endDate.addEventListener("change", function () {

            if (

                startDate.value &&

                this.value <= startDate.value

            ) {

                Swal.fire({

                    icon: "warning",

                    title: "Invalid Date",

                    text: "End date must be later than start date.",

                    confirmButtonColor: "#003DA5"

                });

                this.value = "";

            }

        });

    }

});

</script>

<script>

document.addEventListener("DOMContentLoaded", function () {

    const salaryInput = document.querySelector(
        'input[name="salary"]'
    );

    if (salaryInput) {

        salaryInput.addEventListener("input", function () {

            this.value = this.value.replace(
                /[^0-9.]/g,
                ""
            );

        });

    }

});

</script>


<script>
document.getElementById("contractAddForm").addEventListener("submit", function (e) {

    e.preventDefault();

    const form = this;

    Swal.fire({
        icon: "question",
        title: "Add this contract?",
        text: "This will create a new contract record.",
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