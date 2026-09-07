<?php

require_once __DIR__ . "/../../config/db.php";


$activities = [];


$stmt = $conn->query("
    SELECT
        first_name,
        last_name,
        created_at
    FROM applications
    ORDER BY created_at DESC
    LIMIT 10
");

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

    $activities[] = [

        "icon" => "fa-user-plus",

        "color" => "primary",

        "title" => "New Applicant",

        "message" => trim(
            $row["first_name"] .
            " " .
            $row["last_name"]
        ) . " submitted an application.",

        "badge" => "Application",

        "date" => $row["created_at"]

    ];

}


$stmt = $conn->query("
    SELECT

        a.first_name,
        a.last_name,
        i.stage,
        i.created_at

    FROM interviews i

    LEFT JOIN applications a

        ON a.application_id = i.application_id

    ORDER BY i.created_at DESC

    LIMIT 10
");

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

    $activities[] = [

        "icon" => "fa-calendar-check",

        "color" => "success",

        "title" => "Interview Scheduled",

        "message" =>

            trim(

                ($row["first_name"] ?? "Applicant")

                . " " .

                ($row["last_name"] ?? "")

            )

            . " • "

            . ($row["stage"] ?? "Interview")

            . " Interview",

        "badge" => $row["stage"] ?? "Interview",

        "date" => $row["created_at"]

    ];

}


$stmt = $conn->query("
    SELECT

        message,
        created_at

    FROM notifications

    ORDER BY created_at DESC

    LIMIT 10
");

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

    $activities[] = [

        "icon" => "fa-bell",

        "color" => "warning",

        "title" => "Notification",

        "message" => $row["message"],

        "badge" => "System",

        "date" => $row["created_at"]

    ];

}


usort(

    $activities,

    function ($a, $b) {

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


<div class="hr-widget">

    <div class="hr-widget-header">

        <div class="hr-widget-title">

            <i class="fa-solid fa-clock-rotate-left"></i>

            <h3>Recent Activity</h3>

        </div>

    </div>

    <div class="hr-widget-body hr-scroll-widget">

        <?php if (empty($activities)): ?>

            <div class="hr-empty-state">

                <i class="fa-solid fa-inbox"></i>

                <h4>No Recent Activity</h4>

                <p>Activities will appear here once HR actions are recorded.</p>

            </div>

        <?php else: ?>

            <div class="hr-activity-list">

                <?php foreach ($activities as $activity): ?>

                    <div class="hr-activity-item">

                        <div class="hr-activity-icon <?= htmlspecialchars($activity['color']); ?>">

                            <i class="fa-solid <?= htmlspecialchars($activity['icon']); ?>"></i>

                        </div>

                        <div class="hr-activity-content">

                            <div class="hr-activity-header">

                                <h4>
                                    <?= htmlspecialchars($activity['title']); ?>
                                </h4>

                                <span class="hr-activity-badge">
                                    <?= htmlspecialchars($activity['badge']); ?>
                                </span>

                            </div>

                            <p>
                                <?= htmlspecialchars($activity['message']); ?>
                            </p>

                            <div class="hr-activity-time">

                                <i class="fa-regular fa-clock"></i>

                                <?= date(
                                    "M d, Y • h:i A",
                                    strtotime($activity['date'])
                                ); ?>

                            </div>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>

</div>

