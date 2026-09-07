<?php


/*
==========================================================
            ATTENDANCE BREAK OUT

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
                VALIDATE BREAK OUT
    ======================================================
    */


    $validation = canBreakOut(

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
                BREAK OUT TIME
    ======================================================
    */


    $breakOut = attendanceNow();









    /*
    ======================================================
                UPDATE ATTENDANCE
    ======================================================
    */


    $stmt = $conn->prepare("


        UPDATE attendance


        SET


            break_out = ?


        WHERE attendance_id = ?



    ");







    $stmt->execute([


        $breakOut,


        $attendance["attendance_id"]



    ]);









    /*
    ======================================================
                TIMELINE INSERT
    ======================================================
    */


    addAttendanceTimeline(

        $conn,

        $attendance["attendance_id"],

        $employee_id,

        "Break Out"

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


        "Break Out"



    ]);









    /*
    ======================================================
                COMMIT
    ======================================================
    */


    $conn->commit();









    /*
    ======================================================
                SUCCESS MESSAGE
    ======================================================
    */


    attendanceAlert(

        "Break Out recorded successfully.",

        "success"

    );






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