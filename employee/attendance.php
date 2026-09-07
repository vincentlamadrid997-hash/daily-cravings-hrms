<?php


/*
==========================================================
            EMPLOYEE ATTENDANCE PAGE

        Daily Cravings Foods Inc.

        Employee Attendance System

        FINAL FIXED VERSION

        Automatic OT System

        No OT Request
        No Half Day Request

==========================================================
*/


session_start();



date_default_timezone_set(  
    "Asia/Manila"
);

if(empty($_SESSION["csrf_token"])){

    $_SESSION["csrf_token"] = bin2hex(
        random_bytes(32)
    );

}

require_once "../auth/employee_auth.php";

require_once "../config/db.php";

require_once "includes/attendance_helper.php";





$page_title = "Attendance";






/*
==========================================================
                EMPLOYEE SESSION
==========================================================
*/


$employee_id = $_SESSION["employee_id"] ?? 0;



if(!$employee_id){


    header(

        "Location: ../login.php"

    );


    exit;


}







/*
==========================================================
                LOAD ATTENDANCE STATUS
==========================================================
*/


$attendanceData = attendanceStatus(

    $conn,

    $employee_id

);





/*
==========================================================
                ATTENDANCE RECORD
==========================================================
*/


$attendance =

$attendanceData["attendance"] ?? null;



if(!$attendance){


    $attendance = [


        "time_in" => null,


        "break_out" => null,


        "break_in" => null,


        "time_out" => null


    ];


}









/*
==========================================================
                SUMMARY DATA
==========================================================
*/


$summary =

$attendanceData["summary"] ?? [];





$workingHours =

$summary["working_hours"]

??

"00:00:00";





$remainingTime =

$summary["remaining_time"]

??

"00:00:00";





$overtime =

$summary["overtime"]

??

"00:00:00";





$undertime =

$summary["undertime"]

??

"00:00:00";





$status =

$summary["status"]

??

"Not Started";








/*
==========================================================
                BUTTON STATUS

        TIME IN
        BREAK OUT
        BREAK IN
        TIME OUT

==========================================================
*/


$buttons =

$attendanceData["buttons"]

??

[];





$canTimeIn =

$buttons["timein"]["allowed"]

??

false;





$canBreakOut =

$buttons["breakout"]["allowed"]

??

false;





$canBreakIn =

$buttons["breakin"]["allowed"]

??

false;





$canTimeOut =

$buttons["timeout"]["allowed"]

??

false;








/*
==========================================================
                BUTTON MESSAGES
==========================================================
*/


$timeInMessage =

$buttons["timein"]["message"]

??

"";



$breakOutMessage =

$buttons["breakout"]["message"]

??

"";



$breakInMessage =

$buttons["breakin"]["message"]

??

"";



$timeOutMessage =

$buttons["timeout"]["message"]

??

"";







/*
==========================================================
                STATUS CARD
==========================================================
*/


$statusTitle =

$attendanceData["status_title"]

??

"Not Started";





$statusMessage =

$attendanceData["status_message"]

??

"";





$statusClass =

$attendanceData["status_class"]

??

"primary";





$monitor =

$attendanceData["monitor"]

??

"Offline";






/*
==========================================================
                TIMELINE
==========================================================
*/


$timeline =

$attendanceData["timeline"]

??

[];







/*
==========================================================
                SESSION ALERT
==========================================================
*/


$message =

$_SESSION["attendance_message"]

??

"";




$message_type =

$_SESSION["attendance_type"]

??

"success";




unset(

    $_SESSION["attendance_message"]

);



unset(

    $_SESSION["attendance_type"]

);








/*
==========================================================
                DATE DISPLAY
==========================================================
*/


$current_date = date(

    "F d, Y"

);



$current_day = date(

    "l"

);







/*
==========================================================
                DISPLAY TIME
==========================================================
*/


$timeIn = displayTime(

    $attendance["time_in"]

);



$breakOut = displayTime(

    $attendance["break_out"]

);



$breakIn = displayTime(

    $attendance["break_in"]

);



$timeOut = displayTime(

    $attendance["time_out"]

);







