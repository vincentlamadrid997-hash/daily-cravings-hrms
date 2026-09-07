<?php

session_start();

require_once "../auth/admin_auth.php";
require_once "../config/db.php";
require_once "../config/csrf.php";

$page_title = "Login Attempts";

/*
 * Like Audit Logs, this is an Admin-only visibility/security page —
 * no HR/Employee equivalent, so it relies on admin_auth.php's role
 * check rather than a permissions.php toggle (same pattern as
 * dashboard.php and audit_logs.php).
 */


// ---- Unlock action ------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unlock_email'])) {

    requireCSRFToken("login_attempts.php");

    $unlockEmail = trim($_POST['unlock_email']);

    $stmt = $conn->prepare("
        UPDATE login_attempts
        SET attempt_count = 0,
            locked_until  = NULL
        WHERE email = ?
    ");

    $stmt->execute([$unlockEmail]);

    $_SESSION['la_success'] = "Login attempts reset for {$unlockEmail}.";

    header("Location: login_attempts.php");
    exit();

}

$success = $_SESSION['la_success'] ?? "";
unset($_SESSION['la_success']);


// ---- Filters --------------------------------------------------

$search = trim($_GET['search'] ?? "");
$status = trim($_GET['status'] ?? ""); // '', 'locked', 'unlocked'

