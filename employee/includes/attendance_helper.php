<?php

/*
==========================================================
            ATTENDANCE HELPER
        Daily Cravings Foods Inc.

        Employee Attendance System

        CLEAN VERSION

        FEATURES:
        - Time In / Time Out
        - Break Out / Break In
        - Late Calculation
        - Automatic Overtime
        - No OT Request
        - No Half Day Request

==========================================================
*/


/*
==========================================================
                DATE FUNCTIONS
==========================================================
*/


if(!function_exists("attendanceToday")){


    function attendanceToday(): string
    {

        return date("Y-m-d");

    }


}




if(!function_exists("attendanceNow")){


    function attendanceNow(): string
    {

        return date("H:i:s");

    }


}





if(!function_exists("attendanceDateTime")){


    function attendanceDateTime(): string
    {

        return date(
            "Y-m-d H:i:s"
        );

    }


}





if(!function_exists("attendanceDay")){


    function attendanceDay(
        ?string $date = null
    ): string
    {


        if(empty($date)){

            $date = attendanceToday();

        }



        return date(
            "l",
            strtotime($date)
        );


    }


}







/*
==========================================================
                TIME CONVERSION
==========================================================
*/


if(!function_exists("timeToSeconds")){


    function timeToSeconds(
        ?string $time
    ): int
    {


        if(empty($time)){

            return 0;

        }



        $parts = explode(
            ":",
            $time
        );



        $hour = (int)($parts[0] ?? 0);

        $minute = (int)($parts[1] ?? 0);

        $second = (int)($parts[2] ?? 0);




        return

            ($hour * 3600)

            +

            ($minute * 60)

            +

            $second;


    }


}







if(!function_exists("formatSeconds")){


    function formatSeconds(
        int $seconds
    ): string
    {


        if($seconds < 0){

            $seconds = 0;

        }



        $hours = floor(
            $seconds / 3600
        );


        $minutes = floor(
            ($seconds % 3600) / 60
        );


        $seconds = $seconds % 60;




        return sprintf(
            "%02d:%02d:%02d",
            $hours,
            $minutes,
            $seconds
        );


    }


}







if(!function_exists("hoursToSeconds")){


    function hoursToSeconds(
        $hours
    ): int
    {


        return (int)(
            $hours * 3600
        );


    }


}






if(!function_exists("secondsToHours")){


    function secondsToHours(
        int $seconds
    ): float
    {


        return round(
            $seconds / 3600,
            2
        );


    }


}






/*
==========================================================
                DISPLAY TIME
==========================================================
*/


if(!function_exists("displayTime")){


    function displayTime(
        ?string $time
    ): string
    {


        if(empty($time)){


            return "--:--";


        }



        return date(
            "h:i A",
            strtotime($time)
        );


    }


}







/*
==========================================================
                LOAD ATTENDANCE SETTINGS
==========================================================
*/


