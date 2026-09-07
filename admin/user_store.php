<?php

session_start();


require_once "../auth/admin_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";


if($_SERVER['REQUEST_METHOD'] !== "POST"){

    header("Location: users.php");
    exit;

}

requireCSRFToken("user_add.php", "user_error");


$full_name = trim($_POST['full_name'] ?? "");

$email = trim($_POST['email'] ?? "");

$password = $_POST['password'] ?? "";

$role = $_POST['role'] ?? "";

$gender = $_POST['gender'] ?? "";

$status = $_POST['status'] ?? "";

$employee_id = $_POST['employee_id'] ?? "";


if(
    empty($full_name) ||
    empty($email) ||
    empty($password) ||
    empty($role) ||
    empty($status)
){

    $_SESSION['user_error'] =
    "Please complete all required fields.";

    header("Location: user_add.php");
    exit;

}


if(strlen($password) < 8){

    $_SESSION['user_error'] =
    "Password must be at least 8 characters.";

    header("Location: user_add.php");
    exit;

}


if(
    in_array($role, ['hr', 'employee'], true)
    &&
    empty($employee_id)
){

    $_SESSION['user_error'] =
    "HR and Employee accounts must be linked to an employee record.";

    header("Location: user_add.php");
    exit;

}


if(!filter_var($email,FILTER_VALIDATE_EMAIL)){

    $_SESSION['user_error'] =
    "Invalid email address.";

    header("Location: user_add.php");
    exit;

}


$stmt = $conn->prepare("

    SELECT user_id
    FROM users
    WHERE email = ?
    LIMIT 1

");


$stmt->execute([

    $email

]);


if($stmt->fetch()){

    $_SESSION['user_error'] =
    "Email address already exists.";

    header("Location: user_add.php");
    exit;

}


$profile_picture = "default-profile.png";


if(
    isset($_FILES['profile_picture']) &&
    $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK
){

    $file = $_FILES['profile_picture'];

    $allowedExtensions = [

        "jpg",
        "jpeg",
        "png",
        "webp"

    ];


    $fileExtension = strtolower(

        pathinfo(

            $file['name'],
            PATHINFO_EXTENSION

        )

    );


    if(!in_array($fileExtension,$allowedExtensions)){

        $_SESSION['user_error'] =
        "Invalid profile picture format.";

        header("Location: user_add.php");
        exit;

    }


    if($file['size'] > 5 * 1024 * 1024){

        $_SESSION['user_error'] =
        "Profile picture must not exceed 5MB.";

        header("Location: user_add.php");
        exit;

    }


    $newFileName =

    time()
    . "_"
    . uniqid()
    . "."
    . $fileExtension;


    $uploadDirectory =
    "../uploads/profile/";


    if(!is_dir($uploadDirectory)){

        mkdir(

            $uploadDirectory,
            0777,
            true

        );

    }


    $uploadPath =

    $uploadDirectory . $newFileName;

    
    if(move_uploaded_file(

        $file['tmp_name'],
        $uploadPath

    )){

        $profile_picture = $newFileName;

    }
    else{

        $_SESSION['user_error'] =
        "Failed to upload profile picture.";

        header("Location: user_add.php");
        exit;

    }

}


$hashed_password =

password_hash(

    $password,
    PASSWORD_DEFAULT

);


try{

    $conn->beginTransaction();


        $stmt = $conn->prepare("

        INSERT INTO users

        (

            employee_id,
            full_name,
            email,
            password,
            role,
            gender,
            status,
            must_change_password,
            profile_picture,
            created_at

        )

        VALUES

        (

            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            1,
            ?,
            NOW()

        )

    ");


    $stmt->execute([

        !empty($employee_id)

        ? $employee_id
        : null,

        $full_name,

        $email,

        $hashed_password,

        $role,

        !empty($gender)

        ? $gender
        : null,

        $status,

        $profile_picture

    ]);


    $conn->commit();


    $_SESSION['user_success'] =
    "User account created successfully.";

    header("Location: users.php");
    exit;


}
catch(PDOException $e){


    if($conn->inTransaction()){

        $conn->rollBack();

    }


    if(

        $profile_picture !== "default-profile.png"
        &&

        file_exists(
            "../uploads/profile/" . $profile_picture

        )

    ){

        unlink(

            "../uploads/profile/" . $profile_picture

        );

    }


    $_SESSION['user_error'] =
    "Failed to create user account.";

    header("Location: user_add.php");
    exit;

}