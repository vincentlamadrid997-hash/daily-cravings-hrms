<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";

$page_title = "Edit Contract";


if (isset($_POST['contract_id'])) {

    $_SESSION['selected_contract'] = (int) $_POST['contract_id'];

}


if (!isset($_SESSION['selected_contract'])) {

    header("Location: contracts.php");
    exit;

}


$contract_id = (int) $_SESSION['selected_contract'];


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

    ORDER BY e.first_name ASC

")->fetchAll(PDO::FETCH_ASSOC);


$stmt = $conn->prepare("
    SELECT *

    FROM contracts

    WHERE contract_id = ?
");


$stmt->execute([
    $contract_id
]);


$contract = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$contract) {

    unset($_SESSION['selected_contract']);

    header("Location: contracts.php");
    exit;

}


$alert = [
    "type" => "",
    "message" => ""
];


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_contract'])) {

    requireCSRFToken("contract_edit.php");


    $employee_id = (int) $_POST['employee_id'];

    $contract_type = trim($_POST['contract_type']);

    $start_date = $_POST['start_date'];

    $end_date = $_POST['end_date'];

    $salary = trim($_POST['salary']);

    $status = $_POST['status'];


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


    } elseif (!is_numeric($salary)) {


        $alert["type"] = "error";

        $alert["message"] =
            "Salary must be a valid amount.";


    } elseif ($end_date <= $start_date) {


        $alert["type"] = "error";

        $alert["message"] =
            "End date must be later than start date.";


    } else {


        if ($status === "Active") {


            $check = $conn->prepare("
                SELECT contract_id

                FROM contracts

                WHERE employee_id = ?

                AND status = 'Active'

                AND contract_id != ?
            ");


            $check->execute([
                $employee_id,
                $contract_id
            ]);


            if ($check->fetch()) {


                $alert["type"] = "error";

                $alert["message"] =
                    "This employee already has another active contract.";

            }

        }


        $fileName = $contract['file'];


        if (
            empty($alert["message"]) &&
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
                    "Only PDF, DOC and DOCX files are allowed.";


            } else {


                if (!is_dir("../uploads/contracts")) {


                    mkdir(
                        "../uploads/contracts",
                        0777,
                        true
                    );

                }


                if (
                    !empty($contract['file']) &&
                    file_exists(
                        "../uploads/contracts/" . $contract['file']
                    )
                ) {


                    unlink(
                        "../uploads/contracts/" . $contract['file']
                    );

                }


                $fileName =
                    time()
                    . "_"
                    .
                    preg_replace(
                        "/[^A-Za-z0-9._-]/",
                        "",
                        $_FILES['file']['name']
                    );


                move_uploaded_file(
                    $_FILES['file']['tmp_name'],
                    "../uploads/contracts/" . $fileName
                );

            }

        }


        if (empty($alert["message"])) {


            $update = $conn->prepare("
                UPDATE contracts

                SET
                    employee_id = ?,
                    contract_type = ?,
                    start_date = ?,
                    end_date = ?,
                    salary = ?,
                    status = ?,
                    file = ?

                WHERE contract_id = ?
            ");


            $success = $update->execute([

                $employee_id,
                $contract_type,
                $start_date,
                $end_date,
                $salary,
                $status,
                $fileName,
                $contract_id

            ]);


            if ($success) {


                $alert["type"] = "success";

                $alert["message"] =
                    "Contract updated successfully.";


                $stmt->execute([
                    $contract_id
                ]);


                $contract = $stmt->fetch(PDO::FETCH_ASSOC);


            } else {


                $alert["type"] = "error";

                $alert["message"] =
                    "Unable to update contract.";

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

                            <i class="fa-solid fa-file-pen"></i>
                            Edit Contract

                        </h1>

                        <p>Update employee contract information.</p>

                    </div>

                    <div class="crud-header-actions">

                        <a href="contracts.php"
                           class="crud-back-btn">

                            <i class="fa-solid fa-arrow-left"></i>
                            Back to Contracts

                        </a>

                    </div>

                </div>

<div class="crud-card">

    <div class="crud-card-header">

        <div class="crud-icon">

            <i class="fa-solid fa-file-signature"></i>

        </div>

        <div>

            <h2>Edit Contract Information</h2>

            <p>Modify contract details below.</p>

        </div>

    </div>

            <form method="POST"
          id="contractEditForm"
          novalidate
          enctype="multipart/form-data">

        <?php csrfField(); ?>

        <input type="hidden"
               name="update_contract"
               value="1">

        <div class="crud-form-grid">

            <div class="crud-form-group">

                <label>

                    <i class="fa-solid fa-user"></i>
                    Employee

                </label>

                <input type="text"
                       value="<?php

                       foreach ($employees as $employee) {

                           if ($employee['employee_id'] == $contract['employee_id']) {

                               echo htmlspecialchars(
                                   $employee['employee_code']
                                   . " - "
                                   . $employee['employee_name']
                                   . " ("
                                   . $employee['position_name']
                                   . ")"
                               );

                               break;

                           }

                       }

                       ?>"
                       readonly>

                <input type="hidden"
                       name="employee_id"
                       value="<?= $contract['employee_id']; ?>">

            </div>

            <div class="crud-form-group">

                <label>

                    <i class="fa-solid fa-file-contract"></i>
                    Contract Type

                </label>


                <select name="contract_type"
                        required>

                    <option value="">
                        Select Contract Type
                    </option>

                    <?php

                    $types = [
                        "Probationary",
                        "Regular",
                        "Project-Based",
                        "Part-Time",
                        "Seasonal"
                    ];

                    foreach ($types as $type):

                    ?>

                        <option value="<?= $type; ?>"
                            <?= $contract['contract_type'] == $type ? "selected" : ""; ?>>

                            <?= $type; ?>

                        </option>

                    <?php endforeach; ?>

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

                    <input type="number"
                           name="salary"
                           step="0.01"
                           min="0"
                           value="<?= htmlspecialchars($contract['salary']); ?>"
                           placeholder="Enter salary"
                           required>

                </div>

            </div>

            <div class="crud-form-group">

                <label>

                    <i class="fa-solid fa-calendar-days"></i>
                    Start Date

                </label>

                <input type="date"
                       name="start_date"
                       value="<?= $contract['start_date']; ?>"
                       required>

            </div>

            <div class="crud-form-group">

                <label>

                    <i class="fa-solid fa-calendar-xmark"></i>
                    End Date

                </label>

                <input type="date"
                       name="end_date"
                       value="<?= $contract['end_date']; ?>"
                       required>

            </div>

            <div class="crud-form-group">

                <label>

                    <i class="fa-solid fa-circle-check"></i>
                    Contract Status

                </label>

                <select name="status"
                        required>

                    <option value="Active"
                        <?= $contract['status'] == "Active" ? "selected" : ""; ?>>

                        Active
                    </option>

                    <option value="Expired"
                        <?= $contract['status'] == "Expired" ? "selected" : ""; ?>>

                        Expired
                    </option>

                </select>

            </div>

            <div class="crud-form-group"
                 style="grid-column: 1 / -1;">

                <label>

                    <i class="fa-solid fa-paperclip"></i>
                    Contract File

                </label>

                <input type="file"
                       name="file"
                       accept=".pdf,.doc,.docx">

                <?php if (!empty($contract['file'])): ?>

                    <small style="
                        margin-top:8px;
                        display:block;
                        color:#607D8B;
                    ">

                        Current File:

                        <i class="fa-solid fa-file"></i>

                        <?= htmlspecialchars($contract['file']); ?>

                    </small>

                <?php endif; ?>

                <small style="
                    margin-top:5px;
                    display:block;
                    color:#78909C;
                ">

                    Accepted formats: PDF, DOC, DOCX

                </small>

            </div>

        </div>

        <div class="crud-actions">

                        <button type="submit"
                    id="contractEditSaveBtn"
                    disabled
                    class="crud-btn crud-btn-primary">

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

        title:
            "<?= $alert['type'] === 'success'
                ? 'Success!'
                : 'Unable to Continue'; ?>",

        text:
            <?= json_encode($alert['message']); ?>,

        confirmButtonColor: "#003DA5",

        allowOutsideClick: false,

        allowEscapeKey: false

    }).then((result) => {

        <?php if ($alert['type'] == "success"): ?>

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

    const salaryInput = document.querySelector(
        'input[name="salary"]'
    );

    if (salaryInput) {

        salaryInput.addEventListener("input", function () {

            this.value = this.value.replace(
                /[^0-9.]/g,
                ''
            );

        });

    }

    const startDate = document.querySelector(
        'input[name="start_date"]'
    );

    const endDate = document.querySelector(
        'input[name="end_date"]'
    );

    if (startDate && endDate) {

        endDate.addEventListener(
            "change",
            function () {

                if (
                    startDate.value &&
                    endDate.value <= startDate.value
                ) {

                    Swal.fire({

                        icon: "warning",

                        title: "Invalid Date",

                        text:
                            "End date must be later than start date.",

                        confirmButtonColor: "#003DA5"

                    });

                    endDate.value = "";

                }

            }
        );

    }

});

</script>


<script>
(function () {

    const form = document.getElementById("contractEditForm");
    const saveBtn = document.getElementById("contractEditSaveBtn");
    const initialState = new URLSearchParams(new FormData(form)).toString();

    function checkForChanges() {
        const currentState = new URLSearchParams(new FormData(form)).toString();
        saveBtn.disabled = (currentState === initialState);
    }

    form.addEventListener("input", checkForChanges);
    form.addEventListener("change", checkForChanges);

})();

document.getElementById("contractEditForm").addEventListener("submit", function (e) {

    e.preventDefault();

    const form = this;

    Swal.fire({
        icon: "question",
        title: "Save changes?",
        text: "This will update this contract's record.",
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