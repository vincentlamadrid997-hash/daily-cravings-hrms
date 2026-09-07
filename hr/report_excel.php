<?php

session_start();

require_once "../auth/hr_auth.php";


if (
    empty($_SESSION['report_ready']) ||
    empty($_SESSION['report_title']) ||
    empty($_SESSION['report_columns'])
) {

    $_SESSION['report_error'] =
        "Please generate a report first before exporting Excel.";

    header("Location: reports.php");
    exit;

}


$report_title = $_SESSION['report_title'];

$columns = $_SESSION['report_columns'];

$data = $_SESSION['report_data'] ?? [];



header(
    "Content-Type: application/vnd.ms-excel; charset=utf-8"
);

header(
    "Content-Disposition: attachment; filename="
    . str_replace(" ", "_", $report_title)
    . ".xls"
);

header("Pragma: no-cache");

header("Expires: 0");


echo "
<table border='1'>

<tr>
    <th colspan='" . count($columns) . "'>
        Daily Cravings Foods Inc.
    </th>
</tr>

<tr>
    <th colspan='" . count($columns) . "'>
        " . htmlspecialchars($report_title) . "
    </th>
</tr>

<tr>
    <th colspan='" . count($columns) . "'>
        Generated HR Report
    </th>
</tr>

<tr></tr>

<tr>
";


foreach ($columns as $column) {

    echo "
    <th>
        " . htmlspecialchars($column) . "
    </th>
    ";

}


echo "
</tr>
";


if (!empty($data)) {


    foreach ($data as $row) {


        echo "<tr>";


        foreach ($row as $key => $value) {


            if (
                in_array(
                    $key,
                    [
                        "basic_salary",
                        "allowances",
                        "deductions",
                        "gross_pay",
                        "net_pay"
                    ]
                )
                && is_numeric($value)
            ) {

                echo "
                <td>
                    ₱" . number_format($value, 2) . "
                </td>
                ";

            } 
            else {

                echo "
                <td>
                    " . htmlspecialchars($value ?? "N/A") . "
                </td>
                ";

            }


        }


        echo "</tr>";

    }


}
else {


    echo "
    <tr>
        <td colspan='" . count($columns) . "'>
            No Data Available
        </td>
    </tr>
    ";

}



echo "
</table>
";


unset($_SESSION['report_title']);

unset($_SESSION['report_columns']);

unset($_SESSION['report_data']);

unset($_SESSION['report_ready']);


exit;