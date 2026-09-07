<?php

session_start();

require_once "../config/db.php";


/* ==========================================================
   GET JOB ID
========================================================== */


if (isset($_POST['job_id'])) {

    $_SESSION['apply_job_id'] = (int) $_POST['job_id'];

}



if (!isset($_SESSION['apply_job_id'])) {

    header("Location: jobs.php");
    exit;

}



$job_id = (int) $_SESSION['apply_job_id'];





/* ==========================================================
   GET JOB DETAILS
========================================================== */


$stmt = $conn->prepare("

    SELECT

        jp.*,

        p.position_name,

        d.department_name


    FROM job_postings jp


    LEFT JOIN positions p

        ON jp.position_id = p.position_id



    LEFT JOIN departments d

        ON jp.department_id = d.department_id



    WHERE jp.job_id = ?

    LIMIT 1

");



$stmt->execute([

    $job_id

]);



$job = $stmt->fetch(PDO::FETCH_ASSOC);





if (!$job) {


    header("Location: jobs.php");

    exit;


}





/* ==========================================================
   VARIABLES
========================================================== */


$error = "";

$success = "";



$first_name = "";

$middle_name = "";

$last_name = "";

$email = "";

$phone = "";

$birthdate = "";

$gender = "";

$civil_status = "";

$address = "";

$expected_salary = "";

$available_date = "";





/* ==========================================================
   SUCCESS MESSAGE SESSION
========================================================== */


if(isset($_SESSION['application_success'])){


    $success = $_SESSION['application_success'];


    unset($_SESSION['application_success']);


}







/* ==========================================================
   RESUME / FILE UPLOAD FUNCTION
========================================================== */


function uploadApplicationFile($file)
{


    if(

        !isset($file)

        ||

        $file['error'] !== UPLOAD_ERR_OK

    ){

        return false;

    }





    $allowed_extensions = [

        "pdf",

        "doc",

        "docx"

    ];







    $extension = strtolower(

        pathinfo(

            $file['name'],

            PATHINFO_EXTENSION

        )

    );








    if(

        !in_array(

            $extension,

            $allowed_extensions

        )

    ){


        return false;


    }







    if(

        $file['size'] > (5 * 1024 * 1024)

    ){


        return false;


    }









    $upload_folder = "../uploads/applications/";







    if(

        !is_dir($upload_folder)

    ){


        mkdir(

            $upload_folder,

            0777,

            true

        );


    }









    $new_filename =

        uniqid(

            "application_",

            true

        )

        .

        "."

        .

        $extension;








    $destination =

        $upload_folder

        .

        $new_filename;









    if(

        move_uploaded_file(

            $file['tmp_name'],

            $destination

        )

    ){


        return $new_filename;


    }






    return false;


}



?>

<?php


/* ==========================================================
   APPLICATION SUBMISSION
========================================================== */


if (

    $_SERVER["REQUEST_METHOD"] === "POST"

    &&

    isset($_POST["submit_application"])

) {



    /* ======================================================
       GET FORM DATA
    ====================================================== */


    $first_name = trim($_POST['first_name'] ?? '');

    $middle_name = trim($_POST['middle_name'] ?? '');

    $last_name = trim($_POST['last_name'] ?? '');

    $email = trim($_POST['email'] ?? '');

    $phone = trim($_POST['phone'] ?? '');

    $birthdate = $_POST['birthdate'] ?? '';

    $gender = $_POST['gender'] ?? '';

    $civil_status = $_POST['civil_status'] ?? '';

    $address = trim($_POST['address'] ?? '');

    $expected_salary = $_POST['expected_salary'] ?? '';

    $available_date = $_POST['available_date'] ?? '';





    /* ======================================================
       REQUIRED FIELD VALIDATION
    ====================================================== */


    if (

        empty($first_name)

        ||

        empty($last_name)

        ||

        empty($email)

        ||

        empty($phone)

        ||

        empty($birthdate)

        ||

        empty($gender)

        ||

        empty($civil_status)

        ||

        empty($address)

        ||

        empty($expected_salary)

        ||

        empty($available_date)

    ) {


        $error = "Please complete all required fields.";


    }







    /* ======================================================
       EMAIL VALIDATION
    ====================================================== */


    elseif(

        !filter_var(

            $email,

            FILTER_VALIDATE_EMAIL

        )

    ){


        $error = "Please enter a valid email address.";


    }







    /* ======================================================
       PHONE VALIDATION
       Format: 09XXXXXXXXX
    ====================================================== */


    elseif(

        !preg_match(

            "/^09[0-9]{9}$/",

            $phone

        )

    ){


        $error =

        "Phone number must be 11 digits and start with 09.";


    }







    /* ======================================================
       AGE VALIDATION
       Minimum 18 Years Old
    ====================================================== */


    elseif(!empty($birthdate)){



        try {


            $birth = new DateTime($birthdate);


            $today = new DateTime();



            $age = $today->diff($birth)->y;





            if($age < 18){


                $error =

                "Applicant must be at least 18 years old.";


            }



        }


        catch(Exception $e){


            $error = "Invalid birthdate.";


        }



    }








    /* ======================================================
       AVAILABLE DATE VALIDATION
    ====================================================== */


    elseif(

        $available_date < date("Y-m-d")

    ){


        $error =

        "Available start date cannot be earlier than today.";


    }








    /* ======================================================
       SALARY VALIDATION
    ====================================================== */


    elseif(

        !is_numeric($expected_salary)

        ||

        $expected_salary <= 0

    ){


        $error =

        "Expected salary must be a valid amount.";


    }








    /* ======================================================
       DUPLICATE APPLICATION CHECK
    ====================================================== */


    if(empty($error)){



        $duplicate = $conn->prepare("


            SELECT 

                application_id


            FROM applications


            WHERE email = ?

            AND job_id = ?


            LIMIT 1


        ");





        $duplicate->execute([


            $email,


            $job_id


        ]);






        if($duplicate->fetch()){



            $error =

            "You already submitted an application for this position.";



        }



    }








    /* ======================================================
       RESUME REQUIRED CHECK
    ====================================================== */


    if(empty($error)){



        if(


            !isset($_FILES['resume'])


            ||


            $_FILES['resume']['error']

            !==

            UPLOAD_ERR_OK


        ){



            $error =

            "Resume is required.";



        }



    }




}

?>

<?php


/* ==========================================================
   PROCESS APPLICATION INSERT
========================================================== */


if (

    $_SERVER["REQUEST_METHOD"] === "POST"

    &&

    isset($_POST["submit_application"])

    &&

    empty($error)

) {



    /* ======================================================
       UPLOAD RESUME
    ====================================================== */


    $resume = uploadApplicationFile($_FILES['resume']);



    if(!$resume){


        $error =

        "Invalid resume file. Please upload PDF, DOC, or DOCX only (Maximum 5MB).";


    }







    /* ======================================================
       OPTIONAL COVER LETTER
    ====================================================== */


    $cover_letter = null;



    if(


        empty($error)

        &&

        isset($_FILES['cover_letter'])

        &&

        $_FILES['cover_letter']['error']

        ===

        UPLOAD_ERR_OK


    ){



        $cover_letter =

        uploadApplicationFile(

            $_FILES['cover_letter']

        );



    }








    /* ======================================================
       INSERT TO DATABASE
    ====================================================== */


    if(empty($error)){



        try {



            $insert = $conn->prepare("


                INSERT INTO applications


                (

                    job_id,

                    first_name,

                    middle_name,

                    last_name,

                    email,

                    phone,

                    birthdate,

                    gender,

                    civil_status,

                    address,

                    expected_salary,

                    available_date,

                    resume,

                    cover_letter,

                    status,

                    created_at


                )


                VALUES


                (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())


            ");








            $insert->execute([



                $job_id,



                $first_name,



                $middle_name,



                $last_name,



                $email,



                $phone,



                $birthdate,



                $gender,



                $civil_status,



                $address,



                $expected_salary,



                $available_date,



                $resume,



                $cover_letter,



                "Pending"



            ]);










            $_SESSION['application_success'] =


            "Your application has been submitted successfully. HR will review your application soon.";








            header("Location: apply.php");


            exit;






        }


        catch(PDOException $e){



            $error =

            "Application submission failed. Please try again.";





            // FOR DEBUG ONLY:
            // $error = $e->getMessage();



        }




    }



}



?>

<?php
/* ==========================================================
   END PHP PROCESSING
========================================================== */
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">


<title>
Apply Now | Daily Cravings Foods Inc.
</title>


<link rel="stylesheet" href="../assets/css/global.css">

<link rel="stylesheet" href="../assets/css/apply.css?v=2">


<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">


</head>



<body class="apply-page">



<!-- ==========================================================
     BACKGROUND
========================================================== -->


<div class="apply-background">


<img

src="../assets/images/logo_2.png"

class="apply-bg-logo"

alt="Company Logo"


>


</div>





<!-- ==========================================================
     NAVBAR
========================================================== -->


<header class="apply-navbar">


<div class="apply-nav-container">



<div class="apply-left">



<a

href="../home.php"

class="apply-home-btn"

>


<i class="fa-solid fa-house"></i>


</a>






<div class="apply-logo-area">



<img

src="../assets/images/logo_2.png"

alt="Company Logo"

>




<div>


<h2>

Daily Cravings Foods Inc.

</h2>


<span>

Human Resource Management System

</span>


</div>


</div>




</div>







<a

href="../login.php"

class="apply-login-btn"

>


<i class="fa-solid fa-right-to-bracket"></i>


Employee Login


</a>






</div>


</header>









<!-- ==========================================================
     MAIN APPLICATION SECTION
========================================================== -->


<section class="apply-section">


<div class="apply-container">


<div class="apply-card">





<h1>

Job Application Form

</h1>




<p class="apply-subtitle">

Complete your information below to apply for this position.

</p>








<!-- ==========================================================
     ERROR MESSAGE
========================================================== -->


<?php if(!empty($error)): ?>


<div class="apply-error">


<i class="fa-solid fa-circle-exclamation"></i>



<span>


<?= htmlspecialchars($error); ?>


</span>



</div>



<?php endif; ?>









<!-- ==========================================================
     SUCCESS MESSAGE
========================================================== -->


<?php if(!empty($success)): ?>


<div class="apply-success">


<i class="fa-solid fa-circle-check"></i>



<span>


<?= htmlspecialchars($success); ?>


</span>



</div>



<?php endif; ?>









<!-- ==========================================================
     JOB SUMMARY
========================================================== -->


<div class="job-summary">





<div class="job-summary-header">





<div class="job-summary-icon">


<i class="fa-solid fa-briefcase"></i>


</div>







<div>


<h2>


<?= htmlspecialchars(

$job['job_title']

); ?>


</h2>





<span>


<?= htmlspecialchars(

$job['position_name'] ?? 'Position'

); ?>


</span>



</div>






</div>










<div class="job-summary-grid">





<div>


<i class="fa-solid fa-building"></i>


<strong>

Department

</strong>



<p>

<?= htmlspecialchars(

$job['department_name']

??

'Not Specified'

); ?>


</p>



</div>







<div>


<i class="fa-solid fa-money-bill-wave"></i>


<strong>

Salary

</strong>



<p>


<?= htmlspecialchars(

$job['salary']

??

'Negotiable'

); ?>


</p>



</div>








<div>


<i class="fa-solid fa-clock"></i>


<strong>

Employment Type

</strong>



<p>


<?= htmlspecialchars(

$job['employment_type']

??

'Not Specified'

); ?>


</p>



</div>








<div>


<i class="fa-solid fa-location-dot"></i>


<strong>

Location

</strong>



<p>


<?= htmlspecialchars(

$job['location']

??

'Not Specified'

); ?>


</p>



</div>






</div>





</div>









<!-- ==========================================================
     SUCCESS MODAL
========================================================== -->


<?php if(!empty($success)): ?>


<div

class="success-modal"

id="successModal"

>



<div class="success-box">





<div class="success-icon">


<i class="fa-solid fa-circle-check"></i>


</div>






<h2>

Application Submitted Successfully!

</h2>






<p>


Thank you for applying to


<strong>

Daily Cravings Foods Inc.

</strong>


<br><br>



Your application for


<strong>


<?= htmlspecialchars(

$job['job_title']

); ?>


</strong>



has been received.



<br><br>



HR Department will review your application.



</p>






<span class="pending-status">


Pending Review


</span>







<a

href="jobs.php"

class="success-btn"

>


<i class="fa-solid fa-briefcase"></i>


Back to Careers


</a>





</div>


</div>





<script>


window.onload=function(){


let modal=document.getElementById("successModal");


if(modal){


modal.style.display="flex";


}



};


</script>



<?php endif; ?>










<!-- ==========================================================
     ERROR MODAL
========================================================== -->


<?php if(!empty($error)): ?>



<div

class="error-modal"

id="errorModal"

>


<div class="error-box">





<div class="error-icon">


<i class="fa-solid fa-circle-xmark"></i>


</div>






<h2>

Application Error

</h2>






<p>


<?= htmlspecialchars($error); ?>


</p>





<button

onclick="closeErrorModal()"

>


Okay


</button>






</div>


</div>




<?php endif; ?>

<!-- ==========================================================
     APPLICATION FORM
========================================================== -->


<form
    method="POST"
    enctype="multipart/form-data"
    class="application-form"
>


<input
    type="hidden"
    name="job_id"
    value="<?= $job_id; ?>"
>





<div class="form-grid">



    <!-- FIRST NAME -->

    <div class="form-group">

        <label>
            First Name *
        </label>


        <input

            type="text"

            name="first_name"

            value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>"

            required

        >

    </div>





    <!-- MIDDLE NAME -->

    <div class="form-group">


        <label>
            Middle Name
        </label>


        <input

            type="text"

            name="middle_name"

            value="<?= htmlspecialchars($_POST['middle_name'] ?? '') ?>"

        >


    </div>







    <!-- LAST NAME -->


    <div class="form-group">


        <label>
            Last Name *
        </label>


        <input

            type="text"

            name="last_name"

            value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>"

            required

        >


    </div>








    <!-- EMAIL -->


    <div class="form-group">


        <label>
            Email Address *
        </label>


        <input

            type="email"

            name="email"

            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"

            required

        >


    </div>








    <!-- PHONE -->


    <div class="form-group">


        <label>
            Phone Number *
        </label>


        <input

            type="text"

            name="phone"

            placeholder="09XXXXXXXXX"

            maxlength="11"

            value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"

            required

        >


    </div>








    <!-- BIRTHDATE -->


    <div class="form-group">


        <label>
            Birthdate *
        </label>


        <input

            type="date"

            name="birthdate"

            value="<?= htmlspecialchars($_POST['birthdate'] ?? '') ?>"

            required

        >


    </div>







    <!-- GENDER -->


    <div class="form-group">


        <label>
            Gender *
        </label>


        <select

            name="gender"

            required

        >


            <option value="">
                Select Gender
            </option>


            <option

                value="Male"

                <?=

                (($_POST['gender'] ?? '') == "Male")

                ?

                "selected"

                :

                ""

                ?>

            >

                Male

            </option>



            <option

                value="Female"

                <?=

                (($_POST['gender'] ?? '') == "Female")

                ?

                "selected"

                :

                ""

                ?>

            >

                Female

            </option>


        </select>


    </div>







    <!-- CIVIL STATUS -->


    <div class="form-group">


        <label>
            Civil Status *
        </label>


        <select

            name="civil_status"

            required

        >


            <option value="">
                Select Status
            </option>



            <option

                value="Single"

                <?=

                (($_POST['civil_status'] ?? '') == "Single")

                ?

                "selected"

                :

                ""

                ?>

            >

                Single

            </option>




            <option

                value="Married"

                <?=

                (($_POST['civil_status'] ?? '') == "Married")

                ?

                "selected"

                :

                ""

                ?>

            >

                Married

            </option>




            <option

                value="Widowed"

                <?=

                (($_POST['civil_status'] ?? '') == "Widowed")

                ?

                "selected"

                :

                ""

                ?>

            >

                Widowed

            </option>


        </select>


    </div>



</div>







<!-- ADDRESS -->


<div class="form-group">


<label>

Complete Address *

</label>



<textarea

name="address"

required

><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>



</div>







<!-- SALARY -->


<div class="form-group">


<label>

Expected Salary *

</label>



<input

type="number"

name="expected_salary"

min="1"

placeholder="Example: 18000"

value="<?= htmlspecialchars($_POST['expected_salary'] ?? '') ?>"

required

>



</div>







<!-- AVAILABLE DATE -->


<div class="form-group">


<label>

Available Start Date *

</label>



<input

type="date"

name="available_date"

min="<?= date('Y-m-d'); ?>"

value="<?= htmlspecialchars($_POST['available_date'] ?? '') ?>"

required

>



</div>







<!-- RESUME -->


<div class="form-group">


<label>

Upload Resume *

<br>

<small>
PDF, DOC, DOCX only (Maximum 5MB)
</small>


</label>



<input

type="file"

name="resume"

accept=".pdf,.doc,.docx"

required

>



</div>







<!-- COVER LETTER -->


<div class="form-group">


<label>

Cover Letter

<br>

<small>
Optional
</small>

</label>



<input

type="file"

name="cover_letter"

accept=".pdf,.doc,.docx"

>



</div>







<!-- SUBMIT -->


<button

type="submit"

name="submit_application"

class="apply-submit-btn"

>


<i class="fa-solid fa-paper-plane"></i>


Submit Application


</button>




</form>




</div>

</div>

</section>







<!-- ==========================================================
     FOOTER
========================================================== -->


<footer class="apply-footer">


<div class="apply-footer-container">



<img

src="../assets/images/logo_2.png"

class="apply-footer-logo"

alt="Company Logo"

>



<h3>

Daily Cravings Foods Inc.

</h3>



<p>

Human Resource Management System

</p>







<div class="apply-footer-info">



<div>

<i class="fa-solid fa-location-dot"></i>

Dasmariñas City, Cavite

</div>




<div>

<i class="fa-solid fa-envelope"></i>

dailycravings.hrms@gmail.com

</div>




<div>

<i class="fa-solid fa-phone"></i>

+63 46 123 4567

</div>



</div>






<hr>





<p class="footer-copy">


© <?= date("Y"); ?>

Daily Cravings Foods Inc.

All Rights Reserved.


</p>





</div>


</footer>







<!-- ==========================================================
     JAVASCRIPT
========================================================== -->


<script src="../assets/js/apply.js"></script>








</body>


</html>
