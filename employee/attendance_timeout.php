<?php


/*
==========================================================
            ATTENDANCE TIME OUT

        Daily Cravings Foods Inc.

        Employee Attendance System

        FINAL FIXED VERSION

        Automatic Overtime Engine

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
                VALIDATE TIME OUT
    ======================================================
    */


    $validation = canTimeOut(

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
                CURRENT TIME OUT
    ======================================================
    */


    $timeOut = attendanceNow();









    /*
    ======================================================
                PREPARE CALCULATION DATA
    ======================================================
    */


    $attendance["time_out"] = $timeOut;









    /*
    ======================================================
                WORKING SECONDS
    ======================================================
    */


    $workedSeconds = calculateWorkedSeconds(

        $attendance

    );






    $workingHours = formatSeconds(

        $workedSeconds

    );









    /*
    ======================================================
                REQUIRED HOURS
    ======================================================
    */


    $requiredSeconds = requiredWorkingSeconds(

        $settings

    );









    /*
    ======================================================
                AUTOMATIC OVERTIME
    ======================================================
    */


    $overtimeSeconds = max(

        0,

        $workedSeconds - $requiredSeconds

    );








    /*
    Limit OT based sa attendance settings
    */


    $maximumOTSeconds = hoursToSeconds(

        $settings["maximum_ot"]

    );





    if(

        $overtimeSeconds >

        $maximumOTSeconds

    ){


        $overtimeSeconds = $maximumOTSeconds;


    }









    /*
    ======================================================
                UNDERTIME
    ======================================================
    */


    $undertimeSeconds = max(

        0,

        $requiredSeconds - $workedSeconds

    );









    /*
    ======================================================
                UPDATE ATTENDANCE
    ======================================================
    */


    $stmt = $conn->prepare("


        UPDATE attendance


        SET


            time_out = ?,


            worked_seconds = ?,


            total_working_hours = ?,


            overtime_hours = ?,


            undertime_hours = ?,


            status = ?,


            timeline_completed = 1,


            remarks = ?


        WHERE attendance_id = ?



    ");









    $stmt->execute([


        $timeOut,


        $workedSeconds,


        $workingHours,


        formatSeconds(

            $overtimeSeconds

        ),



        formatSeconds(

            $undertimeSeconds

        ),



        "Present",



        (

            $overtimeSeconds > 0

            ?

            "Completed with overtime"

            :

            "Shift completed"

        ),



        $attendance["attendance_id"]



    ]);









    /*
    ======================================================
                ADD TIMELINE
    ======================================================
    */


    addAttendanceTimeline(

        $conn,

        $attendance["attendance_id"],

        $employee_id,

        "Time Out"

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


        "Time Out"



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


    if($overtimeSeconds > 0){


        attendanceAlert(

            "Time Out completed. Overtime recorded: "
            .
            formatSeconds(
                $overtimeSeconds
            ),

            "success"

        );


    }
    else{


        attendanceAlert(

            "Time Out completed successfully.",

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