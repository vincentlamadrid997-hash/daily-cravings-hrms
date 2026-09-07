<?php


/*
==========================================================
        EMPLOYEE CHANGE PASSWORD PAGE

        Daily Cravings Foods Inc.
        Employee Management System

==========================================================
*/


session_start();


date_default_timezone_set("Asia/Manila");



require_once "../auth/employee_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";



$page_title = "Change Password";







/*
==========================================================
                VALIDATE EMPLOYEE SESSION
==========================================================
*/


$employee_id = $_SESSION["employee_id"] ?? 0;



if(!$employee_id){


    header(
        "Location: ../auth/login.php"
    );


    exit;


}









/*
==========================================================
                    HELPER FUNCTION
==========================================================
*/


function clean($value)

{

    return htmlspecialchars(

        $value ?? "",

        ENT_QUOTES,

        "UTF-8"

    );

}









/*
==========================================================
                SESSION MESSAGE
==========================================================
*/


$success = $_SESSION["password_success"] ?? "";

$error = $_SESSION["password_error"] ?? "";



unset($_SESSION["password_success"]);

unset($_SESSION["password_error"]);









/*
==========================================================
                FETCH USER PASSWORD
==========================================================
*/


$stmt = $conn->prepare("


SELECT


    user_id,

    password



FROM users



WHERE employee_id = :employee_id



LIMIT 1



");





$stmt->execute([



    ":employee_id" => $employee_id



]);





$user = $stmt->fetch(PDO::FETCH_ASSOC);






if(!$user){


    $_SESSION["password_error"] =

    "User account not found.";



    header(

        "Location: profile.php"

    );


    exit;


}









/*
==========================================================
            UPDATE PASSWORD PROCESS
==========================================================
*/


if($_SERVER["REQUEST_METHOD"] === "POST"){

    requireCSRFToken("profile_change_password.php", "password_error");


    $current_password = $_POST["current_password"] ?? "";



    $new_password = $_POST["new_password"] ?? "";



    $confirm_password = $_POST["confirm_password"] ?? "";








/*
==========================================================
                    VALIDATION
==========================================================
*/





if(

    empty($current_password)

    ||

    empty($new_password)

    ||

    empty($confirm_password)

){


    $error =

    "All password fields are required.";



}







elseif(

    !password_verify(

        $current_password,

        $user["password"]

    )

){


    $error =

    "Current password is incorrect.";



}







elseif(

    $new_password === $current_password

){


    $error =

    "New password cannot be the same as your current password.";



}







elseif(

    strlen($new_password) < 8

){


    $error =

    "Password must be at least 8 characters long.";



}







elseif(

    $new_password !== $confirm_password

){


    $error =

    "New password and confirmation password do not match.";



}







else{






/*
==========================================================
                HASH NEW PASSWORD
==========================================================
*/


$new_hashed_password = password_hash(


    $new_password,


    PASSWORD_DEFAULT


);







/*
==========================================================
                UPDATE USERS TABLE
==========================================================
*/


try{



$update = $conn->prepare("


UPDATE users


SET


    password = :password



WHERE user_id = :user_id



");






$update->execute([



    ":password" => $new_hashed_password,


    ":user_id" => $user["user_id"]



]);







$_SESSION["password_success"] =

"Password changed successfully.";



header(

    "Location: profile.php"

);


exit;



}

catch(Exception $e){



$_SESSION["password_error"] =

"Unable to change password. Please try again.";



header(

    "Location: profile_change_password.php"

);


exit;



}





}



}



?>

<!DOCTYPE html>

<html lang="en">


<head>


    <meta charset="UTF-8">


    <meta 
        name="viewport" 
        content="width=device-width, initial-scale=1.0"
    >



    <title>

        <?= clean($page_title); ?>

        | Daily Cravings Foods Inc.

    </title>






    <!-- GLOBAL CSS -->

    <link
        rel="stylesheet"
        href="../assets/css/global.css"
    >






    <!-- EMPLOYEE CSS -->

    <link
        rel="stylesheet"
        href="../assets/css/employee.css"
    >






    <!-- PROFILE PASSWORD CSS -->

    <link
        rel="stylesheet"
        href="../assets/css/crud_employee.css"
    >






    <!-- FONT AWESOME -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    >






    <!-- SWEETALERT -->

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



</head>








<body>






<div class="employee-wrapper">









    <?php require_once "includes/sidebar.php"; ?>









    <div class="employee-content-wrapper">







        <?php require_once "includes/header.php"; ?>











        <main class="employee-main">







            <section class="employee-password-page">










                <!-- ======================================================
                        PAGE HEADER
                ======================================================= -->





                <div class="employee-password-header">






                    <div class="employee-password-title">






                        <h1>



                            <i class="fa-solid fa-lock"></i>



                            Change Password



                        </h1>








                        <p>



                            Update your account password securely.



                        </p>






                    </div>









                    <a
                        href="profile.php"
                        class="employee-password-back-btn"
                    >



                        <i class="fa-solid fa-arrow-left"></i>



                        Back to Profile



                    </a>







                </div>














                <!-- ======================================================
                        PASSWORD CARD
                ======================================================= -->






                <div class="employee-password-card">







                    <div class="employee-password-card-header">






                        <div class="employee-password-icon">



                            <i class="fa-solid fa-key"></i>



                        </div>









                        <div>



                            <h2>



                                Account Security



                            </h2>






                            <p>



                                Change your password to keep your account protected.



                            </p>






                        </div>







                    </div>













                    <!-- ======================================================
                            PASSWORD FORM
                    ======================================================= -->







                                        <form
                        method="POST"
                        action="profile_change_password.php"
                        class="employee-password-form"
                    >

                        <?php csrfField(); ?>










                        <div class="employee-password-group">





                            <label>



                                <i class="fa-solid fa-lock"></i>



                                Current Password



                            </label>







                            <div class="employee-password-input">





                                <input
                                    type="password"
                                    name="current_password"
                                    placeholder="Enter current password"
                                    required
                                >







                                <button
                                    type="button"
                                    class="toggle-password"
                                >



                                    <i class="fa-solid fa-eye"></i>



                                </button>






                            </div>







                        </div>














                        <div class="employee-password-group">





                            <label>



                                <i class="fa-solid fa-key"></i>



                                New Password



                            </label>







                            <div class="employee-password-input">





                                <input
                                    type="password"
                                    name="new_password"
                                    placeholder="Enter new password"
                                    required
                                >







                                <button
                                    type="button"
                                    class="toggle-password"
                                >



                                    <i class="fa-solid fa-eye"></i>



                                </button>






                            </div>







                        </div>














                        <div class="employee-password-group">





                            <label>



                                <i class="fa-solid fa-check"></i>



                                Confirm New Password



                            </label>







                            <div class="employee-password-input">





                                <input
                                    type="password"
                                    name="confirm_password"
                                    placeholder="Confirm new password"
                                    required
                                >







                                <button
                                    type="button"
                                    class="toggle-password"
                                >



                                    <i class="fa-solid fa-eye"></i>



                                </button>






                            </div>







                        </div>














                        <!-- PASSWORD REQUIREMENTS -->






                        <div class="employee-password-note">





                            <h3>



                                <i class="fa-solid fa-circle-info"></i>



                                Password Requirements



                            </h3>







                            <ul>



                                <li>
                                    Minimum 8 characters
                                </li>


                                <li>
                                    Use a strong password
                                </li>


                                <li>
                                    Avoid using your old password
                                </li>


                                <li>
                                    Keep your password private
                                </li>



                            </ul>





                        </div>















                        <!-- ======================================================
                                ACTION BUTTONS
                        ======================================================= -->








                        <div class="employee-password-actions">
















                            <button
                                type="submit"
                                class="employee-password-save-btn"
                            >



                                <i class="fa-solid fa-floppy-disk"></i>



                                Update Password



                            </button>







                        </div>










                    </form>









                </div>









            </section>








        </main>










    </div>









</div>






<!-- ======================================================
            SWEETALERT SUCCESS MESSAGE
======================================================= -->


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









<!-- ======================================================
            SWEETALERT ERROR MESSAGE
======================================================= -->


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












<!-- ======================================================
            SHOW / HIDE PASSWORD SCRIPT
======================================================= -->


<script>


document
.querySelectorAll(".toggle-password")
.forEach(button => {



    button.addEventListener(
    "click",
    function(){



        const input =

        this
        .previousElementSibling;





        if(input.type === "password"){



            input.type = "text";



            this.innerHTML =

            '<i class="fa-solid fa-eye-slash"></i>';



        }

        else{



            input.type = "password";



            this.innerHTML =

            '<i class="fa-solid fa-eye"></i>';



        }




    });



});






</script>













<!-- ======================================================
            PASSWORD FRONTEND VALIDATION
======================================================= -->



<script>


document

.querySelector(".employee-password-form")

.addEventListener(

"submit",

function(e){






const newPassword =

document

.querySelector(
'input[name="new_password"]'
)

.value;






const confirmPassword =

document

.querySelector(
'input[name="confirm_password"]'
)

.value;






if(newPassword.length < 8){



    e.preventDefault();




    Swal.fire({


        icon:"warning",


        title:"Weak Password",


        text:"Password must contain at least 8 characters.",


        confirmButtonColor:"#F9A825"



    });



    return false;



}








if(newPassword !== confirmPassword){



    e.preventDefault();




    Swal.fire({


        icon:"warning",


        title:"Password Mismatch",


        text:"New password and confirmation password do not match.",


        confirmButtonColor:"#F9A825"



    });



    return false;



}


e.preventDefault();

Swal.fire({

    icon: "question",
    title: "Update your password?",
    text: "You'll need to use your new password the next time you log in.",
    showCancelButton: true,
    confirmButtonText: "Yes, Update",
    cancelButtonText: "Cancel",
    confirmButtonColor: "#0D47A1",
    cancelButtonColor: "#90A4AE",
    reverseButtons: true

}).then(function (result) {

    if (result.isConfirmed) {

        document
            .querySelector(".employee-password-form")
            .submit();

    }

});



});




</script>












<!-- ======================================================
            EMPLOYEE GLOBAL SCRIPT
======================================================= -->


<script src="../assets/js/employee.js"></script>









</body>


</html>