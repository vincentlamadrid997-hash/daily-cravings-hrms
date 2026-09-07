<?php


/*
==========================================================
            EMPLOYEE PROFILE EDIT PAGE

        Daily Cravings Foods Inc.
        Employee Management System

==========================================================
*/


session_start();


date_default_timezone_set("Asia/Manila");



require_once "../auth/employee_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";



$page_title = "Edit Profile";





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


$success = $_SESSION["profile_success"] ?? "";

$error = $_SESSION["profile_error"] ?? "";



unset($_SESSION["profile_success"]);

unset($_SESSION["profile_error"]);









/*
==========================================================
                FETCH EMPLOYEE DATA
==========================================================
*/


$stmt = $conn->prepare("

SELECT


    e.employee_id,

    e.employee_code,

    e.first_name,

    e.middle_name,

    e.last_name,

    e.email,

    e.phone,

    e.birthdate,

    e.gender,

    e.civil_status,

    e.address,

    e.basic_salary,

    e.employment_status,

    e.hire_date,


    d.department_name,


    p.position_name



FROM employees e



LEFT JOIN departments d

ON e.department_id = d.department_id



LEFT JOIN positions p

ON e.position_id = p.position_id



WHERE e.employee_id = :employee_id



LIMIT 1


");





$stmt->execute([


    ":employee_id" => $employee_id


]);





$employee = $stmt->fetch(PDO::FETCH_ASSOC);






if(!$employee){


    $_SESSION["profile_error"] =

    "Employee record not found.";



    header(
        "Location: dashboard.php"
    );


    exit;


}












/*
==========================================================
                UPDATE PROFILE
==========================================================
*/


if($_SERVER["REQUEST_METHOD"] === "POST"){

    requireCSRFToken("profile_edit.php", "profile_error");


    $first_name = trim(

        $_POST["first_name"] ?? ""

    );



    $middle_name = trim(

        $_POST["middle_name"] ?? ""

    );



    $last_name = trim(

        $_POST["last_name"] ?? ""

    );



    $email = strtolower(

        trim(

            $_POST["email"] ?? ""

        )

    );



    $phone = trim(

        $_POST["phone"] ?? ""

    );



    $birthdate = $_POST["birthdate"] ?? "";



    $gender = $_POST["gender"] ?? "";



    $civil_status = $_POST["civil_status"] ?? "";



    $address = trim(

        $_POST["address"] ?? ""

    );









/*
==========================================================
                    VALIDATION
==========================================================
*/


if(

    empty($first_name)

    ||

    empty($last_name)

    ||

    empty($email)

){


    $error =

    "First name, last name, and email are required.";



}







elseif(

    !filter_var(

        $email,

        FILTER_VALIDATE_EMAIL

    )

){


    $error =

    "Please enter a valid email address.";



}







else{






/*
==========================================================
            CHECK DUPLICATE EMAIL
==========================================================
*/


$currentEmail = strtolower(

    trim(

        $employee["email"]

    )

);





if($email !== $currentEmail){



    $checkEmail = $conn->prepare("


        SELECT employee_id


        FROM employees


        WHERE LOWER(email) = :email


        AND employee_id != :employee_id


        LIMIT 1



    ");





    $checkEmail->execute([



        ":email" => $email,


        ":employee_id" => $employee_id



    ]);







    if($checkEmail->fetch()){



        $error =

        "Email address is already in use.";



    }



}









/*
==========================================================
                SAVE DATA
==========================================================
*/


if(empty($error)){



try{


$conn->beginTransaction();






$update = $conn->prepare("


UPDATE employees


SET



    first_name = :first_name,


    middle_name = :middle_name,


    last_name = :last_name,


    email = :email,


    phone = :phone,


    birthdate = :birthdate,


    gender = :gender,


    civil_status = :civil_status,


    address = :address



WHERE employee_id = :employee_id



");







$update->execute([



    ":first_name" => $first_name,


    ":middle_name" => $middle_name,


    ":last_name" => $last_name,


    ":email" => $email,


    ":phone" => $phone,


    ":birthdate" => $birthdate,


    ":gender" => $gender,


    ":civil_status" => $civil_status,


    ":address" => $address,


    ":employee_id" => $employee_id



]);












/*
==========================================================
                UPDATE USERS TABLE
==========================================================
*/


$full_name = trim(

    $first_name

    ." ".

    $middle_name

    ." ".

    $last_name

);






$updateUser = $conn->prepare("


UPDATE users


SET



    full_name = :full_name,


    gender = :gender



WHERE employee_id = :employee_id



");





$updateUser->execute([



    ":full_name" => $full_name,


    ":gender" => $gender,


    ":employee_id" => $employee_id



]);







$conn->commit();







$_SESSION["profile_success"] =

"Profile updated successfully.";



header(

    "Location: profile.php"

);


exit;





}

catch(Exception $e){



$conn->rollBack();




$_SESSION["profile_error"] =

"Unable to update profile.";



header(

    "Location: profile_edit.php"

);


exit;



}






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





    <!-- PROFILE EDIT CSS -->

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





            <section class="employee-profile-edit-page">







                <!-- ======================================================
                            PAGE HEADER
                ======================================================= -->


                <div class="employee-profile-edit-header">





                    <div class="employee-profile-edit-title">



                        <h1>


                            <i class="fa-solid fa-user-pen"></i>


                            Edit Profile



                        </h1>






                        <p>


                            Update your personal information and contact details.



                        </p>




                    </div>







                    <a
                        href="profile.php"
                        class="employee-profile-back-btn"
                    >



                        <i class="fa-solid fa-arrow-left"></i>


                        Back to Profile



                    </a>





                </div>









                <!-- ======================================================
                        PROFILE EDIT CARD
                ======================================================= -->





                <div class="employee-profile-edit-card">





                    <div class="employee-profile-edit-card-header">





                        <div class="employee-profile-edit-icon">



                            <i class="fa-solid fa-user"></i>



                        </div>







                        <div>



                            <h2>


                                Personal Information



                            </h2>






                            <p>


                                Modify your employee profile details.



                            </p>





                        </div>





                    </div>









                    <!-- ==================================================
                            PROFILE FORM START
                    =================================================== -->





                                        <form
                        method="POST"
                        action="profile_edit.php"
                        class="employee-profile-edit-form"
                    >

                        <?php csrfField(); ?>









                        <!-- ==================================================
                                BASIC INFORMATION
                        =================================================== -->





                        <div class="employee-profile-edit-grid">







                            <div class="employee-profile-edit-group">



                                <label>


                                    <i class="fa-solid fa-id-card"></i>


                                    Employee Code



                                </label>






                                <input
                                    type="text"
                                    value="<?= clean($employee["employee_code"]); ?>"
                                    readonly
                                >




                            </div>









                            <div class="employee-profile-edit-group">



                                <label>


                                    <i class="fa-solid fa-building"></i>


                                    Department



                                </label>






                                <input
                                    type="text"
                                    value="<?= clean($employee["department_name"]); ?>"
                                    readonly
                                >




                            </div>









                            <div class="employee-profile-edit-group">



                                <label>


                                    <i class="fa-solid fa-briefcase"></i>


                                    Position



                                </label>






                                <input
                                    type="text"
                                    value="<?= clean($employee["position_name"]); ?>"
                                    readonly
                                >




                            </div>









                            <div class="employee-profile-edit-group">



                                <label>


                                    <i class="fa-solid fa-calendar"></i>


                                    Hire Date



                                </label>






                                <input
                                    type="text"
                                    value="<?= clean($employee["hire_date"]); ?>"
                                    readonly
                                >




                            </div>





                        </div>












                        <!-- ==================================================
                                PERSONAL DETAILS
                        =================================================== -->





                        <div class="employee-profile-section-title">


                            <i class="fa-solid fa-address-card"></i>


                            Personal Details



                        </div>








                        <div class="employee-profile-edit-grid">







                            <div class="employee-profile-edit-group">



                                <label>


                                    First Name



                                </label>






                                <input
                                    type="text"
                                    name="first_name"
                                    value="<?= clean($employee["first_name"]); ?>"
                                    required
                                >




                            </div>









                            <div class="employee-profile-edit-group">



                                <label>


                                    Middle Name



                                </label>






                                <input
                                    type="text"
                                    name="middle_name"
                                    value="<?= clean($employee["middle_name"]); ?>"
                                >




                            </div>









                            <div class="employee-profile-edit-group">



                                <label>


                                    Last Name



                                </label>






                                <input
                                    type="text"
                                    name="last_name"
                                    value="<?= clean($employee["last_name"]); ?>"
                                    required
                                >




                            </div>









                            <div class="employee-profile-edit-group">



                                <label>


                                    Birthdate



                                </label>






                                <input
                                    type="date"
                                    name="birthdate"
                                    value="<?= clean($employee["birthdate"]); ?>"
                                >




                            </div>






                        </div>












                        <!-- ==================================================
                                CONTACT INFORMATION
                        =================================================== -->





                        <div class="employee-profile-section-title">


                            <i class="fa-solid fa-phone"></i>


                            Contact Information



                        </div>








                        <div class="employee-profile-edit-grid">







                            <div class="employee-profile-edit-group">



                                <label>


                                    Email Address



                                </label>






                                <input
                                    type="email"
                                    name="email"
                                    value="<?= clean($employee["email"]); ?>"
                                    required
                                >




                            </div>









                            <div class="employee-profile-edit-group">



                                <label>


                                    Phone Number



                                </label>






                                <input
                                    type="text"
                                    name="phone"
                                    value="<?= clean($employee["phone"]); ?>"
                                >




                            </div>









                            <div class="employee-profile-edit-group full-width">



                                <label>


                                    Address



                                </label>






                                <textarea
                                    name="address"
                                    rows="4"
                                ><?= clean($employee["address"]); ?></textarea>




                            </div>





                        </div>

                                                <!-- ==================================================
                                ADDITIONAL PERSONAL INFORMATION
                        =================================================== -->



                        <div class="employee-profile-section-title">


                            <i class="fa-solid fa-user-circle"></i>


                            Additional Information



                        </div>








                        <div class="employee-profile-edit-grid">







                            <div class="employee-profile-edit-group">



                                <label>


                                    Gender



                                </label>






                                <select
                                    name="gender"
                                >



                                    <option value="">


                                        Select Gender


                                    </option>







                                    <option
                                        value="Male"
                                        <?= 
                                            ($employee["gender"] ?? "") === "Male"
                                            ? "selected"
                                            : ""
                                        ?>
                                    >


                                        Male


                                    </option>







                                    <option
                                        value="Female"
                                        <?= 
                                            ($employee["gender"] ?? "") === "Female"
                                            ? "selected"
                                            : ""
                                        ?>
                                    >


                                        Female


                                    </option>







                                    <option
                                        value="Other"
                                        <?= 
                                            ($employee["gender"] ?? "") === "Other"
                                            ? "selected"
                                            : ""
                                        ?>
                                    >


                                        Other


                                    </option>





                                </select>




                            </div>









                            <div class="employee-profile-edit-group">



                                <label>


                                    Civil Status



                                </label>






                                <select
                                    name="civil_status"
                                >



                                    <option value="">


                                        Select Civil Status


                                    </option>







                                    <option
                                        value="Single"
                                        <?= 
                                            ($employee["civil_status"] ?? "") === "Single"
                                            ? "selected"
                                            : ""
                                        ?>
                                    >


                                        Single


                                    </option>







                                    <option
                                        value="Married"
                                        <?= 
                                            ($employee["civil_status"] ?? "") === "Married"
                                            ? "selected"
                                            : ""
                                        ?>
                                    >


                                        Married


                                    </option>







                                    <option
                                        value="Widowed"
                                        <?= 
                                            ($employee["civil_status"] ?? "") === "Widowed"
                                            ? "selected"
                                            : ""
                                        ?>
                                    >


                                        Widowed


                                    </option>







                                    <option
                                        value="Separated"
                                        <?= 
                                            ($employee["civil_status"] ?? "") === "Separated"
                                            ? "selected"
                                            : ""
                                        ?>
                                    >


                                        Separated


                                    </option>





                                </select>




                            </div>





                        </div>












                        <!-- ==================================================
                                EMPLOYMENT INFORMATION
                        =================================================== -->





                        <div class="employee-profile-section-title">


                            <i class="fa-solid fa-id-badge"></i>


                            Employment Information



                        </div>








                        <div class="employee-profile-edit-grid">







                            <div class="employee-profile-edit-group">



                                <label>


                                    Employment Status



                                </label>






                                <input
                                    type="text"
                                    value="<?= clean($employee["employment_status"]); ?>"
                                    readonly
                                >




                            </div>









                            <div class="employee-profile-edit-group">



                                <label>


                                    Basic Salary



                                </label>






                                <input
                                    type="text"
                                    value="₱<?= number_format(
                                        $employee["basic_salary"],
                                        2
                                    ); ?>"
                                    readonly
                                >




                            </div>









                            <div class="employee-profile-edit-group">



                                <label>


                                    Department



                                </label>






                                <input
                                    type="text"
                                    value="<?= clean($employee["department_name"]); ?>"
                                    readonly
                                >




                            </div>









                            <div class="employee-profile-edit-group">



                                <label>


                                    Position



                                </label>






                                <input
                                    type="text"
                                    value="<?= clean($employee["position_name"]); ?>"
                                    readonly
                                >




                            </div>





                        </div>













                        <!-- ==================================================
                                ACTION BUTTONS
                        =================================================== -->





                        <div class="employee-profile-edit-actions">












                                                        <button
                                type="submit"
                                name="update_profile"
                                id="profileEditSaveBtn"
                                disabled
                                class="employee-profile-save-btn"
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
            PROFILE FORM VALIDATION
======================================================= -->


<script>


document
.querySelector(".employee-profile-edit-form")
.addEventListener(
"submit",
function(e){



    const firstName =
    document
    .querySelector('input[name="first_name"]')
    .value
    .trim();




    const lastName =
    document
    .querySelector('input[name="last_name"]')
    .value
    .trim();




    const email =
    document
    .querySelector('input[name="email"]')
    .value
    .trim();






    if(

        firstName === "" ||

        lastName === "" ||

        email === ""

    ){



        e.preventDefault();




        Swal.fire({


            icon:"warning",


            title:"Incomplete Information",


            text:"Please complete all required fields.",


            confirmButtonColor:"#F9A825"



        });



        return false;


    }






    const emailPattern =

    /^[^\s@]+@[^\s@]+\.[^\s@]+$/;






        if(!emailPattern.test(email)){



        e.preventDefault();




        Swal.fire({


            icon:"warning",


            title:"Invalid Email",


            text:"Please enter a valid email address.",


            confirmButtonColor:"#F9A825"



        });



        return false;



    }


    e.preventDefault();

    Swal.fire({

        icon: "question",
        title: "Save profile changes?",
        text: "Your profile information will be updated.",
        showCancelButton: true,
        confirmButtonText: "Yes, Save",
        cancelButtonText: "Cancel",
        confirmButtonColor: "#0D47A1",
        cancelButtonColor: "#90A4AE",
        reverseButtons: true

    }).then(function (result) {

        if (result.isConfirmed) {

            document
                .querySelector(".employee-profile-edit-form")
                .submit();

        }

    });


});



</script>









<!-- ======================================================
            PROFILE CHANGE DETECTION
======================================================= -->


<script>

(function () {

    const form = document.querySelector(".employee-profile-edit-form");
    const saveBtn = document.getElementById("profileEditSaveBtn");
    const initialState = new URLSearchParams(new FormData(form)).toString();

    function checkForChanges() {
        const currentState = new URLSearchParams(new FormData(form)).toString();
        saveBtn.disabled = (currentState === initialState);
    }

    form.addEventListener("input", checkForChanges);
    form.addEventListener("change", checkForChanges);

})();

</script>


<!-- ======================================================
            EMPLOYEE GLOBAL SCRIPT
======================================================= -->


<script src="../assets/js/employee.js"></script>









</body>


</html>