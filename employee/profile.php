<?php

session_start();

require_once "../auth/employee_auth.php";
require_once "../config/db.php";

$page_title = "My Profile";

$success = $_SESSION["profile_success"] ?? "";
$error = $_SESSION["profile_error"] ?? "";

unset($_SESSION["profile_success"]);
unset($_SESSION["profile_error"]);


$employee_id = $_SESSION["employee_id"] ?? 0;

if (!$employee_id) {

    header(
        "Location: ../auth/login.php"
    );
    exit;

}

$stmt = $conn->prepare("

    SELECT

        u.user_id,
        u.employee_id,
        u.full_name,
        u.email,
        u.password,
        u.role,
        e.gender,
        u.profile_picture,
        u.status,
        u.last_login,
        u.created_at,

        e.employee_code,
        e.first_name,
        e.middle_name,
        e.last_name,
        e.phone,
        e.birthdate,
        e.civil_status,
        e.address,
        e.basic_salary,
        e.employment_status,
        e.hire_date,

        d.department_name,

        p.position_name

    FROM users u

    LEFT JOIN employees e
        ON u.employee_id = e.employee_id

    LEFT JOIN departments d
        ON e.department_id = d.department_id

    LEFT JOIN positions p
        ON e.position_id = p.position_id

    WHERE e.employee_id = ?

    LIMIT 1

");


$stmt->execute([

    $employee_id

]);


$employee = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$employee) {

    header(
        "Location: dashboard.php"
    );
    exit;

}


