<?php

if(session_status() === PHP_SESSION_NONE){
    session_start();
}


$hr_name = $_SESSION['full_name'] ?? "HR User";

$gender = $_SESSION['gender'] ?? "Female";

$profile_picture = $_SESSION['profile_picture'] ?? null;


if(!empty($profile_picture)){

    $avatar = "../assets/images/profiles/" . $profile_picture;

}
else{

    if($gender === "Male"){

        $avatar = "../assets/images/avatar/hr_boy.jpg";

    }
    else{

        $avatar = "../assets/images/avatar/hr_girl.png";

    }

}


?>


<header class="hr-header">

    <div class="hr-header-left">

        <h2>
            Good Day,
            <?= htmlspecialchars($hr_name); ?>
        </h2>

        <p>
            Welcome back to Daily Cravings Foods Inc.
            HR Management Portal
        </p>

    </div>


    <div class="hr-header-right">

                <div class="hr-profile">


            <a href="profile.php" class="hr-profile-btn" style="text-decoration:none; color:inherit;">

                <img 
                src="<?= htmlspecialchars($avatar); ?>"
                class="hr-profile-image"
                alt="Profile Picture">

                <div class="hr-profile-info">

                    <strong>
                        <?= htmlspecialchars($hr_name); ?>
                    </strong>


                                        <small>
                        HR Administrator
                    </small>

                </div>

            </a>

        </div>

    </div>

</header>