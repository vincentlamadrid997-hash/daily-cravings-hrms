<?php

require_once __DIR__ . "/../../config/db.php";


$activities = [];


$stmt = $conn->query("
    SELECT
        full_name,
        role,
        created_at

    FROM users
    ORDER BY created_at DESC
    LIMIT 10
");


while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

    $activities[] = [

        "icon" => "fa-user-plus",

        "color" => "success",

        "title" => "New User Created",

        "message" =>
            htmlspecialchars($row["full_name"])
            . " account was created ("
            . htmlspecialchars($row["role"])
            . ").",

        "badge" => "User",

        "date" => $row["created_at"]

    ];

}


$stmt = $conn->query("
    SELECT
        email,
        attempt_count,
        last_attempt,
        locked_until

    FROM login_attempts
    ORDER BY last_attempt DESC
    LIMIT 10
");


while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

    $activities[] = [

        "icon" => "fa-right-to-bracket",

        "color" => "primary",

        "title" => "Login Attempt",

        "message" =>
            htmlspecialchars($row["email"])
            . " attempted login ("
            . $row["attempt_count"]
            . " attempt(s)).",

        "badge" =>
            empty($row["locked_until"])
            ? "Login"
            : "Locked",

        "date" => $row["last_attempt"]

    ];

}


$stmt = $conn->query("
    SELECT
        activity,
        date_created

    FROM audit_logs
    ORDER BY date_created DESC
    LIMIT 20
");


while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

    $activityText = strtolower($row["activity"]);

    $icon = "fa-clock-rotate-left";

    $color = "warning";

    $title = "System Activity";


    if(
        str_contains($activityText,"employee")
    ){

        $icon = "fa-user-group";
        $color = "primary";
        $title = "Employee Management";


    }
    elseif(
        str_contains($activityText,"payroll")
        ||
        str_contains($activityText,"allowance")
        ||
        str_contains($activityText,"deduction")
    ){

        $icon = "fa-money-bill-wave";
        $color = "warning";
        $title = "Payroll Activity";


    }
    elseif(
        str_contains($activityText,"attendance")
    ){

        $icon = "fa-calendar-check";
        $color = "success";
        $title = "Attendance Activity";


    }
    elseif(
        str_contains($activityText,"leave")
    ){

        $icon = "fa-calendar-days";
        $color = "primary";
        $title = "Leave Management";


    }
    elseif(
        str_contains($activityText,"job")
        ||
        str_contains($activityText,"applicant")
    ){

        $icon = "fa-briefcase";
        $color = "danger";
        $title = "Recruitment Activity";

    }




    $activities[] = [

        "icon" => $icon,
        "color" => $color,
        "title" => $title,
        "message" =>
            htmlspecialchars($row["activity"]),
        "badge" => "Audit",
        "date" => $row["date_created"]

    ];

}


usort(

    $activities,

    function($a,$b){

        return strtotime($b["date"])
            -
            strtotime($a["date"]);

    }

);


$activities = array_slice(

    $activities,

    0,

    10

);


?>


<div class="admin-widget">

    <div class="admin-widget-header">

        <div class="admin-widget-title">

            <i class="fa-solid fa-clock-rotate-left"></i>

            <h3>Recent Activity</h3>

        </div>

    </div>

    <div class="admin-widget-body admin-scroll-widget">

        <?php if(empty($activities)): ?>

            <div class="admin-empty-state">

                <i class="fa-solid fa-inbox"></i>

                <h4>No Recent Activity</h4>

                <p>System activities will appear here.</p>

            </div>

        <?php else: ?>

            <div class="admin-activity-list">

                <?php foreach($activities as $activity): ?>

                    <div class="admin-activity-item">

                        <div class="admin-activity-icon <?= $activity["color"]; ?>">

                            <i class="fa-solid <?= $activity["icon"]; ?>"></i>

                        </div>

                        <div class="admin-activity-content">

                            <div class="admin-activity-header">

                                <h4>
                                    <?= $activity["title"]; ?>
                                </h4>

                                <span class="admin-activity-badge">

                                    <?= $activity["badge"]; ?>

                                </span>

                            </div>

                            <p>
                                <?= $activity["message"]; ?>
                            </p>

                            <div class="admin-activity-time">

                                <i class="fa-regular fa-clock"></i>

                                <?= date(
                                    "M d, Y • h:i A",
                                    strtotime($activity["date"])
                                ); ?>

                            </div>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>

</div>