/*
==========================================================
                LIVE DATA FOR JS
==========================================================
*/


$liveAttendance = attendanceLiveData(

    $attendance,

    $attendanceData["settings"]

);



?>

<!DOCTYPE html>

<html lang="en">


<head>


<meta charset="UTF-8">


<meta name="viewport" content="width=device-width, initial-scale=1.0">



<title>

<?= htmlspecialchars($page_title); ?>

|

Daily Cravings Foods Inc.

</title>





<!-- =====================================================
                    CSS FILES
===================================================== -->


<link rel="stylesheet"

href="../assets/css/global.css">



<link rel="stylesheet"

href="../assets/css/employee.css">



<link rel="stylesheet"

href="../assets/css/attendance.css">







<!-- =====================================================
                    FONT AWESOME
===================================================== -->


<link rel="stylesheet"

href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">







<!-- =====================================================
                    SWEETALERT 2
===================================================== -->


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>





</head>







<body>





<div class="employee-wrapper">







<!-- =====================================================
                    SIDEBAR
===================================================== -->


<?php require_once "includes/sidebar.php"; ?>










<!-- =====================================================
                CONTENT WRAPPER
===================================================== -->


<div class="employee-content-wrapper">







<!-- =====================================================
                    HEADER
===================================================== -->


<?php require_once "includes/header.php"; ?>











<main class="employee-main">






<section class="employee-dashboard">







<!-- =====================================================
                SWEETALERT MESSAGE
===================================================== -->


<?php if(!empty($message)): ?>


<script>


document.addEventListener(

"DOMContentLoaded",

function(){



Swal.fire({


title:

"<?= ucfirst(

htmlspecialchars($message_type)

); ?>",



text:

"<?= htmlspecialchars($message); ?>",




icon:

"<?= htmlspecialchars($message_type); ?>",




confirmButtonText:

"OK",




confirmButtonColor:

"#0D47A1"



});



}


);



</script>



<?php endif; ?>











<!-- =====================================================
                PAGE HEADER
===================================================== -->


<div class="employee-dashboard-header">







<div class="employee-dashboard-title">





<h1>



<i class="fa-solid fa-clock"></i>



Time In / Time Out



</h1>







<p>



Manage your daily attendance record and working hours.



</p>







</div>









<div class="attendance-date-display">






<div>



<i class="fa-solid fa-calendar-days"></i>



<span>



<?= $current_day; ?>



</span>



</div>







<strong>



<?= $current_date; ?>



</strong>






</div>








</div>















<!-- =====================================================
                ATTENDANCE WIDGET
===================================================== -->


<div class="employee-widget employee-attendance-page">







<div class="employee-widget-header">





<div class="employee-widget-title">





<i class="fa-solid fa-calendar-check"></i>





<h3>



Today's Attendance



</h3>







</div>







</div>











<div class="employee-widget-body">







<div class="attendance-dashboard-container">







<!-- =====================================================
                LIVE MONITOR CARD
===================================================== -->


<div class="attendance-live-monitor">






<div class="live-monitor-icon">



<i class="fa-solid fa-business-time"></i>



</div>








<div class="live-monitor-content">






<span>



Current Activity



</span>








<h2>



<?= htmlspecialchars($monitor); ?>



</h2>









<div class="live-timer-grid">







<div>



<label>



Working Hours



</label>







<strong id="liveWorkingHoursMonitor">



<?= htmlspecialchars($workingHours); ?>



</strong>






</div>









<div>



<label>



Remaining



</label>







<strong id="liveRemainingMonitor">



<?= htmlspecialchars($remainingTime); ?>



</strong>






</div>








<div>



<label>



Status



</label>







<strong>



<?= htmlspecialchars($status); ?>



</strong>






</div>







</div>







</div>







</div>






<!-- =====================================================
                STATUS CARD
===================================================== -->


<div class="attendance-status-card <?= htmlspecialchars($statusClass); ?>">






<div class="attendance-status-icon">


<i class="fa-solid fa-circle-info"></i>


</div>







<div class="attendance-status-content">






<span>



Current Status



