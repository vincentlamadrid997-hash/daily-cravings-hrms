<?php


/*
==========================================================
            ATTENDANCE TIME IN
        Daily Cravings Foods Inc.

        Employee Attendance System

        FIXED VERSION

        Automatic Attendance System

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
                CSRF CHECK
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
                CHECK EXISTING ATTENDANCE
    ======================================================
    */


    $attendance = getTodayAttendance(

        $conn,

        $employee_id

    );









    /*
    ======================================================
                VALIDATE
    ======================================================
    */


    $validation = canTimeIn(

        $conn,

        $employee_id,

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
                TIME IN DATA
    ======================================================
    */


    $timeIn = attendanceNow();




    $lateMinutes = calculateLateMinutes(

        $timeIn,

        $settings

    );






    $status = (

        $lateMinutes > 0

        ?

        "Late"

        :

        "Present"

    );









    /*
    ======================================================
                INSERT ATTENDANCE
    ======================================================
    */


    $stmt = $conn->prepare("

        INSERT INTO attendance

        (

            employee_id,

            attendance_date,

            work_type,

            time_in,

            status,

            late_minutes,

            break_minutes

        )

        VALUES

        (?,?,?,?,?,?,?)

    ");






    $stmt->execute([


        $employee_id,


        attendanceToday(),


        "Regular",


        $timeIn,


        $status,


        $lateMinutes,


        $settings["break_minutes"]



    ]);









    $attendance_id = $conn->lastInsertId();









    /*
    ======================================================
                TIMELINE
    ======================================================
    */


    addAttendanceTimeline(

        $conn,

        $attendance_id,

        $employee_id,

        "Time In"

    );









    /*
    ======================================================
                LOGS
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


        $attendance_id,


        "Time In"



    ]);









    $conn->commit();









    if($lateMinutes > 0){


        attendanceAlert(

            "Time In successful. You are {$lateMinutes} minute(s) late.",

            "warning"

        );


    }
    else{


        attendanceAlert(

            "Time In successful.",

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