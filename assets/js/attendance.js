/*
==========================================================
        EMPLOYEE ATTENDANCE LIVE TIMER

        Daily Cravings Foods Inc.

        FINAL LIVE VERSION

        Automatic OT System

        No OT Request
        No Half Day Request

==========================================================
*/


document.addEventListener(
"DOMContentLoaded",
function(){



/*
==========================================================
                CHECK DATA
==========================================================
*/


if(
typeof attendanceData === "undefined"
){

    return;

}






/*
==========================================================
                ELEMENTS
==========================================================
*/


const workingHoursElement =
document.getElementById(
"liveWorkingHours"
);



const workingMonitorElement =
document.getElementById(
"liveWorkingHoursMonitor"
);



const remainingElement =
document.getElementById(
"liveRemainingTime"
);



const remainingMonitorElement =
document.getElementById(
"liveRemainingMonitor"
);



const overtimeElement =
document.getElementById(
"liveOvertime"
);



const undertimeElement =
document.getElementById(
"liveUndertime"
);



const activityElement =
document.querySelector(
".live-monitor-content h2"
);








/*
==========================================================
                DATA
==========================================================
*/


const timeIn =
attendanceData.timeIn;



const timeOut =
attendanceData.timeOut;



const breakOut =
attendanceData.breakOut;



const breakIn =
attendanceData.breakIn;



const requiredSeconds =
parseInt(
attendanceData.requiredSeconds
)
||
28800;








/*
==========================================================
                TIME CONVERT
==========================================================
*/


function timeToSeconds(time){


    if(!time){

        return 0;

    }



    let parts =
    time.split(":");



    return (

        parseInt(parts[0]) * 3600

    )

    +

    (

        parseInt(parts[1]) * 60

    )

    +

    (

        parseInt(parts[2] || 0)

    );


}








/*
==========================================================
                FORMAT
==========================================================
*/


function formatSeconds(seconds){



    if(seconds < 0){

        seconds = 0;

    }



    let hours =
    Math.floor(
        seconds / 3600
    );



    let minutes =
    Math.floor(
        (
            seconds % 3600
        )
        /
        60
    );



    let secs =
    seconds % 60;




    return (

        String(hours).padStart(2,"0")

        +

        ":"

        +

        String(minutes).padStart(2,"0")

        +

        ":"

        +

        String(secs).padStart(2,"0")

    );


}










/*
==========================================================
                CREATE DATE TODAY
==========================================================
*/


function createTodayTime(time){


    let today =
    new Date();



    let date =
    today.toISOString()
    .split("T")[0];



    return new Date(

        date
        +
        " "
        +
        time

    );


}









/*
==========================================================
                BREAK TIME
==========================================================
*/


function getBreakSeconds(){



    if(
        !breakOut
        ||
        !breakIn
    ){

        return 0;

    }



    return (

        timeToSeconds(breakIn)

        -

        timeToSeconds(breakOut)

    );


}









/*
==========================================================
                UPDATE TIMER
==========================================================
*/


function updateAttendance(){



    if(!timeIn){

        if(activityElement){

            activityElement.innerHTML =
            "Not Started";

        }


        return;

    }








    let start =
    createTodayTime(
        timeIn
    );




    let now =
    new Date();




    let end;





    if(timeOut){


        end =
        createTodayTime(
            timeOut
        );


    }

    else{


        end =
        now;


    }







    let totalSeconds =

    Math.floor(

        (

            end - start

        )

        /

        1000

    );








    /*
    REMOVE BREAK
    */


    totalSeconds -=

    getBreakSeconds();





    if(totalSeconds < 0){

        totalSeconds = 0;

    }








    /*
    ===============================
            WORKING HOURS
    ===============================
    */


    let working =
    formatSeconds(
        totalSeconds
    );




    if(workingHoursElement){

        workingHoursElement.innerHTML =
        working;

    }




    if(workingMonitorElement){

        workingMonitorElement.innerHTML =
        working;

    }









    /*
    ===============================
            REMAINING
    ===============================
    */


    let remaining =

    requiredSeconds

    -

    totalSeconds;





    if(remaining < 0){

        remaining = 0;

    }





    let remainingFormatted =

    formatSeconds(
        remaining
    );





    if(remainingElement){

        remainingElement.innerHTML =
        remainingFormatted;

    }



    if(remainingMonitorElement){

        remainingMonitorElement.innerHTML =
        remainingFormatted;

    }









    /*
    ===============================
            OVERTIME
    ===============================
    */


    let overtime =

    totalSeconds

    -

    requiredSeconds;





    if(overtime < 0){

        overtime = 0;

    }






    if(overtimeElement){

        overtimeElement.innerHTML =

        formatSeconds(
            overtime
        );

    }









    /*
    ===============================
            UNDERTIME

        ONLY AFTER TIME OUT

    ===============================
    */


    let undertime = 0;





    if(timeOut){



        undertime =

        requiredSeconds

        -

        totalSeconds;



        if(undertime < 0){

            undertime = 0;

        }



    }





    if(undertimeElement){

        undertimeElement.innerHTML =

        formatSeconds(
            undertime
        );


    }









    /*
    ===============================
            CURRENT ACTIVITY
    ===============================
    */


    if(activityElement){



        if(timeOut){


            activityElement.innerHTML =
            "Completed";


        }


        else if(
            breakOut
            &&
            !breakIn
        ){


            activityElement.innerHTML =
            "On Break";


        }


        else{


            activityElement.innerHTML =
            "Working";


        }



    }





}









/*
==========================================================
                START LIVE
==========================================================
*/


updateAttendance();



setInterval(

updateAttendance,

1000

);



});