</span>








<h2>



<?= htmlspecialchars($statusTitle); ?>



</h2>








<p>



<?= htmlspecialchars($statusMessage); ?>



</p>







</div>








</div>














<!-- =====================================================
                TIME DETAILS
===================================================== -->


<div class="attendance-detail-grid">







<!-- ================= TIME IN ================= -->


<div class="attendance-detail-card">





<div class="attendance-detail-icon timein">



<i class="fa-solid fa-right-to-bracket"></i>



</div>







<div>



<span>



Time In



</span>







<strong>



<?= $timeIn; ?>



</strong>






</div>







</div>














<!-- ================= BREAK OUT ================= -->


<div class="attendance-detail-card">






<div class="attendance-detail-icon break">



<i class="fa-solid fa-mug-hot"></i>



</div>







<div>



<span>



Break Out



</span>







<strong>



<?= $breakOut; ?>



</strong>






</div>







</div>














<!-- ================= BREAK IN ================= -->


<div class="attendance-detail-card">






<div class="attendance-detail-icon break">



<i class="fa-solid fa-arrow-rotate-left"></i>



</div>







<div>



<span>



Break In



</span>







<strong>



<?= $breakIn; ?>



</strong>






</div>







</div>














<!-- ================= TIME OUT ================= -->


<div class="attendance-detail-card">






<div class="attendance-detail-icon timeout">



<i class="fa-solid fa-right-from-bracket"></i>



</div>







<div>



<span>



Time Out



</span>







<strong>



<?= $timeOut; ?>



</strong>






</div>







</div>









</div>
















<!-- =====================================================
                SUMMARY CARDS

        Automatic OT Calculation

===================================================== -->


<div class="attendance-summary-grid">







<!-- ================= WORKING HOURS ================= -->


<div class="attendance-summary-card">





<i class="fa-solid fa-hourglass-half"></i>







<div>



<span>



Working Hours



</span>







<strong id="liveWorkingHours">



<?= htmlspecialchars($workingHours); ?>



</strong>






</div>







</div>














<!-- ================= REMAINING ================= -->


<div class="attendance-summary-card">






<i class="fa-solid fa-hourglass-end"></i>







<div>



<span>



Remaining Time



</span>







<strong id="liveRemainingTime">



<?= htmlspecialchars($remainingTime); ?>



</strong>






</div>







</div>














<!-- ================= OVERTIME ================= -->


<div class="attendance-summary-card">






<i class="fa-solid fa-arrow-trend-up"></i>







<div>



<span>



Overtime



</span>







<strong id="liveOvertime">



<?= htmlspecialchars($overtime); ?>



</strong>






</div>







</div>














<!-- ================= UNDERTIME ================= -->


<div class="attendance-summary-card">






<i class="fa-solid fa-clock-rotate-left"></i>







<div>



<span>



Undertime



</span>







<strong id="liveUndertime">



<?= htmlspecialchars($undertime); ?>



</strong>






</div>







</div>









</div>








<!-- =====================================================
                ACTION BUTTONS
===================================================== -->


<div class="attendance-action-container">





<!-- ================= TIME IN ================= -->


<div class="attendance-button-group">


<form

action="attendance_timein.php"

method="POST"

class="attendance-form">

<input 
type="hidden"
name="csrf_token"
value="<?= $_SESSION["csrf_token"]; ?>">

<button


type="submit"


class="employee-dashboard-btn attendance-timein-btn"


data-action="Time In"


<?= !$canTimeIn ? "disabled" : ""; ?>


>


<i class="fa-solid fa-play"></i>


Time In


</button>



</form>





<?php if(!$canTimeIn && !empty($timeInMessage)): ?>


<small class="attendance-button-message">


<?= htmlspecialchars($timeInMessage); ?>


</small>


<?php endif; ?>



</div>









<!-- ================= BREAK OUT ================= -->


<div class="attendance-button-group">


<form

action="attendance_breakout.php"

method="POST"

class="attendance-form">

<input 
type="hidden"
name="csrf_token"
value="<?= $_SESSION["csrf_token"]; ?>">