$contract_stmt = $conn->prepare("

    SELECT

        contract_type,
        start_date,
        end_date,
        status

    FROM contracts
    WHERE employee_id = ?
    ORDER BY contract_id DESC

    LIMIT 1

");


$contract_stmt->execute([

    $employee['employee_id']

]);


$contract = $contract_stmt->fetch(PDO::FETCH_ASSOC);


/*
==================================================
PROFILE AVATAR LOGIC

Uploaded Picture > Gender Default
==================================================
*/


if(!empty($employee['profile_picture'])){


    $avatar =

    "../uploads/profile/"

    .

    $employee['profile_picture'];



}

else{


    $gender = strtolower(

        trim(

            $employee['gender'] ?? ""

        )

    );



    switch($gender){



        case "male":


            $avatar =

            "../assets/images/avatar/employee_boy.png";


        break;




        case "female":


            $avatar =

            "../assets/images/avatar/employee_girl.png";


        break;




        default:


            $avatar =

            "../assets/images/avatar/default-avatar.png";


        break;



    }


}


$full_name =
    $employee['full_name'] ?? "Employee";


$email =
    $employee['email'] ?? "N/A";


$employee_code =
    $employee['employee_code'] ?? "N/A";


$department =
    $employee['department_name'] ?? "N/A";


$position =
    $employee['position_name'] ?? "N/A";


$employment_status =
    $employee['employment_status'] ?? "N/A";


$birthdate =

    !empty($employee['birthdate'])

        ?

        date(
            "F d, Y",
            strtotime(
                $employee['birthdate']
            )
        )
        :
        "N/A";


$hire_date =

    !empty($employee['hire_date'])

        ?

        date(
            "F d, Y",
            strtotime(
                $employee['hire_date']
            )
        )
        :
        "N/A";


$created_date =

    !empty($employee['created_at'])

        ?

        date(
            "F d, Y",
            strtotime(
                $employee['created_at']
            )
        )
        :
        "N/A";


$contract_type =
    $contract['contract_type'] ?? "N/A";


$contract_start =

    !empty($contract['start_date'])

        ?

        date(
            "F d, Y",
            strtotime(
                $contract['start_date']
            )
        )
        :
        "N/A";


$contract_end =

    !empty($contract['end_date'])

        ?

        date(
            "F d, Y",
            strtotime(
                $contract['end_date']
            )
        )
        :
        "N/A";


$contract_status =
    $contract['status'] ?? "N/A";


$status_class = strtolower(

    $employee['status']

);


?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($page_title); ?> | Daily Cravings Foods Inc.</title>

    <link rel="stylesheet" href="../assets/css/global.css">

    <link rel="stylesheet" href="../assets/css/employee.css">

    <link rel="stylesheet" href="../assets/css/employee_profile.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" >

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>


<body>

<div class="employee-wrapper">

    <?php require_once "includes/sidebar.php"; ?>

    <div class="employee-content-wrapper">

        <?php require_once "includes/header.php"; ?>

        <main class="employee-main">

            <section class="employee-profile-page">

                <div class="employee-profile-header">

                    <div class="employee-profile-title">

                        <h1>
                            <i class="fa-solid fa-user"></i>
                            My Profile
                        </h1>

                        <p>Manage your personal information and employee account.</p>

                    </div>

                </div>

                <div class="employee-profile-card">

                    <div class="employee-profile-top">

                        <div class="employee-profile-avatar">

                            <img
                                src="<?= htmlspecialchars($avatar); ?>"
                                alt="Employee Profile"
                                class="employee-profile-image"
                            >

                        </div>

                        <div class="employee-profile-info">

                            <h2>
                                <?= htmlspecialchars($full_name); ?>
                            </h2>

                            <p class="employee-profile-email">

                                <i class="fa-solid fa-envelope"></i>
                                <?= htmlspecialchars($email); ?>

                            </p>

                            <div class="employee-profile-badges">

                                <span class="employee-role-badge">

                                    <i class="fa-solid fa-user"></i>
                                    Employee

                                </span>

                                <span class="employee-status-badge <?= $status_class; ?>">

                                    <i class="fa-solid fa-circle"></i>

                                    <?= htmlspecialchars(
                                        ucfirst($employee['status'])
                                    ); ?>

                                </span>

                            </div>

                        </div>

                    </div>

                    <div class="employee-profile-actions">

                        <a
                            href="profile_edit.php"
                            class="employee-profile-btn edit"

                        >

                            <i class="fa-solid fa-pen"></i>
                            Edit Profile

                        </a>

                        <a
                            href="profile_change_password.php"
                            class="employee-profile-btn password"

                        >

                            <i class="fa-solid fa-lock"></i>
                            Change Password
                        </a>

                    </div>

                    <div class="employee-profile-section-header">

                        <div class="employee-profile-section-icon">
                            <i class="fa-solid fa-id-card"></i>
                        </div>

                        <div>

                            <h2>Employee Information</h2>

                            <p>Your personal and employment details.</p>

                        </div>

                    </div>

                    <div class="employee-profile-grid">

                        <div class="employee-profile-group">

                            <label>
                                <i class="fa-solid fa-barcode"></i>
                                Employee Code
                            </label>

                            <input
                                type="text"
                                value="<?= htmlspecialchars($employee_code); ?>"
                                readonly
                            >

                        </div>

                        <div class="employee-profile-group">

                            <label>
                                <i class="fa-solid fa-user"></i>
                                Full Name
                            </label>

                            <input
                                type="text"
                                value="<?= htmlspecialchars($full_name); ?>"
                                readonly
                            >

                        </div>

                        <div class="employee-profile-group">

                            <label>
                                <i class="fa-solid fa-envelope"></i>
                                Email Address
                            </label>

                            <input
                                type="text"
                                value="<?= htmlspecialchars($email); ?>"
                                readonly
                            >

                        </div>

                        <div class="employee-profile-group">

                            <label>
                                <i class="fa-solid fa-phone"></i>
                                Phone Number
                            </label>

                            <input
                                type="text"
                                value="<?= htmlspecialchars(
                                    $employee['phone'] ?? 'N/A'
                                ); ?>"
                                readonly
                            >

                        </div>

                        <div class="employee-profile-group">

                            <label>
                                <i class="fa-solid fa-calendar"></i>
                                Birthdate
                            </label>

                            <input
                                type="text"
                                value="<?= htmlspecialchars($birthdate); ?>"
                                readonly
                            >

                        </div>

                        <div class="employee-profile-group">

                            <label>
                                <i class="fa-solid fa-venus-mars"></i>
                                Gender
                            </label>

                            <input
                                type="text"
                                value="<?= htmlspecialchars(
                                    $employee['gender'] ?? 'N/A'
                                ); ?>"
                                readonly
                            >

                        </div>

                        <div class="employee-profile-group">

                            <label>
                                <i class="fa-solid fa-ring"></i>
                                Civil Status
                            </label>

                            <input
                                type="text"
                                value="<?= htmlspecialchars(
                                    $employee['civil_status'] ?? 'N/A'
                                ); ?>"
                                readonly
                            >

                        </div>

                        <div class="employee-profile-group employee-profile-full">

                            <label>
                                <i class="fa-solid fa-location-dot"></i>
                                Address
                            </label>

                            <textarea
                                readonly
                            ><?= htmlspecialchars(
                                $employee['address'] ?? 'N/A'
                            ); ?></textarea>

                        </div>

                    </div>

                    <div class="employee-profile-section-header">

                        <div class="employee-profile-section-icon">
                            <i class="fa-solid fa-briefcase"></i>
                        </div>

                        <div>

                            <h2>Employment Information</h2>

                            <p>Company assignment and employment details.</p>

                        </div>

                    </div>

                    <div class="employee-profile-grid">

                        <div class="employee-profile-group">

                            <label>
                                <i class="fa-solid fa-building"></i>
                                Department
                            </label>

                            <input
                                type="text"
                                value="<?= htmlspecialchars($department); ?>"
                                readonly
                            >

                        </div>

                        <div class="employee-profile-group">

                            <label>
                                <i class="fa-solid fa-user-tie"></i>
                                Position
                            </label>

                            <input
                                type="text"
                                value="<?= htmlspecialchars($position); ?>"
                                readonly
                            >

                        </div>

                        <div class="employee-profile-group">

                            <label>
                                <i class="fa-solid fa-money-bill"></i>
                                Basic Salary
                            </label>

                            <input
                                type="text"
                                value="₱<?= number_format(
                                    $employee['basic_salary'] ?? 0,
                                    2
                                ); ?>"
                                readonly
                            >

                        </div>

                        <div class="employee-profile-group">

                            <label>
                                <i class="fa-solid fa-user-check"></i>
                                Employment Status
                            </label>

                            <input
                                type="text"
                                value="<?= htmlspecialchars($employment_status); ?>"
                                readonly
                            >

                        </div>

                        <div class="employee-profile-group">

                            <label>
                                <i class="fa-solid fa-calendar-plus"></i>
                                Hire Date
                            </label>

                            <input
                                type="text"
                                value="<?= htmlspecialchars($hire_date); ?>"
                                readonly
                            >

                        </div>

                    </div>

                    <div class="employee-profile-section-header">

                        <div class="employee-profile-section-icon">
                            <i class="fa-solid fa-file-contract"></i>
                        </div>

                        <div>

                            <h2>Employment Contract</h2>

                            <p>Your current contract information.</p>

                        </div>

                    </div>

                    <div class="employee-profile-grid">

                        <div class="employee-profile-group">

                            <label>
                                <i class="fa-solid fa-file-signature"></i>
                                Contract Type
                            </label>

                            <input
                                type="text"
                                value="<?= htmlspecialchars($contract_type); ?>"
                                readonly
                            >

                        </div>

                        <div class="employee-profile-group">

                            <label>
                                <i class="fa-solid fa-calendar-check"></i>
                                Start Date
                            </label>

                            <input
                                type="text"
                                value="<?= htmlspecialchars($contract_start); ?>"
                                readonly
                            >

                        </div>

                        <div class="employee-profile-group">

                            <label>
                                <i class="fa-solid fa-calendar-xmark"></i>
                                End Date
                            </label>

                            <input
                                type="text"
                                value="<?= htmlspecialchars($contract_end); ?>"
                                readonly
                            >

                        </div>

                        <div class="employee-profile-group">

                            <label>
                                <i class="fa-solid fa-circle-check"></i>
                                Contract Status
                            </label>

                            <input
                                type="text"
                                value="<?= htmlspecialchars($contract_status); ?>"
                                readonly

                            >

                        </div>

                    </div>

                    <div class="employee-profile-section-header">

                        <div class="employee-profile-section-icon">
                            <i class="fa-solid fa-user-gear"></i>
                        </div>

                        <div>

                            <h2>Account Information</h2>

                            <p>Your system account details.</p>

                        </div>

                    </div>

                    <div class="employee-profile-grid">

                        <div class="employee-profile-group">

                            <label>
                                <i class="fa-solid fa-user-shield"></i>
                                Account Role
                            </label>

                            <input
                                type="text"
                                value="<?= htmlspecialchars(
                                    ucfirst($employee['role'])
                                ); ?>"
                                readonly
                            >

                        </div>

                        <div class="employee-profile-group">

                            <label>
                                <i class="fa-solid fa-circle"></i>
                                Account Status
                            </label>

                            <input
                                type="text"
                                value="<?= htmlspecialchars(
                                    ucfirst($employee['status'])
                                ); ?>"
                                readonly
                            >

                        </div>

                        <div class="employee-profile-group">

                            <label>
                                <i class="fa-solid fa-clock"></i>
                                Last Login
                            </label>

                            <input
                                type="text"
                                value="<?=

                                    !empty($employee['last_login'])
                                    ?
                                    date(
                                        "F d, Y h:i A",
                                        strtotime(
                                            $employee['last_login']
                                        )
                                    )
                                    :
                                    "No login yet";
                                ?>"
                                readonly
                            >

                        </div>

                        <div class="employee-profile-group">

                            <label>
                                <i class="fa-solid fa-calendar-days"></i>
                                Account Created
                            </label>

                            <input
                                type="text"
                                value="<?= htmlspecialchars($created_date); ?>"
                                readonly
                            >

                        </div>

                    </div>

                </div>

            </section>

        </main>

    </div>

</div>

<?php if($success): ?>

<script>

Swal.fire({

    icon:"success",
    title:"Success",
    text:<?= json_encode($success); ?>,
    confirmButtonColor:"#0D47A1"

});

</script>

<?php endif; ?>

<?php if($error): ?>

<script>

Swal.fire({

    icon:"error",
    title:"Error",
    text:<?= json_encode($error); ?>,
    confirmButtonColor:"#C62828"

});

</script>

<?php endif; ?>

<script>

const employeeProfileImage = document.querySelector(

    ".employee-profile-image"

);

if (employeeProfileImage) {

    employeeProfileImage.onerror = function () {

        this.src =

        "../assets/images/avatar/employee_girl.png";

    };

}

</script>

<script src="../assets/js/employee.js"></script>


</body>

</html>