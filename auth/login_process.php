<?php

$remember = isset($_POST['remember']);

if ($remember) {
    // 30 days
    session_set_cookie_params(60 * 60 * 24 * 30);
}

session_start();

require_once "../config/db.php";
require_once "../config/csrf.php";

$lockoutThreshold = 5;
$lockoutDurationMinutes = 5;

try {

    $policyStmt = $conn->prepare("
        SELECT setting_name, setting_value
        FROM system_settings
        WHERE setting_name IN ('lockout_threshold', 'lockout_duration_minutes')
    ");

    $policyStmt->execute();

    foreach ($policyStmt->fetchAll(PDO::FETCH_KEY_PAIR) as $name => $value) {

        if (!is_numeric($value) || (int) $value <= 0) {
            continue;
        }

        if ($name === 'lockout_threshold') {
            $lockoutThreshold = (int) $value;
        }

        if ($name === 'lockout_duration_minutes') {
            $lockoutDurationMinutes = (int) $value;
        }

    }

} catch (Exception $e) {
    // Table/setting not available — keep the defaults above.
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../login.php");
    exit();
}

requireCSRFToken("../login.php");

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'];

if (empty($email) || empty($password)) {

    $_SESSION['error'] = "Please fill in all fields.";

    header("Location: ../login.php");
    exit();
}


$lock_stmt = $conn->prepare("
    SELECT
        attempt_count,
        last_attempt,
        locked_until
    FROM login_attempts
    WHERE email = ?
    LIMIT 1
");

$lock_stmt->execute([$email]);

$attempt_data = $lock_stmt->fetch(PDO::FETCH_ASSOC);

if (
    $attempt_data &&
    !empty($attempt_data['locked_until']) &&
    strtotime($attempt_data['locked_until']) > time()
) {

    $remaining_seconds = strtotime($attempt_data['locked_until']) - time();

    $minutes = floor($remaining_seconds / 60);
    $seconds = $remaining_seconds % 60;

    $_SESSION['error'] =
        "Account locked. Please try again after "
        . $minutes
        . " minutes and "
        . $seconds
        . " seconds.";

    header("Location: ../login.php");
    exit();
}


$stmt = $conn->prepare("
    SELECT
        user_id,
        employee_id,
        full_name,
        email,
        password,
        role,
        gender,
        profile_picture,
        status,
        must_change_password
    FROM users
    WHERE email = ?
    LIMIT 1

");

$stmt->execute([$email]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {


    if ($user['status'] !== 'active') {

        $_SESSION['error'] = "Your account is inactive.";

        header("Location: ../login.php");
        exit();
    }


    if (password_verify($password, $user['password'])) {

        session_regenerate_id(true);

        $login_update = $conn->prepare("    
            UPDATE users
            SET last_login = NOW()
            WHERE user_id = ?
        ");

        $login_update->execute([
            $user['user_id']
        ]);

        $reset = $conn->prepare("
            DELETE FROM login_attempts
            WHERE email = ?
        ");

        $reset->execute([$email]);

                $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['employee_id'] = $user['employee_id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];

        $_SESSION['gender'] = $user['gender'];
        $_SESSION['profile_picture'] = $user['profile_picture'];

        if (!empty($user['must_change_password'])) {
            header("Location: ../auth/force_password_change.php");
            exit();
        }


        switch ($user['role']) {

            case "admin":
                header("Location: ../admin/dashboard.php");
                break;

            case "hr":
                header("Location: ../hr/dashboard.php");
                break;

            case "employee":
                header("Location: ../employee/dashboard.php");
                break;

            default:

                session_destroy();

                $_SESSION['error'] = "Invalid account role.";

                header("Location: ../login.php");
                break;
        }

        exit();

    } else {


        $check = $conn->prepare("
            SELECT
                attempt_count,
                last_attempt
            FROM login_attempts
            WHERE email = ?
            LIMIT 1
        ");

        $check->execute([$email]);

        $attempt = $check->fetch(PDO::FETCH_ASSOC);


        if ($attempt) {


            if (strtotime($attempt['last_attempt']) < strtotime("-15 minutes")) {
                $current_attempt = 1;
            } else {
                $current_attempt = $attempt['attempt_count'] + 1;
            }

                        if ($current_attempt >= $lockoutThreshold) {

                $locked_until = date(
                    "Y-m-d H:i:s",
                    strtotime("+{$lockoutDurationMinutes} minutes")
                );

                $update = $conn->prepare("
                    UPDATE login_attempts
                    SET
                        attempt_count = ?,
                        last_attempt = NOW(),
                        locked_until = ?
                    WHERE email = ?
                ");

                $update->execute([
                    $current_attempt,
                    $locked_until,
                    $email
                ]);

                $_SESSION['error'] =
                    "Too many failed attempts. Your account has been locked for {$lockoutDurationMinutes} minutes.";

                        } else {

                $update = $conn->prepare("
                    UPDATE login_attempts
                    SET
                        attempt_count = ?,
                        last_attempt = NOW(),
                        locked_until = NULL
                    WHERE email = ?
                ");

                $update->execute([
                    $current_attempt,
                    $email
                ]);

                $remaining = $lockoutThreshold - $current_attempt;

                $_SESSION['error'] =
                "Invalid email or password. "
                . "Attempt {$current_attempt}/{$lockoutThreshold}. "
                . $remaining
                . " attempts remaining before lock.";
        }

        } else {

            $insert = $conn->prepare("
                INSERT INTO login_attempts (
                    email,
                    attempt_count,
                    last_attempt
                )
                VALUES (
                    ?,
                    1,
                    NOW()
                )
            ");

            $insert->execute([$email]);

            $_SESSION['error'] =
                "Invalid email or password. "
                . "Attempt 1/5. "
                . "4 attempts remaining before lock.";
        }

        header("Location: ../login.php");
        exit();
    }

} else {

    $check = $conn->prepare("
        SELECT
            attempt_count,
            last_attempt
        FROM login_attempts
        WHERE email = ?
        LIMIT 1
    ");

    $check->execute([$email]);

    $attempt = $check->fetch(PDO::FETCH_ASSOC);

    if ($attempt) {

        if (strtotime($attempt['last_attempt']) < strtotime("-15 minutes")) {
            $current_attempt = 1;
        } else {
            $current_attempt = $attempt['attempt_count'] + 1;
        }

        $update = $conn->prepare("
            UPDATE login_attempts
            SET
                attempt_count = ?,
                last_attempt = NOW()
            WHERE email = ?
        ");

        $update->execute([
            $current_attempt,
            $email
        ]);

    } else {

        $insert = $conn->prepare("
            INSERT INTO login_attempts (
                email,
                attempt_count,
                last_attempt
            )
            VALUES (
                ?,
                1,
                NOW()
            )
        ");

        $insert->execute([$email]);

    }

    $_SESSION['error'] = "Invalid email or password.";

    header("Location: ../login.php");
    exit();
}

?>