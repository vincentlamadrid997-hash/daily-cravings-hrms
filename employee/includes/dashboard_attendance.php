<?php

require_once __DIR__ . "/../../config/db.php";


$employee_id = $_SESSION["employee_id"] ?? 0;


$attendance = [

    "status" => "No Record",
    "time_in" => "--:--",
    "time_out" => "--:--",
    "attendance_date" => date("Y-m-d")

];


$stmt = $conn->prepare("

    SELECT

        attendance_date,
        time_in,
        time_out,
        status

    FROM attendance
    WHERE employee_id = ?
    AND attendance_date = CURDATE()
    LIMIT 1

");


$stmt->execute([$employee_id]);


$row = $stmt->fetch(PDO::FETCH_ASSOC);


if($row){


    $attendance["status"] = $row["status"];
    $attendance["attendance_date"] = $row["attendance_date"];

    $attendance["time_in"] = !empty($row["time_in"])
        ? date("h:i A", strtotime($row["time_in"]))
        : "--:--";

    $attendance["time_out"] = !empty($row["time_out"])
        ? date("h:i A", strtotime($row["time_out"]))
        : "--:--";

}


$statusClass = "primary";
$statusIcon = "fa-circle-question";
$statusMessage = "No attendance record today.";


switch($attendance["status"]){

    case "Present":

        $statusClass = "success";
        $statusIcon = "fa-circle-check";
        $statusMessage = "You are currently present.";

    break;


    case "Late":

        $statusClass = "warning";
        $statusIcon = "fa-clock";
        $statusMessage = "You arrived late today.";

    break;


    case "Absent":

        $statusClass = "danger";
        $statusIcon = "fa-circle-xmark";
        $statusMessage = "You are marked absent.";

    break;


}


?>


<div class="employee-widget employee-attendance-modern">

    <div class="employee-widget-header">

        <div class="employee-widget-title">

            <i class="fa-solid fa-calendar-check"></i>

            <h3>Today's Attendance</h3>

        </div>

    </div>

    <div class="employee-widget-body">

        <div class="employee-attendance-modern-container">

            <div class="employee-attendance-status-box <?= $statusClass; ?>">

                <div class="employee-attendance-status-icon">
                    <i class="fa-solid <?= $statusIcon; ?>"></i>
                </div>

                <div class="employee-attendance-status-content">

                    <span>
                        Attendance Status
                    </span>

                    <h2>
                        <?= htmlspecialchars($attendance["status"]); ?>
                    </h2>

                    <p>
                        <?= htmlspecialchars($statusMessage); ?>
                    </p>

                </div>

            </div>

            <div class="employee-attendance-details">

                <div class="employee-attendance-detail-card">

                    <div class="employee-attendance-detail-icon">
                        <i class="fa-solid fa-right-to-bracket"></i>
                    </div>

                    <div>

                        <span>
                            Time In
                        </span>

                        <strong>
                            <?= htmlspecialchars($attendance["time_in"]); ?>
                        </strong>

                    </div>

                </div>

                <div class="employee-attendance-detail-card">

                    <div class="employee-attendance-detail-icon logout">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </div>

                    <div>

                        <span>
                            Time Out
                        </span>

                        <strong>
                            <?= htmlspecialchars($attendance["time_out"]); ?>
                        </strong>

                    </div>

                </div>

                <div class="employee-attendance-detail-card">

                    <div class="employee-attendance-detail-icon date">
                        <i class="fa-solid fa-calendar-days"></i>
                    </div>

                    <div>

                        <span>
                            Date
                        </span>

                        <strong>

                            <?= date(
                                "M d, Y",
                                strtotime($attendance["attendance_date"])
                            ); ?>

                        </strong>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>