<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$admin_name = $_SESSION['full_name'] ?? "Administrator";

$gender = $_SESSION['gender'] ?? "Female";

$profile_picture = $_SESSION['profile_picture'] ?? "";

if (!empty($profile_picture) && file_exists("../assets/images/profiles/" . $profile_picture)) {

    $avatar = "../assets/images/profiles/" . $profile_picture;

} else {

    if ($gender == "Male") {
        $avatar = "../assets/images/avatar/admin_boy.png";

    } else {
        $avatar = "../assets/images/avatar/admin_girl.png";

    }

}

?>

<header class="admin-header">

    <div class="admin-header-left">

        <h2>
            Welcome,
            <?= htmlspecialchars($admin_name); ?>
        </h2>

        <p>Daily Cravings Foods Inc. Administration Panel</p>

    </div>

    <div class="admin-header-right">

            <div class="admin-profile">

            <a href="profile.php" class="admin-profile-btn" style="text-decoration:none; color:inherit;">

                <img
                    src="<?= htmlspecialchars($avatar); ?>"
                    alt="Profile"
                    class="admin-profile-image">

                <div class="admin-profile-info">

                    <strong>
                        <?= htmlspecialchars($admin_name); ?>
                    </strong>

                    <small>
                        System Administrator
                    </small>

                </div>

            </a>

        </div>

    </div>

</header>