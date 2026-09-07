<?php

session_start();

require_once "../auth/admin_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";

$page_title = "System Settings";

/*
 * Admin-only, same pattern as dashboard.php / audit_logs.php /
 * login_attempts.php — no HR/Employee equivalent, so no
 * permissions.php toggle.
 *
 * Backed by a key/value `system_settings` table (see
 * system_settings.sql) rather than fixed columns, so new settings
 * can be added later without an ALTER TABLE.
 */


// Field definitions: key => [label, type, group]
$settingFields = [

    'company_name'   => ['Company Name',        'text',  'Company Info'],
    'company_email'  => ['Company Email',        'email', 'Company Info'],
    'company_phone'  => ['Company Phone',        'text',  'Company Info'],
    'company_address'=> ['Company Address',      'text',  'Company Info'],

    'timezone'                 => ['Timezone',                       'text',   'General'],
    'date_format'               => ['Date Format (PHP date() syntax)', 'text', 'General'],

    'lockout_threshold'         => ['Failed Attempts Before Lockout', 'number', 'Security Policy'],
    'lockout_duration_minutes'  => ['Lockout Duration (minutes)',     'number', 'Security Policy'],
    'session_timeout_minutes'   => ['Session Timeout (minutes)',      'number', 'Security Policy'],

];


// ---- Handle Save --------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    requireCSRFToken("system_settings.php");

    try {

        $conn->beginTransaction();

        $stmt = $conn->prepare("
            INSERT INTO system_settings (setting_name, setting_value)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");

        foreach ($settingFields as $key => $meta) {

            $value = trim($_POST[$key] ?? '');
            $stmt->execute([$key, $value]);

        }

        $conn->commit();

        $_SESSION['ss_success'] = "System settings updated.";

    } catch (Exception $e) {

        $conn->rollBack();
        $_SESSION['ss_error'] = "Failed to update settings. Please try again.";

    }

    header("Location: system_settings.php");
    exit();

}


$success = $_SESSION['ss_success'] ?? "";
$error   = $_SESSION['ss_error'] ?? "";

unset($_SESSION['ss_success']);
unset($_SESSION['ss_error']);


// ---- Load current values -------------------------------------------

$rows = $conn->query("
    SELECT setting_name, setting_value
    FROM system_settings
")->fetchAll(PDO::FETCH_KEY_PAIR);


// Group fields for rendering
$grouped = [];

foreach ($settingFields as $key => [$label, $type, $group]) {

    $grouped[$group][$key] = [
        'label' => $label,
        'type'  => $type,
        'value' => $rows[$key] ?? '',
    ];

}

?>

<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($page_title); ?> | Daily Cravings Foods Inc.</title>

    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>

        .ss-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 18px rgba(0,0,0,.06);
            margin-bottom: 22px;
            overflow: hidden;
        }

        .ss-card-header {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 20px 24px;
            background: #F5F8FF;
            border-bottom: 1px solid #E6ECF5;
        }

        .ss-card-icon {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #003DA5;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .ss-card-header h2 {
            margin: 0;
            font-size: 16px;
            color: #003DA5;
            font-weight: 700;
        }

        .ss-card-header p {
            margin: 4px 0 0;
            font-size: 13px;
            color: #78909C;
        }

        .ss-field-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            padding: 22px 24px;
        }

        .ss-field-group label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 600;
            color: #607D8B;
            margin-bottom: 8px;
        }

        .ss-field-group input {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid #E0E4EA;
            border-radius: 10px;
            font-size: 14px;
            transition: .2s;
        }

        .ss-field-group input:focus {
            outline: none;
            border-color: #003DA5;
            box-shadow: 0 0 0 4px rgba(0,61,165,.10);
        }

        .ss-save-bar {
            display: flex;
            justify-content: flex-end;
            padding: 4px 4px 0;
        }

        .ss-note {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            background: #E6F4EA;
            color: #2E7D32;
            border-left: 4px solid #2E7D32;
            padding: 14px 18px;
            border-radius: 10px;
            font-size: 13px;
            margin: 0 0 20px;
        }

    </style>

</head>

<body>

