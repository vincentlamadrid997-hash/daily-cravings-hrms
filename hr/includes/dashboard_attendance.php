<?php

require_once __DIR__ . "/../../config/db.php";


$attendance = [
    "Present" => 0,
    "Late"    => 0,
    "Absent"  => 0,
    "Leave"   => 0
];

$stmt = $conn->prepare("
    SELECT
        status,
        COUNT(*) AS total
    FROM attendance
    WHERE attendance_date = CURDATE()
    GROUP BY status
");

$stmt->execute();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if (isset($attendance[$row["status"]])) {
        $attendance[$row["status"]] = (int) $row["total"];
    }
}



$totalAttendance = array_sum($attendance);

$presentPercentage = $totalAttendance > 0
    ? round(($attendance["Present"] / $totalAttendance) * 100)
    : 0;

?>

<div class="hr-widget">

    <div class="hr-widget-header">

        <div class="hr-widget-title">

            <i class="fa-solid fa-clock"></i>

            <h3>Today's Attendance</h3>

        </div>

    </div>

    <div class="hr-widget-body">

        <div class="hr-attendance-summary">

            <div class="hr-attendance-card present">

                <div class="hr-attendance-top">

                    <div class="hr-attendance-icon present">

                        <i class="fa-solid fa-circle-check"></i>

                    </div>

                    <div>

                        <h4>Present</h4>

                        <h2><?= number_format($attendance["Present"]); ?></h2>

                    </div>
                </div>

            </div>

            <div class="hr-attendance-card late">

                <div class="hr-attendance-top">

                    <div class="hr-attendance-icon late">

                        <i class="fa-solid fa-hourglass-half"></i>

                    </div>

                    <div>

                        <h4>Late</h4>

                        <h2><?= number_format($attendance["Late"]); ?></h2>

                    </div>

                </div>

            </div>

            <div class="hr-attendance-card absent">

                <div class="hr-attendance-top">

                    <div class="hr-attendance-icon absent">

                        <i class="fa-solid fa-circle-xmark"></i>

                    </div>

                    <div>

                        <h4>Absent</h4>

                        <h2><?= number_format($attendance["Absent"]); ?></h2>

                    </div>

                </div>

            </div>

            <div class="hr-attendance-card leave">

                <div class="hr-attendance-top">

                    <div class="hr-attendance-icon leave">

                        <i class="fa-solid fa-umbrella-beach"></i>

                    </div>

                    <div>

                        <h4>Leave</h4>

                        <h2><?= number_format($attendance["Leave"]); ?></h2>

                    </div>

                </div>
                
            </div>

        </div>

    </div>

</div>