if(!function_exists("loadAttendanceSettings")){


    function loadAttendanceSettings(
        PDO $conn
    ): array
    {


        $stmt = $conn->prepare("

            SELECT *

            FROM attendance_settings

            LIMIT 1

        ");



        $stmt->execute();



        $settings = $stmt->fetch(
            PDO::FETCH_ASSOC
        );




        if(!$settings){


            return [

                "time_in_start" => "07:00:00",

                "time_in_end" => "08:30:00",

                "late_until" => "08:15:00",

                "absent_time" => "09:00:00",


                "break_out_time" => "12:00:00",

                "break_in_time" => "12:30:00",


                "time_out_time" => "17:00:00",


                "required_hours" => 8,

                "break_minutes" => 30,


                "minimum_ot" => 2,

                "maximum_ot" => 4,


                "work_days" =>
                "Monday,Tuesday,Wednesday,Thursday,Friday,Saturday"

            ];


        }




        return $settings;


    }


}





/*
==========================================================
                SESSION ALERT
==========================================================
*/


if(!function_exists("attendanceAlert")){


    function attendanceAlert(
        string $message,
        string $type="success"
    ): void
    {


        $_SESSION["attendance_message"] = $message;


        $_SESSION["attendance_type"] = $type;


    }


}




/*
==========================================================
                END OF PART 1
==========================================================
*/

/*
==========================================================
                PART 2
        ATTENDANCE QUERIES
==========================================================
*/



/*
==========================================================
                GET TODAY ATTENDANCE
==========================================================
*/


if(!function_exists("getTodayAttendance")){


    function getTodayAttendance(
        PDO $conn,
        int $employee_id
    ): ?array
    {


        $stmt = $conn->prepare("

            SELECT *

            FROM attendance

            WHERE employee_id = ?

            AND attendance_date = ?

            LIMIT 1

        ");



        $stmt->execute([


            $employee_id,


            attendanceToday()


        ]);




        $attendance = $stmt->fetch(
            PDO::FETCH_ASSOC
        );



        return $attendance ?: null;


    }


}


/*
==========================================================
                GET ATTENDANCE TIMELINE
==========================================================
*/


if(!function_exists("getAttendanceTimeline")){


    function getAttendanceTimeline(
        PDO $conn,
        int $attendance_id
    ): array
    {


        $stmt = $conn->prepare("

            SELECT *

            FROM attendance_timeline

            WHERE attendance_id = ?

            ORDER BY activity_time ASC

        ");




        $stmt->execute([

            $attendance_id

        ]);




        return $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );


    }


}








/*
==========================================================
                GET EMPLOYEE DATA
==========================================================
*/


if(!function_exists("getEmployee")){


    function getEmployee(
        PDO $conn,
        int $employee_id
    ): ?array
    {


        $stmt = $conn->prepare("

            SELECT *

            FROM employees

            WHERE employee_id = ?

            LIMIT 1

        ");




        $stmt->execute([


            $employee_id


        ]);




        $employee = $stmt->fetch(
            PDO::FETCH_ASSOC
        );




        return $employee ?: null;


    }


}








/*
==========================================================
                GET TODAY HOLIDAY
==========================================================
*/


if(!function_exists("getTodayHoliday")){


    function getTodayHoliday(
        PDO $conn
    ): ?array
    {


        $stmt = $conn->prepare("

            SELECT *

            FROM holidays

            WHERE holiday_date = ?

            AND status = 'Active'

            LIMIT 1

        ");




        $stmt->execute([


            attendanceToday()


        ]);





        $holiday = $stmt->fetch(
            PDO::FETCH_ASSOC
        );




        return $holiday ?: null;


    }


}








/*
==========================================================
                CHECK HOLIDAY
==========================================================
*/


if(!function_exists("isHoliday")){


    function isHoliday(
        PDO $conn
    ): bool
    {


        return getTodayHoliday(
            $conn
        ) !== null;


    }


}








/*
==========================================================
                GET TODAY LEAVE
==========================================================
*/


if(!function_exists("getTodayLeave")){


    function getTodayLeave(
        PDO $conn,
        int $employee_id
    ): ?array
    {


        $stmt = $conn->prepare("

            SELECT *

            FROM leave_requests

            WHERE employee_id = ?

            AND status = 'Approved'

            AND ? BETWEEN start_date AND end_date

            LIMIT 1

        ");





        $stmt->execute([


            $employee_id,


            attendanceToday()


        ]);





        $leave = $stmt->fetch(
            PDO::FETCH_ASSOC
        );





        return $leave ?: null;


    }


}








/*
==========================================================
                CHECK ON LEAVE
==========================================================
*/


if(!function_exists("isOnLeave")){


    function isOnLeave(
        PDO $conn,
        int $employee_id
    ): bool
    {


        return getTodayLeave(

            $conn,

            $employee_id

        ) !== null;


    }


}








/*
==========================================================
                CHECK REST DAY
==========================================================
*/


if(!function_exists("isRestDay")){


    function isRestDay(
        PDO $conn
    ): bool
    {


        $settings = loadAttendanceSettings(

            $conn

        );





        $workDays = array_map(

            "trim",

            explode(

                ",",

                $settings["work_days"]

            )

        );






        return !in_array(

            attendanceDay(),

            $workDays

        );


    }


}








/*
==========================================================
                ADD ATTENDANCE TIMELINE
==========================================================
*/


if(!function_exists("addAttendanceTimeline")){


    function addAttendanceTimeline(
        PDO $conn,
        int $attendance_id,
        int $employee_id,
        string $activity
    ): bool
    {


        $stmt = $conn->prepare("

            INSERT INTO attendance_timeline

            (

                attendance_id,

                employee_id,

                activity,

                activity_time

            )

            VALUES

            (

                ?,

                ?,

                ?,

                ?

            )

        ");





        return $stmt->execute([


            $attendance_id,


            $employee_id,


            $activity,


            attendanceDateTime()


        ]);



    }


}








/*
==========================================================
                END OF PART 2
==========================================================
*/

/*
==========================================================
                PART 3
        ATTENDANCE CALCULATION ENGINE
==========================================================
*/



/*
==========================================================
                CALCULATE LATE MINUTES
==========================================================
*/


if(!function_exists("calculateLateMinutes")){


    function calculateLateMinutes(
        ?string $timeIn,
        array $settings
    ): int
    {


        if(empty($timeIn)){


            return 0;


        }





        $timeInSeconds = timeToSeconds(

            $timeIn

        );





        $lateLimit = timeToSeconds(

            $settings["late_until"]

        );







        if($timeInSeconds <= $lateLimit){


            return 0;


        }






        return floor(

            ($timeInSeconds - $lateLimit) / 60

        );


    }


}








/*
==========================================================
                CALCULATE BREAK TIME
==========================================================
*/


if(!function_exists("calculateBreakSeconds")){


    function calculateBreakSeconds(
        array $attendance
    ): int
    {



        if(

            empty($attendance["break_out"])

            ||

            empty($attendance["break_in"])

        ){


            return 0;


        }






        $breakOut = timeToSeconds(

            $attendance["break_out"]

        );





        $breakIn = timeToSeconds(

            $attendance["break_in"]

        );






        return max(

            0,

            $breakIn - $breakOut

        );


    }


}








/*
==========================================================
                CALCULATE WORKED SECONDS
==========================================================
*/


if(!function_exists("calculateWorkedSeconds")){


    function calculateWorkedSeconds(
        array $attendance
    ): int
    {



        if(empty($attendance["time_in"])){


            return 0;


        }






        $timeIn = timeToSeconds(

            $attendance["time_in"]

        );







        if(!empty($attendance["time_out"])){

            
            $timeOut = timeToSeconds(

                $attendance["time_out"]

            );


        }
        else{


            $timeOut = timeToSeconds(

                attendanceNow()

            );


        }






        $worked = 

            $timeOut 

            -

            $timeIn;







        /*
        Remove Break Time
        */


        $worked -= calculateBreakSeconds(

            $attendance

        );







        return max(

            0,

            $worked

        );


    }


}








/*
==========================================================
                REQUIRED WORK HOURS
==========================================================
*/


if(!function_exists("requiredWorkingSeconds")){


    function requiredWorkingSeconds(
        array $settings
    ): int
    {


        return hoursToSeconds(

            $settings["required_hours"]

        );


    }


}








/*
==========================================================
                WORKING HOURS FORMAT
==========================================================
*/


if(!function_exists("calculateWorkingHours")){


    function calculateWorkingHours(
        array $attendance
    ): string
    {


        return formatSeconds(

            calculateWorkedSeconds(

                $attendance

            )

        );


    }


}








/*
==========================================================
                CALCULATE OVERTIME
==========================================================
*/


if(!function_exists("calculateOvertime")){


    function calculateOvertime(
        array $attendance,
        array $settings
    ): string
    {



        $workedSeconds = calculateWorkedSeconds(

            $attendance

        );





        $requiredSeconds = requiredWorkingSeconds(

            $settings

        );







        $overtime = max(

            0,

            $workedSeconds - $requiredSeconds

        );








        /*
        Maximum OT Limit
        From attendance_settings
        */


        $maxOT = hoursToSeconds(

            $settings["maximum_ot"]

        );





        if($overtime > $maxOT){


            $overtime = $maxOT;


        }






        return formatSeconds(

            $overtime

        );


    }


}








/*
==========================================================
                CALCULATE UNDERTIME
==========================================================
*/


if(!function_exists("calculateUndertime")){


    function calculateUndertime(
        array $attendance,
        array $settings
    ): string
    {


        $workedSeconds = calculateWorkedSeconds(

            $attendance

        );





        $requiredSeconds = requiredWorkingSeconds(

            $settings

        );







        $undertime = max(

            0,

            $requiredSeconds - $workedSeconds

        );






        return formatSeconds(

            $undertime

        );


    }


}








/*
==========================================================
                ATTENDANCE STATUS
==========================================================
*/


if(!function_exists("calculateAttendanceStatus")){


    function calculateAttendanceStatus(
        array $attendance,
        array $settings
    ): string
    {


        if(empty($attendance["time_in"])){


            return "Not Started";


        }







        if(!empty($attendance["time_out"])){


            return "Completed";


        }








        if(

            !empty($attendance["break_out"])

            &&

            empty($attendance["break_in"])

        ){


            return "On Break";


        }








        $late = calculateLateMinutes(

            $attendance["time_in"],

            $settings

        );








        if($late > 0){


            return "Late";


        }








        return "Working";


    }


}








/*
==========================================================
                ATTENDANCE SUMMARY
==========================================================
*/


if(!function_exists("attendanceSummary")){


    function attendanceSummary(
        array $attendance,
        array $settings
    ): array
    {


        return [



            "late_minutes" => calculateLateMinutes(

                $attendance["time_in"] ?? null,

                $settings

            ),






            "working_hours" => calculateWorkingHours(

                $attendance

            ),






            "overtime" => calculateOvertime(

                $attendance,

                $settings

            ),






            "undertime" => calculateUndertime(

                $attendance,

                $settings

            ),






            "status" => calculateAttendanceStatus(

                $attendance,

                $settings

            )



        ];


    }


}








/*
==========================================================
                END OF PART 3
==========================================================
*/

/*
==========================================================
                PART 4
        ATTENDANCE VALIDATION ENGINE
==========================================================
*/



/*
==========================================================
                TIME IN VALIDATION
==========================================================
*/


if(!function_exists("canTimeIn")){


    function canTimeIn(
        PDO $conn,
        int $employee_id,
        ?array $attendance,
        array $settings
    ): array
    {


        /*
        Already Time In
        */


        if($attendance){


            return [

                "allowed" => false,

                "message" => "Already Timed In."

            ];


        }







        /*
        Check Leave
        */


        if(isOnLeave(

            $conn,

            $employee_id

        )){


            return [

                "allowed" => false,

                "message" => "You are on approved leave today."

            ];


        }








        /*
        Check Holiday
        */


        if(isHoliday($conn)){


            return [

                "allowed" => false,

                "message" => "Today is a holiday."

            ];


        }








        /*
        Check Rest Day
        */


        if(isRestDay($conn)){


            return [

                "allowed" => false,

                "message" => "Today is your rest day."

            ];


        }








        $now = timeToSeconds(

            attendanceNow()

        );





        $start = timeToSeconds(

            $settings["time_in_start"]

        );





        $absent = timeToSeconds(

            $settings["absent_time"]

        );








        if($now < $start){


            return [

                "allowed" => false,

                "message" => "Time In is not available yet."

            ];


        }








        if($now >= $absent){


            return [

                "allowed" => false,

                "message" => "Attendance period already ended."

            ];


        }








        return [

            "allowed" => true,

            "message" => ""

        ];



    }


}








/*
==========================================================
                BREAK OUT VALIDATION
==========================================================
*/


if(!function_exists("canBreakOut")){


    function canBreakOut(
        ?array $attendance,
        array $settings
    ): array
    {



        if(!$attendance){


            return [

                "allowed" => false,

                "message" => "Please Time In first."

            ];


        }








        if(!empty($attendance["break_out"])){


            return [

                "allowed" => false,

                "message" => "Already Break Out."

            ];


        }








        if(!empty($attendance["time_out"])){


            return [

                "allowed" => false,

                "message" => "Attendance already completed."

            ];


        }








        $now = timeToSeconds(

            attendanceNow()

        );





        $breakTime = timeToSeconds(

            $settings["break_out_time"]

        );







        if($now < $breakTime){


            return [

                "allowed" => false,

                "message" => "Break Out is not available yet."

            ];


        }








        return [

            "allowed" => true,

            "message" => ""

        ];



    }


}








/*
==========================================================
                BREAK IN VALIDATION
==========================================================
*/


if(!function_exists("canBreakIn")){


    function canBreakIn(
        ?array $attendance,
        array $settings
    ): array
    {



        if(!$attendance){


            return [

                "allowed" => false,

                "message" => "No attendance record found."

            ];


        }








        if(empty($attendance["break_out"])){


            return [

                "allowed" => false,

                "message" => "Break Out first."

            ];


        }








        if(!empty($attendance["break_in"])){


            return [

                "allowed" => false,

                "message" => "Already Break In."

            ];


        }








        $now = timeToSeconds(

            attendanceNow()

        );





        $breakInTime = timeToSeconds(

            $settings["break_in_time"]

        );







        if($now < $breakInTime){


            return [

                "allowed" => false,

                "message" => "Break In is not available yet."

            ];


        }








        return [

            "allowed" => true,

            "message" => ""

        ];



    }


}








/*
==========================================================
                TIME OUT VALIDATION
==========================================================
*/


if(!function_exists("canTimeOut")){


    function canTimeOut(
        ?array $attendance,
        array $settings
    ): array
    {



        if(!$attendance){


            return [

                "allowed" => false,

                "message" => "No attendance record found."

            ];


        }








        if(!empty($attendance["time_out"])){


            return [

                "allowed" => false,

                "message" => "Already Timed Out."

            ];


        }








        if(

            !empty($attendance["break_out"])

            &&

            empty($attendance["break_in"])

        ){


            return [

                "allowed" => false,

                "message" => "Please Break In first."

            ];


        }








        $now = timeToSeconds(

            attendanceNow()

        );





        $timeOut = timeToSeconds(

            $settings["time_out_time"]

        );








        if($now < $timeOut){


            return [

                "allowed" => false,

                "message" => "Time Out is not available yet."

            ];


        }








        return [

            "allowed" => true,

            "message" => ""

        ];



    }


}








/*
==========================================================
                ATTENDANCE BUTTON ENGINE
==========================================================
*/


if(!function_exists("attendanceButtons")){


    function attendanceButtons(
        PDO $conn,
        int $employee_id,
        ?array $attendance,
        array $settings
    ): array
    {



        return [



            "timein" => canTimeIn(

                $conn,

                $employee_id,

                $attendance,

                $settings

            ),





            "breakout" => canBreakOut(

                $attendance,

                $settings

            ),






            "breakin" => canBreakIn(

                $attendance,

                $settings

            ),






            "timeout" => canTimeOut(

                $attendance,

                $settings

            )



        ];



    }


}








/*
==========================================================
                END OF PART 4
==========================================================
*/

/*
==========================================================
                PART 5
        FINAL ATTENDANCE STATUS ENGINE
==========================================================
*/



if(!function_exists("attendanceStatus")){


    function attendanceStatus(
        PDO $conn,
        int $employee_id
    ): array
    {



        /*
        ==================================================
                    LOAD SETTINGS
        ==================================================
        */


        $settings = loadAttendanceSettings(

            $conn

        );







        /*
        ==================================================
                    GET ATTENDANCE
        ==================================================
        */


        $attendance = getTodayAttendance(

            $conn,

            $employee_id

        );








        /*
        ==================================================
                    CHECK SPECIAL DAYS
        ==================================================
        */


        $holiday = getTodayHoliday(

            $conn

        );





        $leave = getTodayLeave(

            $conn,

            $employee_id

        );









        /*
        ==================================================
                    SUMMARY
        ==================================================
        */


        if($attendance){



            $summary = attendanceSummary(

                $attendance,

                $settings

            );



        }
        else
        {



            $summary = [



                "late_minutes" => 0,



                "working_hours" => "00:00:00",



                "overtime" => "00:00:00",



                "undertime" => "00:00:00",



                "status" => "Not Started"



            ];



        }









        /*
        ==================================================
                    BUTTON STATUS
        ==================================================
        */


        $buttons = attendanceButtons(

            $conn,

            $employee_id,

            $attendance,

            $settings

        );









        /*
        ==================================================
                    LIVE MONITOR
        ==================================================
        */


        $monitor = "Offline";





        if($attendance){



            if(!empty($attendance["time_out"])){


                $monitor = "Completed";


            }



            elseif(

                !empty($attendance["break_out"])

                &&

                empty($attendance["break_in"])

            ){


                $monitor = "On Break";


            }



            elseif(!empty($attendance["time_in"])){


                $monitor = "Working";


            }



        }









        /*
        ==================================================
                    STATUS CARD
        ==================================================
        */


        $statusTitle = $summary["status"];


        $statusMessage = "";


        $statusClass = "primary";









        /*
        APPROVED LEAVE
        */


        if($leave){



            $statusTitle = "Leave";


            $statusClass = "warning";


            $statusMessage =
                "You are on approved leave today.";



        }







        /*
        HOLIDAY
        */


        elseif($holiday){



            $statusTitle = "Holiday";


            $statusClass = "info";


            $statusMessage =
                $holiday["holiday_name"];



        }








        /*
        REST DAY
        */


        elseif(isRestDay($conn)){



            $statusTitle = "Rest Day";


            $statusClass = "secondary";


            $statusMessage =
                "Today is your scheduled rest day.";



        }








        else
        {



            switch($summary["status"]){



                case "Working":


                    $statusClass = "success";


                    $statusMessage =
                        "Currently working.";


                break;





                case "Late":


                    $statusClass = "warning";


                    $statusMessage =

                        $summary["late_minutes"]

                        .

                        " minute(s) late.";


                break;





                case "Completed":


                    $statusClass = "success";


                    $statusMessage =
                        "Attendance completed.";


                break;





                case "On Break":


                    $statusClass = "info";


                    $statusMessage =
                        "Currently on break.";


                break;





                default:


                    $statusClass = "primary";


                    $statusMessage =
                        "Waiting for Time In.";



            }


        }









        /*
        ==================================================
                    TIMELINE
        ==================================================
        */


        $timeline = [];





        if($attendance){



            $timeline = getAttendanceTimeline(

                $conn,

                $attendance["attendance_id"]

            );



        }









        /*
        ==================================================
                    RETURN DATA
        ==================================================
        */


        return [



            /*
            SETTINGS
            */

            "settings" => $settings,







            /*
            ATTENDANCE
            */

            "attendance" => $attendance,








            /*
            SPECIAL DAYS
            */

            "holiday" => $holiday,


            "leave" => $leave,








            /*
            STATUS
            */

            "status_title" => $statusTitle,


            "status_message" => $statusMessage,


            "status_class" => $statusClass,








            /*
            LIVE MONITOR
            */

            "monitor" => $monitor,








            /*
            SUMMARY
            */

            "summary" => $summary,








            /*
            BUTTONS
            */

            "buttons" => $buttons,








            /*
            TIMELINE
            */

            "timeline" => $timeline



        ];



    }


}









/*
==========================================================
                LIVE ATTENDANCE DATA
==========================================================
*/


if(!function_exists("attendanceLiveData")){


    function attendanceLiveData(
        ?array $attendance,
        array $settings
    ): array
    {



        return [



            "timeIn" =>

                $attendance["time_in"] ?? null,




            "breakOut" =>

                $attendance["break_out"] ?? null,




            "breakIn" =>

                $attendance["break_in"] ?? null,




            "timeOut" =>

                $attendance["time_out"] ?? null,




            "requiredSeconds" =>

                requiredWorkingSeconds(

                    $settings

                )



        ];



    }


}



/*
==========================================================
                CSRF VALIDATION
==========================================================
*/


if (!function_exists("verifyCSRFToken")) {


    function verifyCSRFToken(): bool
    {


        if (
            empty($_POST["csrf_token"])
        ) {


            return false;

        }



        if (
            empty($_SESSION["csrf_token"])
        ) {


            return false;

        }



        return hash_equals(

            $_SESSION["csrf_token"],

            $_POST["csrf_token"]

        );


    }

}




/*
==========================================================
                END OF PART 5
        END attendance_helper.php
==========================================================
*/