<div class="admin-wrapper">

    <?php require_once "includes/sidebar.php"; ?>

    <div class="admin-content-wrapper">

        <?php require_once "includes/header.php"; ?>

        <main class="admin-main">

        <section class="admin-user-page">

            <div class="admin-user-header">

                <div class="admin-user-title">

                    <h1>
                        <i class="fa-solid fa-gears"></i>
                        System Settings
                    </h1>

                <p class="ss-note">
                <i class="fa-solid fa-circle-check"></i>
                All settings below are live — Company Info, General, and Security Policy
                values are read directly by the system, including login lockout and session timeout.
            </p>

            <?php
                $groupIcons = [
                    'Company Info'    => 'fa-building',
                    'General'         => 'fa-sliders',
                    'Security Policy' => 'fa-shield-halved',
                ];
            ?>

                <form method="POST" action="system_settings.php" id="ssForm">
                    <?php csrfField(); ?>

                <?php foreach ($grouped as $groupName => $fields): ?>

                    <div class="ss-card">

                        <div class="ss-card-header">

                            <div class="ss-card-icon">
                                <i class="fa-solid <?= $groupIcons[$groupName] ?? 'fa-gear'; ?>"></i>
                            </div>

                            <div>
                                <h2><?= htmlspecialchars($groupName); ?></h2>
                                <p>Update the <?= htmlspecialchars(strtolower($groupName)); ?> values below.</p>
                            </div>

                        </div>

                        <div class="ss-field-grid">

                            <?php foreach ($fields as $key => $field): ?>

                                <div class="ss-field-group">

                                    <label>
                                        <i class="fa-solid fa-pen"></i>
                                        <?= htmlspecialchars($field['label']); ?>
                                    </label>

                                    <input
                                        type="<?= htmlspecialchars($field['type']); ?>"
                                        name="<?= htmlspecialchars($key); ?>"
                                        class="ss-field-input"
                                        data-initial="<?= htmlspecialchars($field['value']); ?>"
                                        value="<?= htmlspecialchars($field['value']); ?>"
                                        <?= $field['type'] === 'number' ? 'min="0"' : ''; ?>
                                    >

                                </div>

                            <?php endforeach; ?>

                        </div>

                    </div>

                <?php endforeach; ?>

                <div class="ss-save-bar">

                    <button type="submit" class="admin-user-add-btn" id="ssSaveBtn" disabled>
                        <i class="fa-solid fa-floppy-disk"></i>
                        Save Settings
                    </button>

                </div>

            </form>

                    </div>

                </form>

            </div>

        </section>

        </main>

    </div>

</div>

<div
    id="userAlertData"
    data-success="<?= htmlspecialchars($success); ?>"
    data-error="<?= htmlspecialchars($error); ?>"
></div>

<script>

    (function () {

        var form    = document.getElementById("ssForm");
        var saveBtn = document.getElementById("ssSaveBtn");
        var inputs  = form
            ? form.querySelectorAll(".ss-field-input")
            : [];

        if (!form || !saveBtn || inputs.length === 0) {
            return;
        }

        function hasChanges() {

            for (var i = 0; i < inputs.length; i++) {

                var input = inputs[i];

                if (input.value !== input.getAttribute("data-initial")) {
                    return true;
                }

            }

            return false;

        }

        function refreshSaveState() {
            saveBtn.disabled = !hasChanges();
        }

        inputs.forEach(function (input) {
            input.addEventListener("input", refreshSaveState);
        });

        var isConfirmed = false;

        form.addEventListener("submit", function (event) {

            if (!hasChanges()) {
                event.preventDefault();
                return;
            }

            if (isConfirmed) {
                return;
            }

            event.preventDefault();

            Swal.fire({
                title: "Save system settings?",
                text: "This will update the values used across the system.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, save changes",
                cancelButtonText: "Cancel",
                confirmButtonColor: "#4a6cf7",
                reverseButtons: true
            }).then(function (result) {

                if (result.isConfirmed) {
                    isConfirmed = true;
                    form.submit();
                }

            });

        });

    })();

</script>

<script src="../assets/js/admin.js"></script>

</body>

</html>