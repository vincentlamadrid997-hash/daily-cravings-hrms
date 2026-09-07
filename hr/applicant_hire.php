<?php

session_start();

require_once "../auth/hr_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";


if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: applicants.php");
    exit;

}

requireCSRFToken("applicants.php");


if (isset($_POST['application_id'])) {

    $_SESSION['selected_application'] = (int) $_POST['application_id'];

}

if (!isset($_SESSION['selected_application'])) {

    $_SESSION['hire_error'] = "Invalid applicant.";

    header("Location: applicants.php");
    exit;

}

$application_id = (int) $_SESSION['selected_application'];


try {

    $conn->beginTransaction();


    $stmt = $conn->prepare("

        SELECT

            a.*,

            jp.department_id,
            jp.position_id,
            jp.job_title

        FROM applications a

        INNER JOIN job_postings jp

            ON jp.job_id = a.job_id

        WHERE a.application_id = ?

        LIMIT 1

    ");

    $stmt->execute([
        $application_id
    ]);

    $applicant = $stmt->fetch(PDO::FETCH_ASSOC);


    if (!$applicant) {

        throw new Exception(
            "Applicant record not found."
        );

    }


    if (trim($applicant['status']) !== "Accepted") {

        throw new Exception(
            "Applicant must be Accepted first before hiring."
        );

    }


    if (

        $applicant['is_hired'] === "Yes"

        ||

        !empty($applicant['employee_id'])

    ) {

        throw new Exception(
            "Applicant has already been hired."
        );

    }


    $check = $conn->prepare("

        SELECT employee_id

        FROM employees

        WHERE email = ?

        LIMIT 1

    ");

    $check->execute([

        $applicant['email']

    ]);

    if ($check->fetch()) {

        throw new Exception(

            "Email address already exists in Employees."

        );

    }


    $lastEmployee = $conn->query("

        SELECT employee_code

        FROM employees

        ORDER BY employee_id DESC

        LIMIT 1

    ")->fetch(PDO::FETCH_ASSOC);

    if (

        $lastEmployee

        &&

        !empty($lastEmployee['employee_code'])

    ) {

        preg_match(

            '/(\d+)$/',

            $lastEmployee['employee_code'],

            $matches

        );

        $number = isset($matches[1])

            ? (int) $matches[1] + 1

            : 5;

    } else {

        $number = 5;

    }

    $employee_code = "DCI-EMP-" .

        str_pad(

            $number,

            4,

            "0",

            STR_PAD_LEFT

        );


    $insert = $conn->prepare("

        INSERT INTO employees (

            employee_code,
            first_name,
            middle_name,
            last_name,
            email,
            phone,
            birthdate,
            gender,
            civil_status,
            address,
            department_id,
            position_id,
            employment_status,
            hire_date

        )

        VALUES (

            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?

        )

    ");

    $insert->execute([

        $employee_code,

        $applicant['first_name'],
        $applicant['middle_name'],
        $applicant['last_name'],

        $applicant['email'],
        $applicant['phone'],

        $applicant['birthdate'],
        $applicant['gender'],
        $applicant['civil_status'],
        $applicant['address'],

        $applicant['department_id'],
        $applicant['position_id'],

        "Active",

        date("Y-m-d")

    ]);


    $employee_id = (int) $conn->lastInsertId();


    $update = $conn->prepare("

        UPDATE applications

        SET

            employee_id = ?,
            is_hired = 'Yes'

        WHERE application_id = ?

    ");

    $update->execute([

        $employee_id,

        $application_id

    ]);


    $conn->commit();

    unset($_SESSION['selected_application']);

    $_SESSION['hire_success'] =
        "Applicant has been hired successfully.";

    header("Location: applicants.php");

    exit;

} catch (Exception $e) {

    if ($conn->inTransaction()) {

        $conn->rollBack();

    }

    unset($_SESSION['selected_application']);

    $_SESSION['hire_error'] = $e->getMessage();

    header("Location: applicants.php");

    exit;

}