<button


type="submit"


class="employee-dashboard-btn attendance-break-btn"


data-action="Break Out"



<?= !$canBreakOut ? "disabled" : ""; ?>


>


<i class="fa-solid fa-mug-hot"></i>


Break Out


</button>



</form>







<?php if(!$canBreakOut && !empty($breakOutMessage)): ?>


<small class="attendance-button-message">


<?= htmlspecialchars($breakOutMessage); ?>


</small>


<?php endif; ?>



</div>









<!-- ================= BREAK IN ================= -->


<div class="attendance-button-group">


<form


action="attendance_breakin.php"


method="POST"


class="attendance-form">

<input 
type="hidden"
name="csrf_token"
value="<?= $_SESSION["csrf_token"]; ?>">

<button


type="submit"


class="employee-dashboard-btn attendance-breakin-btn"


data-action="Break In"


<?= !$canBreakIn ? "disabled" : ""; ?>


>


<i class="fa-solid fa-arrow-rotate-left"></i>


Break In


</button>



</form>







<?php if(!$canBreakIn && !empty($breakInMessage)): ?>


<small class="attendance-button-message">


<?= htmlspecialchars($breakInMessage); ?>


</small>


<?php endif; ?>



</div>









<!-- ================= TIME OUT ================= -->


<div class="attendance-button-group">


<form


action="attendance_timeout.php"


method="POST"


class="attendance-form">

<input 
type="hidden"
name="csrf_token"
value="<?= $_SESSION["csrf_token"]; ?>">

<button


type="submit"


class="employee-dashboard-btn attendance-timeout-btn"


data-action="Time Out"


<?= !$canTimeOut ? "disabled" : ""; ?>


>


<i class="fa-solid fa-stop"></i>


Time Out


</button>



</form>







<?php if(!$canTimeOut && !empty($timeOutMessage)): ?>


<small class="attendance-button-message">


<?= htmlspecialchars($timeOutMessage); ?>


</small>


<?php endif; ?>



</div>







</div>














<!-- =====================================================
                ATTENDANCE TIMELINE
===================================================== -->


<div class="attendance-timeline-card">






<h3>



<i class="fa-solid fa-clock-rotate-left"></i>



Attendance Timeline



</h3>








<?php if(empty($timeline)): ?>





<div class="timeline-empty">





<i class="fa-solid fa-calendar-xmark"></i>







<p>



No attendance activity recorded yet.



</p>





</div>







<?php else: ?>





<ul class="attendance-timeline-list">






<?php foreach($timeline as $item): ?>





<li>






<div class="timeline-icon">



<i class="fa-solid fa-circle-check"></i>



</div>







<div class="timeline-content">





<strong>



<?= htmlspecialchars(

$item["activity"]

); ?>



</strong>







<span>



<?= date(

"h:i A",

strtotime(

$item["activity_time"]

)

); ?>



</span>







</div>







</li>







<?php endforeach; ?>







</ul>







<?php endif; ?>






</div>















<!-- =====================================================
                ATTENDANCE GUIDELINES
===================================================== -->


<div class="attendance-info-card">






<div class="attendance-info-icon">



<i class="fa-solid fa-circle-info"></i>



</div>







<div class="attendance-info-content">





<h4>



Attendance Guidelines



</h4>








<p>


• Time In according to your scheduled work hours.<br>

• Break Out and Break In follow company break schedule.<br>

• Time Out finalizes your attendance record.<br>

• Overtime is automatically calculated when you exceed required working hours.<br>

• Attendance records are saved automatically.


</p>







</div>







</div>









</div>

<!-- attendance-dashboard-container END -->









</div>

<!-- employee-widget-body END -->









</div>

<!-- employee-widget END -->








</section>







</main>








</div>

<!-- employee-content-wrapper END -->








</div>

<!-- employee-wrapper END -->









<script>

const attendanceData = <?= json_encode(

$liveAttendance

); ?>;


const attendanceSettings = <?= json_encode(

$attendanceData["settings"]

); ?>;


</script>







<script src="../assets/js/attendance.js"></script>







</body>


</html>