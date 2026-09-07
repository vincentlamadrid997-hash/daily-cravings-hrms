<?php

session_start();

require_once "../auth/admin_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";


$page_title = "View User";


if(isset($_POST['user_id'])){

    $_SESSION['selected_user'] = (int) $_POST['user_id'];

}

if(!isset($_SESSION['selected_user'])){

    header("Location: users.php");
    exit;

}

$user_id = (int) $_SESSION['selected_user'];


$stmt = $conn->prepare("

    SELECT

        u.user_id,
        u.employee_id,
        u.full_name,
        u.email,
        u.role,
        u.gender,
        u.status,
        u.profile_picture,
        u.created_at,

        e.employee_code,

        CONCAT(

            e.first_name,
            ' ',
            IFNULL(e.middle_name,''),
            ' ',
            e.last_name

        ) AS employee_name,

        d.department_name,

        p.position_name,

        e.employment_status

    FROM users u

    LEFT JOIN employees e
    ON u.employee_id = e.employee_id

    LEFT JOIN departments d
    ON e.department_id = d.department_id

    LEFT JOIN positions p
    ON e.position_id = p.position_id

    WHERE u.user_id = ?
    LIMIT 1

");


$stmt->execute([

    $user_id

]);


$user = $stmt->fetch(PDO::FETCH_ASSOC);


if(!$user){

    unset($_SESSION['selected_user']);

    header("Location: users.php");
    exit;

}


if(

    !empty($user['profile_picture'])
    &&
    $user['profile_picture'] !== "default-profile.png"

){

    $profile_image =
    "../uploads/profile/"
    .
    $user['profile_picture'];

}
else{

    $gender = strtolower(
        trim($user['gender'] ?? "")
    );

    $role = strtolower(
        trim($user['role'] ?? "")
    );


    if($gender === "female"){

        switch($role){

            case "admin":

                $profile_image =
                "../assets/images/avatar/admin_girl.png";

            break;

            case "hr":

                $profile_image =
                "../assets/images/avatar/hr_girl.png";

            break;

            default:

                $profile_image =
                "../assets/images/avatar/employee_girl.png";

        }

    }
    else{

        switch($role){

            case "admin":

                $profile_image =
                "../assets/images/avatar/admin_boy.png";

            break;

            case "hr":

                $profile_image =
                "../assets/images/avatar/hr_boy.png";

            break;

            default:

                $profile_image =
                "../assets/images/avatar/employee_boy.png";

        }

    }

}


$employee_code =
$user['employee_code'] ?? "N/A";

$employee_name =
$user['employee_name'] ?? "N/A";

$department =
$user['department_name'] ?? "N/A";

$position =
$user['position_name'] ?? "N/A";

$employment_status =
$user['employment_status'] ?? "N/A";


$created_date = !empty($user['created_at'])
?

date(

    "F d, Y",
    strtotime($user['created_at'])

)

:
"N/A";

$status_class = strtolower(

    $user['status']

);

$role_class = strtolower(

    $user['role']

);


?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($page_title); ?> | Daily Cravings Foods Inc.</title>

    <link rel="stylesheet" href="../assets/css/global.css">

    <link rel="stylesheet" href="../assets/css/admin.css">

    <link rel="stylesheet" href="../assets/css/crud_admin.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>


<body>

<div class="admin-wrapper">

    <?php require_once "includes/sidebar.php"; ?>

    <div class="admin-content-wrapper">

        <?php require_once "includes/header.php"; ?>

        <main class="admin-main">

            <section class="admin-user-page">

                <div class="admin-user-header">

                    <div class="admin-user-title">

                        <h1>
                            <i class="fa-solid fa-user"></i>
                            View User
                        </h1>

                        <p>View complete information about this system account.</p>

                    </div>

                    <a
                        href="users.php"
                        class="admin-user-back-btn"

                    >

                        <i class="fa-solid fa-arrow-left"></i>
                        Back to Users
                    </a>

                </div>

                <div class="admin-user-card">

                    <div class="admin-user-card-header">

                        <div class="admin-user-card-icon">
                            <i class="fa-solid fa-user-shield"></i>
                        </div>

                        <div>

                            <h2>User Account Information</h2>

                            <p>View account details and linked employee information.</p>

                        </div>

                    </div>

                    <div class="admin-user-view-profile">

                        <div class="admin-user-view-avatar">

                            <img
                                src="<?= htmlspecialchars($profile_image); ?>"
                                alt="User Avatar"
                            >

                        </div>

                        <div class="admin-user-view-info">

                            <h2>
                                <?= htmlspecialchars($user['full_name']); ?>
                            </h2>

                            <p class="admin-user-view-email">

                                <i class="fa-solid fa-envelope"></i>
                                <?= htmlspecialchars($user['email']); ?>

                            </p>

                            <div class="admin-user-view-badges">

                                <span class="admin-user-role-badge <?= $role_class; ?>">

                                    <?php if($role_class === "admin"): ?>
                                        <i class="fa-solid fa-user-shield"></i>

                                    <?php elseif($role_class === "hr"): ?>
                                        <i class="fa-solid fa-user-tie"></i>

                                    <?php else: ?>
                                        <i class="fa-solid fa-user"></i>

                                    <?php endif; ?>

                                    <?= htmlspecialchars(
                                        ucfirst($user['role'])
                                    ); ?>

                                </span>

                                <span class="admin-user-status-badge <?= $status_class; ?>">

                                    <i class="fa-solid fa-circle"></i>

                                    <?= htmlspecialchars(
                                        ucfirst($user['status'])

                                    ); ?>

                                </span>

                                <?php if(!empty($user['gender'])): ?>

                                    <span class="admin-user-gender-badge">

                                        <?php if(

                                            strtolower($user['gender'])
                                            ===
                                            "male"

                                        ): ?>

                                            <i class="fa-solid fa-mars"></i>

                                        <?php else: ?>

                                            <i class="fa-solid fa-venus"></i>

                                        <?php endif; ?>

                                        <?= htmlspecialchars(

                                            $user['gender']

                                        ); ?>

                                    </span>

                                <?php endif; ?>

                            </div>

                        </div>

                    </div>

                    <div class="admin-user-grid">

                        <div class="admin-user-group">

                            <label>
                                <i class="fa-solid fa-user"></i>
                                Full Name
                            </label>

                            <input
                                type="text"
                                value="<?= htmlspecialchars(
                                    $user['full_name']
                                ); ?>"
                                readonly
                            >

                        </div>

                        <div class="admin-user-group">

                            <label>
                                <i class="fa-solid fa-envelope"></i>
                                Email Address
                            </label>

                            <input
                                type="text"
                                value="<?= htmlspecialchars(
                                    $user['email']
                                ); ?>"
                                readonly
                            >

                        </div>

                        <div class="admin-user-group">

                            <label>
                                <i class="fa-solid fa-user-gear"></i>
                                User Role
                            </label>

                            <input
                                type="text"
                                value="<?= htmlspecialchars(
                                    ucfirst($user['role'])
                                ); ?>"
                                readonly
                            >

                        </div>

                        <div class="admin-user-group">

                            <label>
                                <i class="fa-solid fa-venus-mars"></i>
                                Gender
                            </label>

                            <input
                                type="text"
                                value="<?= !empty($user['gender'])
                                    ?
                                    htmlspecialchars($user['gender'])
                                    :
                                    'N/A';
                                ?>"
                                readonly
                            >

                        </div>

                        <div class="admin-user-group">

                            <label>
                                <i class="fa-solid fa-circle-check"></i>
                                Account Status
                            </label>

                            <input
                                type="text"
                                value="<?= htmlspecialchars(
                                    ucfirst($user['status'])
                                ); ?>"
                                readonly
                            >

                        </div>

                        <div class="admin-user-group">

                            <label>
                                <i class="fa-solid fa-calendar-days"></i>
                                Created Date
                            </label>

                            <input
                                type="text"
                                value="<?= htmlspecialchars(
                                    $created_date
                                ); ?>"
                                readonly
                            >

                        </div>

                    </div>

                    <div

                        class="admin-user-card-header"
                        style="margin-top:35px;"

                    >

                        <div class="admin-user-card-icon">
                            <i class="fa-solid fa-id-card"></i>
                        </div>

                        <div>

                            <h2>Employee Information</h2>

                            <p>Details connected to this user account.</p>

                        </div>

                    </div>

                    <div class="admin-user-grid">

                        <div class="admin-user-group">

                            <label>
                                <i class="fa-solid fa-barcode"></i>
                                Employee Code
                            </label>

                            <input
                                type="text"
                                value="<?= htmlspecialchars(
                                    $employee_code
                                ); ?>"
                                readonly
                            >

                        </div>

                        <div class="admin-user-group">

                            <label>
                                <i class="fa-solid fa-user-tie"></i>
                                Linked Employee
                            </label>

                            <input
                                type="text"
                                value="<?= htmlspecialchars(
                                    $employee_name
                                ); ?>"
                                readonly
                            >

                        </div>

                        <div class="admin-user-group">

                            <label>
                                <i class="fa-solid fa-building"></i>
                                Department
                            </label>

                            <input
                                type="text"
                                value="<?= htmlspecialchars(
                                    $department
                                ); ?>"
                                readonly
                            >

                        </div>

                        <div class="admin-user-group">

                            <label>
                                <i class="fa-solid fa-briefcase"></i>
                                Position
                            </label>

                            <input
                                type="text"
                                value="<?= htmlspecialchars(
                                    $position
                                ); ?>"
                                readonly
                            >

                        </div>

                        <div class="admin-user-group">

                            <label>
                                <i class="fa-solid fa-user-check"></i>
                                Employment Status
                            </label>

                            <input
                                type="text"
                                value="<?= htmlspecialchars(
                                    $employment_status
                                ); ?>"
                                readonly
                            >

                        </div>

                    </div>

                    <div class="admin-user-actions">

                        <form
                            action="user_edit.php"
                            method="POST"
                        >

                            <?php csrfField(); ?>

                            <input
                                type="hidden"
                                name="user_id"
                                value="<?= $user['user_id']; ?>"
                            >

                            <button
                                type="submit"
                                class="admin-user-btn save"
                            >

                                <i class="fa-solid fa-pen"></i>
                                Edit User
                            </button>

                        </form>

                    </div>

                </div>

            </section>

        </main>

    </div>

</div>


<script src="../assets/js/admin.js"></script>


</body>

</html>