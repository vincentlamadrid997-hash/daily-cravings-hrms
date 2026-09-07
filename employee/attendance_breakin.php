<?php


/*
==========================================================
            ATTENDANCE BREAK IN

        Daily Cravings Foods Inc.

        Employee Attendance System

        FIXED VERSION

==========================================================
*/


session_start();


date_default_timezone_set(
    "Asia/Manila"
);



require_once "../auth/employee_auth.php";

require_once "../config/db.php";

require_once "includes/attendance_helper.php";







/*
==========================================================
                CSRF VALIDATION
==========================================================
*/


if(!verifyCSRFToken()){


    attendanceAlert(

        "Invalid security request.",

        "error"

    );


    header(

        "Location: attendance.php"

    );


    exit;


}








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









try{


    /*
    ======================================================
                START TRANSACTION
    ======================================================
    */


    $conn->beginTransaction();









    /*
    ======================================================
                LOAD SETTINGS
    ======================================================
    */


    $settings = loadAttendanceSettings(

        $conn

    );









    /*
    ======================================================
                GET TODAY ATTENDANCE
    ======================================================
    */


    $attendance = getTodayAttendance(

        $conn,

        $employee_id

    );









    /*
    ======================================================
                VALIDATE BREAK IN
    ======================================================
    */


    $validation = canBreakIn(

        $attendance,

        $settings

    );









    if(!$validation["allowed"]){


        throw new Exception(

            $validation["message"]

        );


    }









    /*
    ======================================================
                BREAK IN TIME
    ======================================================
    */


    $breakIn = attendanceNow();









    /*
    ======================================================
                COMPUTE BREAK DURATION
    ======================================================
    */


    $breakOutSeconds = timeToSeconds(

        $attendance["break_out"]

    );




    $breakInSeconds = timeToSeconds(

        $breakIn

    );





    $breakDuration = max(

        0,

        $breakInSeconds - $breakOutSeconds

    );





    $breakMinutes = floor(

        $breakDuration / 60

    );









    /*
    ======================================================
                CHECK EXCESS BREAK
    ======================================================
    */


    $lunchDeducted = 0;



    if(

        $breakMinutes >

        $settings["break_minutes"]

    ){


        $lunchDeducted = 1;


    }









    /*
    ======================================================
                UPDATE ATTENDANCE
    ======================================================
    */


    $stmt = $conn->prepare("


        UPDATE attendance


        SET


            break_in = ?,


            lunch_deducted = ?


        WHERE attendance_id = ?



    ");







    $stmt->execute([


        $breakIn,


        $lunchDeducted,


        $attendance["attendance_id"]



    ]);









    /*
    ======================================================
                TIMELINE
    ======================================================
    */


    addAttendanceTimeline(

        $conn,

        $attendance["attendance_id"],

        $employee_id,

        "Break In"

    );









    /*
    ======================================================
                ATTENDANCE LOG
    ======================================================
    */


    $log = $conn->prepare("


        INSERT INTO attendance_logs


        (

            employee_id,

            attendance_id,

            action

        )


        VALUES


        (?,?,?)



    ");







    $log->execute([


        $employee_id,


        $attendance["attendance_id"],


        "Break In"



    ]);









    /*
    ======================================================
                COMMIT
    ======================================================
    */


    $conn->commit();









    /*
    ======================================================
                MESSAGE
    ======================================================
    */


    if($lunchDeducted){


        attendanceAlert(

            "Break In recorded. Break exceeded allowed break time.",

            "warning"

        );


    }
    else{


        attendanceAlert(

            "Break In recorded successfully.",

            "success"

        );


    }






}
catch(Exception $e){



    if($conn->inTransaction()){


        $conn->rollBack();


    }





    attendanceAlert(

        $e->getMessage(),

        "error"

    );


}








header(

    "Location: attendance.php"

);


exit;


?>