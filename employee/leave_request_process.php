<?php


session_start();


date_default_timezone_set("Asia/Manila");


require_once "../auth/employee_auth.php";

require_once "../config/db.php";
require_once "../config/csrf.php";


$employee_id = $_SESSION["employee_id"] ?? 0;


if (!$employee_id){

    header("Location: ../login.php");
    exit;

}

if ($_SERVER["REQUEST_METHOD"] !== "POST"){

    header("Location: leave_request.php");
    exit;

}

requireCSRFToken("leave_request.php", "leave_error");


$leave_type = $_POST["leave_type"] ?? "";

$custom_leave_type = trim(
    $_POST["other_leave_type"] ?? ""
);


$start_date = $_POST["start_date"] ?? "";

$end_date = $_POST["end_date"] ?? "";

$reason = trim(
    $_POST["reason"] ?? ""
);


$attachmentName = null;


function redirectError($message)
{

    $_SESSION["leave_error"] = $message;

    header(
        "Location: leave_request.php"
    );
    exit;

}


function redirectSuccess($message)
{

    $_SESSION["leave_success"] = $message;

    header(
        "Location: leave_history.php"
    );
    exit;

}

if (empty($leave_type)){

    redirectError(
        "Please select a leave type."
    );

}

if (empty($start_date)){

    redirectError(
        "Start date is required."
    );

}

if (empty($end_date)){

    redirectError(
        "End date is required."
    );

}

if (empty($reason)){

    redirectError(
        "Leave reason is required."
    );

}


if (
    $leave_type === "others"
    &&
    empty($custom_leave_type)
){

    redirectError(
        "Please specify your leave type."
    );

}


$start = new DateTime($start_date);

$end = new DateTime($end_date);


if ($end < $start){

    redirectError(
        "End date cannot be earlier than start date."
    );

}


$leave_type_id = null;

if ($leave_type !== "others"){

    $leaveCheck = $conn->prepare("

        SELECT
            leave_type_id
        FROM leave_types
        WHERE
            leave_type_id = :leave_type_id
        AND
            status='Active'
        LIMIT 1

    ");

    $leaveCheck->execute([

        ":leave_type_id" => $leave_type

    ]);

    $leaveResult =
    $leaveCheck->fetch(PDO::FETCH_ASSOC);

    if (!$leaveResult){

        redirectError(
            "Invalid leave type selected."
        );

    }

    $leave_type_id =
    $leaveResult["leave_type_id"];

}


$duplicateStmt = $conn->prepare("

SELECT
    leave_id
FROM leave_requests
WHERE
    employee_id = :employee_id
AND
    start_date = :start_date
AND
    end_date = :end_date
AND
    status IN

    (
        'Pending',
        'Approved'
    )

LIMIT 1

");

$duplicateStmt->execute([

    ":employee_id" => $employee_id,
    ":start_date" => $start_date,
    ":end_date" => $end_date

]);

if ($duplicateStmt->fetch()){

    redirectError(

        "You already have an existing leave request for this date."

    );

}

$total_days = 0;

$current = clone $start;


$holidayStmt = $conn->prepare("

SELECT
    holiday_date
FROM holidays
WHERE status='Active'

");

$holidayStmt->execute();

$holidays = [];

foreach (
    $holidayStmt->fetchAll(PDO::FETCH_ASSOC)
    as $holiday
){

    $holidays[] =
    $holiday["holiday_date"];

}

while ($current <= $end){

    $day =
    $current->format("N");

    $date =
    $current->format("Y-m-d");

    $isWeekend =
    ($day >= 6);

    $isHoliday =
    in_array(
        $date,
        $holidays
    );

    if (
        !$isWeekend
        &&
        !$isHoliday
    ){

        $total_days++;

    }

    $current->modify("+1 day");

}


if ($total_days <= 0){

    redirectError(

        "Selected dates are not valid working days."

    );


}


if (
    isset($_FILES["attachment"])
    &&
    $_FILES["attachment"]["error"]
    === UPLOAD_ERR_OK
){

    $file =
    $_FILES["attachment"];


    $allowedExtensions = [

        "pdf",
        "jpg",
        "jpeg",
        "png",
        "doc",
        "docx"

    ];

    $fileExtension =
    strtolower(
        pathinfo(
            $file["name"],
            PATHINFO_EXTENSION
        )
    );

    if (
        !in_array(
            $fileExtension,
            $allowedExtensions
        )
    ){

        redirectError(
            "Invalid attachment file type."
        );

    }

    if (
        $file["size"]
        >
        5 * 1024 * 1024
    ){

        redirectError(
            "Attachment size must not exceed 5MB."
        );

    }

}


if (
    isset($_FILES["attachment"])
    &&
    $_FILES["attachment"]["error"]
    === UPLOAD_ERR_OK
){


    $uploadDirectory = "../uploads/leave_attachments/";

    if (!is_dir($uploadDirectory)){

        mkdir(
            $uploadDirectory,
            0777,
            true
        );

    }


    $newFileName =

        "leave_"
        .
        $employee_id
        .
        "_"
        .
        date("YmdHis")
        .
        "."
        .
        $fileExtension;


    $uploadPath =
    $uploadDirectory . $newFileName;

        if (
        move_uploaded_file(
            $file["tmp_name"],
            $uploadPath
        )
    ){

        $attachmentName =
        $newFileName;

    }
    else{

        redirectError(
            "Failed to upload attachment."
        );

    }

}


try{

    $insertStmt = $conn->prepare("

    INSERT INTO leave_requests
    (

        employee_id,
        leave_type_id,
        custom_leave_type,
        start_date,
        end_date,
        total_days,
        reason,
        attachment,
        status

    )

    VALUES

    (

        :employee_id,
        :leave_type_id,
        :custom_leave_type,
        :start_date,
        :end_date,
        :total_days,
        :reason,
        :attachment,
        'Pending'

    )

    ");

        $insertStmt->execute([

        ":employee_id" => $employee_id,
        ":leave_type_id" => $leave_type_id,
        ":custom_leave_type" =>

            (
                $leave_type === "others"
                ?
                $custom_leave_type
                :
                null
            ),

        ":start_date" => $start_date,
        ":end_date" => $end_date,
        ":total_days" => $total_days,
        ":reason" => $reason,
        ":attachment" => $attachmentName

    ]);

    $notifyStmt = $conn->prepare("
        INSERT INTO notifications (user_id, message, redirect_url, status)
        VALUES (NULL, ?, 'leaves.php', 'Unread')
    ");

    $notifyStmt->execute([
        htmlspecialchars($_SESSION['full_name'] ?? 'An employee') . " submitted a new leave request."
    ]);


    $_SESSION["leave_success"] =

    "Leave request submitted successfully.
     Your request is now waiting for approval.";

    header(
        "Location: leave_history.php"
    );
    exit;


}

catch(PDOException $e){


    /*
    ======================================================
            DELETE UPLOADED FILE IF INSERT FAILED
    ======================================================
    */


    if(
        !empty($attachmentName)
        &&
        file_exists(
            "../uploads/leave_attachments/"
            .
            $attachmentName
        )
    ){

        unlink(
            "../uploads/leave_attachments/"
            .
            $attachmentName
        );

    }




    $_SESSION["leave_error"] = 
    "Unable to submit leave request.
    Please try again.";





    header(
        "Location: leave_request.php"
    );

    exit;


}