$perPage = 20;
$page    = max(1, (int) ($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

$where  = "WHERE 1";
$params = [];

if ($search !== "") {
    $where   .= " AND email LIKE ?";
    $params[] = "%{$search}%";
}

if ($status === "locked") {
    $where .= " AND locked_until IS NOT NULL AND locked_until > NOW()";
} elseif ($status === "unlocked") {
    $where .= " AND (locked_until IS NULL OR locked_until <= NOW())";
}


// ---- Summary cards ----------------------------------------------

$totalAttempts = $conn->query("
    SELECT COUNT(*)
    FROM login_attempts
")->fetchColumn();


$todayAttempts = $conn->query("
    SELECT COUNT(*)
    FROM login_attempts
    WHERE DATE(last_attempt) = CURDATE()
")->fetchColumn();


$currentlyLocked = $conn->query("
    SELECT COUNT(*)
    FROM login_attempts
    WHERE locked_until IS NOT NULL
    AND locked_until > NOW()
")->fetchColumn();


// ---- Count for pagination -----------------------------------------

$countStmt = $conn->prepare("
    SELECT COUNT(*)
    FROM login_attempts
    {$where}
");

$countStmt->execute($params);

$totalFiltered = (int) $countStmt->fetchColumn();
$totalPages    = max(1, (int) ceil($totalFiltered / $perPage));

if ($page > $totalPages) {
    $page   = $totalPages;
    $offset = ($page - 1) * $perPage;
}


// ---- Fetch page -----------------------------------------------

$sql = "
    SELECT
        email,
        attempt_count,
        last_attempt,
        locked_until
    FROM login_attempts
    {$where}
    ORDER BY last_attempt DESC
    LIMIT {$perPage} OFFSET {$offset}
";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

        .la-filter-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: flex-end;
            padding: 18px 20px;
            border-bottom: 1px solid #f0f0f0;
        }

        .la-filter-field {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .la-filter-field label {
            font-size: 12px;
            font-weight: 600;
            color: #888;
        }

        .la-filter-field input,
        .la-filter-field select {
            padding: 8px 12px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
        }

        .la-filter-actions {
            display: flex;
            gap: 8px;
        }

        .la-filter-btn {
            padding: 9px 18px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
        }

        .la-filter-apply {
            background: #4a6cf7;
            color: #fff;
        }

        .la-filter-clear {
            background: #f0f0f0;
            color: #555;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .la-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .la-status-badge.locked {
            background: #fde8e8;
            color: #e74c3c;
        }

        .la-status-badge.unlocked {
            background: #eafaf1;
            color: #2ecc71;
        }

        .la-unlock-btn {
            padding: 7px 14px;
            border-radius: 8px;
            border: none;
            background: #4a6cf7;
            color: #fff;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .la-pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            padding: 18px 20px;
        }

        .la-pagination a,
        .la-pagination span {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 14px;
            text-decoration: none;
            color: #444;
        }

        .la-pagination a:hover {
            background: #f0f0f0;
        }

        .la-pagination .current {
            background: #4a6cf7;
            color: #fff;
            font-weight: 600;
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

            <div class="admin-user-summary-grid">

                <div class="admin-user-summary-card blue">

                    <div class="admin-user-summary-icon">
                        <i class="fa-solid fa-right-to-bracket"></i>
                    </div>

                    <div class="admin-user-summary-content">
                        <span>Total Records</span>
                        <h2><?= number_format($totalAttempts); ?></h2>
                        <p>All tracked login attempts</p>
                    </div>

                </div>

                <div class="admin-user-summary-card purple">

                    <div class="admin-user-summary-icon">
                        <i class="fa-solid fa-calendar-day"></i>
                    </div>

                    <div class="admin-user-summary-content">
                        <span>Today</span>
                        <h2><?= number_format($todayAttempts); ?></h2>
                        <p>Attempts logged today</p>
                    </div>

                </div>

                <div class="admin-user-summary-card red">

                    <div class="admin-user-summary-icon">
                        <i class="fa-solid fa-lock"></i>
                    </div>

                    <div class="admin-user-summary-content">
                        <span>Currently Locked</span>
                        <h2><?= number_format($currentlyLocked); ?></h2>
                        <p>Accounts locked out right now</p>
                    </div>

                </div>

            </div>

            <div class="admin-user-header">

                <div class="admin-user-title">

                    <h1>
                        <i class="fa-solid fa-lock"></i>
                        Login Attempts
                    </h1>

                    <p>Monitor failed logins and manage account lockouts.</p>

                </div>

            </div>

            <div class="admin-user-table-card">

                <form method="GET" action="login_attempts.php" class="la-filter-bar">

                    <div class="la-filter-field">
                        <label for="laSearch">Search</label>
                        <input
                            type="text"
                            id="laSearch"
                            name="search"
                            placeholder="Search by email..."
                            value="<?= htmlspecialchars($search); ?>"
                        >
                    </div>

                    <div class="la-filter-field">
                        <label for="laStatus">Status</label>
                        <select id="laStatus" name="status">
                            <option value="" <?= $status === "" ? "selected" : ""; ?>>All</option>
                            <option value="locked" <?= $status === "locked" ? "selected" : ""; ?>>Locked</option>
                            <option value="unlocked" <?= $status === "unlocked" ? "selected" : ""; ?>>Unlocked</option>
                        </select>
                    </div>

                    <div class="la-filter-actions">

                        <button type="submit" class="la-filter-btn la-filter-apply">
                            <i class="fa-solid fa-filter"></i>
                            Apply
                        </button>

                        <a href="login_attempts.php" class="la-filter-btn la-filter-clear">
                            Clear
                        </a>

                    </div>

                </form>

                <div id="laResults">

                <div class="admin-user-table-wrapper">

                    <table class="admin-user-table">

                        <thead>

                            <tr>
                                <th>Email</th>
                                <th>Attempts</th>
                                <th>Last Attempt</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>

                        </thead>

                        <tbody>

                            <?php if (empty($attempts)): ?>

                                <tr>
                                    <td colspan="5">

                                        <div class="admin-user-empty">
                                            <i class="fa-solid fa-magnifying-glass"></i>
                                            <h3>No Records Found</h3>
                                            <p>No login attempts matched your filters.</p>
                                        </div>

                                    </td>
                                </tr>

                            <?php else: ?>

                                <?php foreach ($attempts as $attempt): ?>

                                    <?php
                                        $isLocked =
                                            !empty($attempt["locked_until"])
                                            && strtotime($attempt["locked_until"]) > time();
                                    ?>

                                    <tr>

                                        <td><?= htmlspecialchars($attempt["email"]); ?></td>

                                        <td><?= (int) $attempt["attempt_count"]; ?></td>

                                        <td>
                                            <?= date("M d, Y h:i A", strtotime($attempt["last_attempt"])); ?>
                                        </td>

                                        <td>

                                            <?php if ($isLocked): ?>

                                                <span class="la-status-badge locked">
                                                    <i class="fa-solid fa-lock"></i>
                                                    Locked until
                                                    <?= date("h:i A", strtotime($attempt["locked_until"])); ?>
                                                </span>

                                            <?php else: ?>

                                                <span class="la-status-badge unlocked">
                                                    <i class="fa-solid fa-lock-open"></i>
                                                    Unlocked
                                                </span>

                                            <?php endif; ?>

                                        </td>

                                        <td>

                                            <?php if ($isLocked || $attempt["attempt_count"] > 0): ?>

                                                <form method="POST" action="login_attempts.php" class="la-unlock-form">
                                                    <?php csrfField(); ?>
                                                    <input
                                                        type="hidden"
                                                        name="unlock_email"
                                                        value="<?= htmlspecialchars($attempt["email"]); ?>"
                                                    >

                                                    <button type="submit" class="la-unlock-btn">
                                                        Reset
                                                    </button>

                                                </form>

                                            <?php else: ?>

                                                &mdash;

                                            <?php endif; ?>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

                <?php if ($totalPages > 1): ?>

                    <div class="la-pagination">

                        <?php
                            $queryBase = [
                                "search" => $search,
                                "status" => $status,
                            ];
                        ?>

                        <?php if ($page > 1): ?>
                            <a href="?<?= http_build_query($queryBase + ["page" => $page - 1]); ?>">
                                <i class="fa-solid fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>

                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>

                            <?php if ($i === $page): ?>
                                <span class="current"><?= $i; ?></span>
                            <?php else: ?>
                                <a href="?<?= http_build_query($queryBase + ["page" => $i]); ?>"><?= $i; ?></a>
                            <?php endif; ?>

                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <a href="?<?= http_build_query($queryBase + ["page" => $page + 1]); ?>">
                                <i class="fa-solid fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>

                    </div>

                <?php endif; ?>

                </div>

            </div>

        </section>

        </main>

    </div>

</div>

<div
    id="userAlertData"
    data-success="<?= htmlspecialchars($success); ?>"
    data-error=""
></div>

<script>

    (function () {

        var resultsWrap = document.getElementById("laResults");

        // Delegated so it still works after AJAX swaps the table's HTML
        if (resultsWrap) {
            resultsWrap.addEventListener("submit", function (event) {

                var form = event.target.closest(".la-unlock-form");
                if (!form) {
                    return;
                }

                event.preventDefault();

                var email = form.querySelector("[name='unlock_email']").value;

                Swal.fire({
                    title: "Reset login attempts?",
                    text: "This clears the attempt count and unlocks " + email + " immediately.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, reset",
                    cancelButtonText: "Cancel",
                    confirmButtonColor: "#4a6cf7",
                    reverseButtons: true
                }).then(function (result) {

                    if (result.isConfirmed) {
                        form.submit();
                    }

                });

            });
        }

        var searchInput = document.getElementById("laSearch");
        var statusSelect = document.getElementById("laStatus");
        var filterForm   = document.querySelector(".la-filter-bar");
        var debounceTimer = null;

        function loadResults(page) {

            var params = new URLSearchParams({
                search: searchInput.value.trim(),
                status: statusSelect.value,
                page: page || 1
            });

            fetch("login_attempts_search.php?" + params.toString())
                .then(function (res) { return res.text(); })
                .then(function (html) {
                    resultsWrap.innerHTML = html;
                });

        }

        function debouncedLoad() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                loadResults(1);
            }, 300);
        }

        if (searchInput) {
            searchInput.addEventListener("input", debouncedLoad);
        }

        if (statusSelect) {
            statusSelect.addEventListener("change", debouncedLoad);
        }

        if (filterForm) {
            filterForm.addEventListener("submit", function (e) {
                e.preventDefault();
                loadResults(1);
            });
        }

        var clearLink = document.querySelector(".la-filter-clear");
        if (clearLink) {
            clearLink.addEventListener("click", function (e) {
                e.preventDefault();
                searchInput.value = "";
                statusSelect.value = "";
                loadResults(1);
            });
        }

        if (resultsWrap) {
            resultsWrap.addEventListener("click", function (e) {
                var link = e.target.closest("a");
                if (!link) {
                    return;
                }
                e.preventDefault();
                var url = new URL(link.href);
                var page = url.searchParams.get("page") || 1;
                loadResults(page);
            });
        }

    })();

</script>

<script src="../assets/js/admin.js"></script>

</body>

</html>