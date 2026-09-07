<?php

require_once __DIR__ . "/../../config/db.php";

$employee_id = $_SESSION['employee_id'] ?? 0;

$activities = [];


$stmt = $conn->prepare("
    SELECT
        lr.status,
        lt.leave_type_name,
        lr.created_at
    FROM leave_requests lr

    LEFT JOIN leave_types lt
        ON lr.leave_type_id = lt.leave_type_id

    WHERE lr.employee_id = ?

    ORDER BY lr.created_at DESC

    LIMIT 10
");

$stmt->execute([$employee_id]);

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

    $color = "warning";

    if ($row["status"] === "Approved") {

        $color = "success";

    } elseif ($row["status"] === "Rejected") {

        $color = "danger";

    }

    $activities[] = [

        "icon" => "fa-calendar-check",

        "color" => $color,

        "title" => "Leave Request",

        "message" =>

            ($row["leave_type_name"] ?? "Leave")

            .

            " request is "

            .

            $row["status"],

        "badge" => $row["status"],

        "date" => $row["created_at"]

    ];

}


$stmt = $conn->prepare("
    SELECT

        contract_type,
        status,
        created_at

    FROM contracts

    WHERE employee_id = ?

    ORDER BY created_at DESC

    LIMIT 5
");

$stmt->execute([$employee_id]);

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

    $activities[] = [

        "icon" => "fa-file-contract",

        "color" => "primary",

        "title" => "Employment Contract",

        "message" =>

            ($row["contract_type"] ?? "Contract")

            .

            " contract is "

            .

            $row["status"],

        "badge" => $row["status"],

        "date" => $row["created_at"]

    ];

}


$stmt = $conn->prepare("

    SELECT

        n.message,
        n.status,
        n.created_at

    FROM notifications n

    INNER JOIN users u

        ON n.user_id = u.user_id

    WHERE u.employee_id = ?

    ORDER BY n.created_at DESC

    LIMIT 10

");

$stmt->execute([$employee_id]);

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

    $activities[] = [

        "icon" => "fa-bell",

        "color" => "primary",

        "title" => "Notification",

        "message" => $row["message"],

        "badge" => $row["status"],

        "date" => $row["created_at"]

    ];

}


usort(

    $activities,

    function ($a, $b) {

        return strtotime($b["date"]) - strtotime($a["date"]);

    }

);

$activities = array_slice($activities, 0, 10);

?>


<div class="employee-widget">

    <div class="employee-widget-header">

        <div class="employee-widget-title">

            <i class="fa-solid fa-clock-rotate-left"></i>

            <h3>Recent Activity</h3>

        </div>

    </div>

    <div class="employee-widget-body employee-scroll-widget">

        <?php if (empty($activities)): ?>

            <div class="employee-empty-state">

                <i class="fa-solid fa-inbox"></i>

                <h4>No Recent Activity</h4>

                <p>Your recent activities will appear here.</p>

            </div>

        <?php else: ?>

            <div class="employee-activity-list">

                <?php foreach ($activities as $activity): ?>

                    <div class="employee-activity-item">

                        <div class="employee-activity-icon <?= htmlspecialchars($activity["color"]); ?>">

                            <i class="fa-solid <?= htmlspecialchars($activity["icon"]); ?>"></i>

                        </div>

                        <div class="employee-activity-content">

                            <div class="employee-activity-header">

                                <h4>

                                    <?= htmlspecialchars($activity["title"]); ?>

                                </h4>

                                <span class="employee-activity-badge">

                                    <?= htmlspecialchars($activity["badge"]); ?>

                                </span>

                            </div>

                            <p>

                                <?= htmlspecialchars($activity["message"]); ?>

                            </p>

                            <div class="employee-activity-time">

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