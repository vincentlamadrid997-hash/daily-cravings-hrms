<?php

session_start();


require_once "../auth/admin_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";


$page_title = "Edit User";


$user_error = $_SESSION['user_error'] ?? "";
$user_success = $_SESSION['user_success'] ?? "";

unset($_SESSION['user_error']);
unset($_SESSION['user_success']);


if(

    isset($_POST['user_id'])
    &&
    !isset($_POST['full_name'])

){

    $_SESSION['selected_user'] =

        (int) $_POST['user_id'];

    header(
        "Location: user_edit.php"
    );

    exit;

}


if(

    !isset($_SESSION['selected_user'])

){

    header(
        "Location: users.php"
    );

    exit;

}


$user_id =

(int) $_SESSION['selected_user'];

$employees = $conn->query("

    SELECT

        employee_id,
        employee_code,
        CONCAT(
            first_name,
            ' ',
            IFNULL(middle_name,''),
            ' ',
            last_name
        ) AS employee_name,
        email

    FROM employees
    WHERE employment_status = 'Active'
    ORDER BY first_name ASC

")

->fetchAll(PDO::FETCH_ASSOC);


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
        u.created_at

    FROM users u
    WHERE u.user_id = ?
    LIMIT 1

");

$stmt->execute([

    $user_id

]);


$user = $stmt->fetch(PDO::FETCH_ASSOC);


if(!$user){

    unset(
        $_SESSION['selected_user']
    );

    header(
        "Location: users.php"
    );

    exit;

}

if(

    $_SERVER["REQUEST_METHOD"] === "POST"

){

    requireCSRFToken("user_edit.php", "user_error");

    $employee_id =     

        !empty($_POST['employee_id'])
        ?
        (int) $_POST['employee_id']
        :
        null;

    $full_name =

        trim(
            $_POST['full_name'] ?? ""
        );

    $email =

        trim(
            $_POST['email'] ?? ""
        );

    $role =
        $_POST['role'] ?? "";

    $gender =
        $_POST['gender'] ?? "";

    $status =
        $_POST['status'] ?? "";


    if(

        empty($full_name)
        ||
        empty($email)
        ||
        empty($role)
        ||
        empty($gender)
        ||
        empty($status)

    ){

        $_SESSION['user_error'] =
        "Please complete all required fields.";

    }

    elseif(

        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )

    ){

        $_SESSION['user_error'] =
        "Invalid email address.";

    }

    else{

        $update = $conn->prepare("

            UPDATE users SET
                employee_id = ?,
                full_name = ?,
                email = ?,
                role = ?,
                gender = ?,
                status = ?

            WHERE user_id = ?

        ");

        $update->execute([

            $employee_id,
            $full_name,
            $email,
            $role,
            $gender,
            $status,
            $user_id

        ]);

        $_SESSION['user_success'] =
        "User updated successfully.";

        unset(
            $_SESSION['selected_user']
        );

        header(
            "Location: users.php"
        );

        exit;

    }

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

        trim(
            $user['gender'] ?? ""
        )

    );

    $role = strtolower(

        trim(
            $user['role'] ?? ""
        )

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


$role_class =

strtolower(
    $user['role']
);


$status_class =

strtolower(
    $user['status']
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

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" >
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
                            <i class="fa-solid fa-user-pen"></i>
                            Edit User
                        </h1>

                        <p>Update system account information and employee connection.</p>

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
                            <i class="fa-solid fa-user-gear"></i>
                        </div>

                        <div>

                            <h2>Edit User Account</h2>

                            <p>Modify user details and linked employee information.</p>

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
                                <?= htmlspecialchars(
                                    $user['full_name']
                                ); ?>
                            </h2>

                            <p class="admin-user-view-email">

                                <i class="fa-solid fa-envelope"></i>

                                <?= htmlspecialchars(
                                    $user['email']
                                ); ?>

                            </p>

                            <div class="admin-user-view-badges">

                                <span class="admin-user-role-badge">

                                    <i class="fa-solid fa-user-shield"></i>

                                    <?= ucfirst(
                                        htmlspecialchars(
                                            $user['role']
                                        )
                                    ); ?>

                                </span>

                                <span class="admin-user-status-badge <?= $status_class; ?>">

                                    <i class="fa-solid fa-circle"></i>

                                    <?= ucfirst(
                                        htmlspecialchars(
                                            $user['status']
                                        )
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

                    <form
                        method="POST"
                        action="user_edit.php"
                        enctype="multipart/form-data"
                        id="userForm"
                    >

                        <?php csrfField(); ?>

                        <input
                            type="hidden"
                            name="user_id"
                            value="<?= $user['user_id']; ?>"
                        >

                        <div class="admin-user-grid">

                            <div class="admin-user-group">

                                <label>
                                    <i class="fa-solid fa-image"></i>
                                    Profile Picture
                                </label>

                                <input
                                    type="file"
                                    name="profile_picture"
                                    id="profile_picture"
                                    accept="image/*"
                                >

                                <span class="admin-user-help">
                                    Upload new profile image (optional)
                                </span>

                            </div>

                            <div class="admin-user-group">

                                <label>
                                    <i class="fa-solid fa-user"></i>
                                    Full Name
                                </label>

                                <input
                                    type="text"
                                    name="full_name"
                                    value="<?= htmlspecialchars($user['full_name']); ?>"
                                    required
                                >

                                <div class="admin-error-message"></div>

                            </div>

                            <div class="admin-user-group">

                                <label>
                                    <i class="fa-solid fa-envelope"></i>
                                    Email Address
                                </label>

                                <input
                                    type="email"
                                    name="email"
                                    value="<?= htmlspecialchars($user['email']); ?>"
                                    required
                                >

                                <div class="admin-error-message"></div>

                            </div>

                            <div class="admin-user-group">

                                <label>
                                    <i class="fa-solid fa-user-shield"></i>
                                    User Role
                                </label>

                                <select
                                    name="role"
                                    required
                                >

                                    <option value="">Select Role</option>

                                    <option
                                        value="admin"
                                        <?= $user['role'] == "admin" ? "selected" : ""; ?>

                                    >

                                        Admin
                                    </option>

                                    <option
                                        value="hr"
                                        <?= $user['role'] == "hr" ? "selected" : ""; ?>

                                    >

                                        HR
                                    </option>

                                    <option
                                        value="employee"
                                        <?= $user['role'] == "employee" ? "selected" : ""; ?>

                                    >

                                        Employee
                                    </option>

                                </select>

                                <div class="admin-error-message"></div>

                            </div>

                            <div class="admin-user-group">

                                <label>
                                    <i class="fa-solid fa-id-badge"></i>
                                    Linked Employee
                                </label>

                                <select
                                    name="employee_id"
                                    id="employee_id"
                                >

                                    <option value="">No Employee Linked</option>

                                    <?php foreach($employees as $employee): ?>

                                        <option
                                            value="<?= $employee['employee_id']; ?>"
                                            data-name="<?= htmlspecialchars($employee['employee_name']); ?>"
                                            data-email="<?= htmlspecialchars($employee['email']); ?>"

                                            <?=
                                                $user['employee_id'] == $employee['employee_id']
                                                ?
                                                "selected"
                                                :
                                                ""
                                            ?>
                                        >

                                            <?= htmlspecialchars(
                                                $employee['employee_code']
                                            ); ?>
                                            -

                                            <?= htmlspecialchars(
                                                $employee['employee_name']
                                            ); ?>
                                        </option>

                                    <?php endforeach; ?>

                                </select>

                                <span class="admin-user-help">
                                    Select employee to automatically connect this account.
                                </span>

                            </div>

                            <div class="admin-user-group">

                                <label>
                                    <i class="fa-solid fa-venus-mars"></i>
                                    Gender
                                </label>

                                <select
                                    name="gender"
                                    required
                                >

                                    <option value="">Select Gender</option>

                                    <option
                                        value="Male"
                                        <?= $user['gender'] == "Male" ? "selected" : ""; ?>
                                    >

                                        Male
                                    </option>

                                    <option
                                        value="Female"
                                        <?= $user['gender'] == "Female" ? "selected" : ""; ?>

                                    >

                                        Female
                                    </option>

                                </select>

                                <div class="admin-error-message"></div>

                            </div>

                            <div class="admin-user-group">

                                <label>
                                    <i class="fa-solid fa-circle-check"></i>
                                    Account Status
                                </label>

                                <select
                                    name="status"
                                    required
                                >

                                    <option value="">Select Status</option>

                                    <option
                                        value="active"
                                        <?= $user['status'] == "active" ? "selected" : ""; ?>
                                    >

                                        Active
                                    </option>

                                    <option
                                        value="inactive"
                                        <?= $user['status'] == "inactive" ? "selected" : ""; ?>

                                    >

                                        Inactive
                                    </option>

                                    <option
                                        value="locked"
                                        <?= $user['status'] == "locked" ? "selected" : ""; ?>

                                    >
                                        Locked
                                    </option>

                                </select>

                                <div class="admin-error-message"></div>

                            </div>

                            <div class="admin-user-group admin-user-full">

                                <label>
                                    <i class="fa-solid fa-image"></i>
                                    Current Profile Picture
                                </label>

                                <div class="admin-user-image-preview">

                                    <img
                                        src="<?= htmlspecialchars($profile_image); ?>"
                                        id="previewImage"
                                        alt="Profile Preview"
                                    >

                                </div>

                            </div>

                        </div>

                        <div class="admin-user-actions">

                                                        <button
                                type="submit"
                                id="userEditSaveBtn"
                                disabled
                                class="admin-user-btn save"

                            >

                                <i class="fa-solid fa-save"></i>
                                Update User
                            </button>

                        </div>

                    </form>

                </div>

            </section>

        </main>

    </div>

</div>

<div
    id="userAlertData"
    data-success="<?= htmlspecialchars($user_success); ?>"
    data-error="<?= htmlspecialchars($user_error); ?>"
></div>


<script>
(function () {

    const form = document.querySelector('form[action="user_edit.php"]');
    const saveBtn = document.getElementById("userEditSaveBtn");
    const initialState = new URLSearchParams(new FormData(form)).toString();

    function checkForChanges() {
        const currentState = new URLSearchParams(new FormData(form)).toString();
        saveBtn.disabled = (currentState === initialState);
    }

    form.addEventListener("input", checkForChanges);
    form.addEventListener("change", checkForChanges);

    form.addEventListener("submit", function (e) {

        e.preventDefault();

        Swal.fire({
            icon: "question",
            title: "Save changes?",
            text: "This will update this user's account.",
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

})();
</script>

<script src="../assets/js/admin.js"></script>


</body>


</html>