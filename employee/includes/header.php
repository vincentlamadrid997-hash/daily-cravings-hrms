<?php

if (session_status() === PHP_SESSION_NONE) {

    session_start();

}

$employee_name = $_SESSION['full_name'] ?? "Employee";

$gender = $_SESSION['gender'] ?? "Female";

$profile_picture = $_SESSION['profile_picture'] ?? null;

if (!empty($profile_picture)) {

    $avatar = "../assets/images/profiles/" . $profile_picture;

} else {

    if ($gender === "Male") {

        $avatar = "../assets/images/avatar/employee_boy.png";

    } else {

        $avatar = "../assets/images/avatar/employee_girl.png";

    }

}

?>


<header class="emp-header">

    <div class="emp-header-left">

        <h2>

            <?= htmlspecialchars($page_title ?? "Employee Dashboard"); ?>

        </h2>

        <p>

            Welcome back,

            <strong>

                <?= htmlspecialchars($employee_name); ?>

            </strong>

            Manage your employee account here.

        </p>

    </div>

    <div class="emp-header-right">

        <div class="emp-profile">

            <div class="emp-profile-btn">

                <img
                    src="<?= htmlspecialchars($avatar); ?>"
                    alt="Employee Profile"
                    class="emp-profile-image">

                <div class="emp-profile-info">

                    <strong>

                        <?= htmlspecialchars($employee_name); ?>

                    </strong>

                    <small>

                        Employee Account

                    </small>

                </div>

            </div>

        </div>

    </div>

</header>