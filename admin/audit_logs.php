<?php

session_start();

require_once "../auth/admin_auth.php";
require_once "../config/db.php";

$page_title = "Audit Logs";

/*
 * Audit Logs is an Admin-only visibility page (system-wide activity
 * trail). There is no HR/Employee equivalent and nothing here is
 * user-editable, so — like dashboard.php — this page relies on
 * admin_auth.php's role check rather than a permissions.php toggle.
 */


// ---- Filters -------------------------------------------------

$search   = trim($_GET['search'] ?? "");
$dateFrom = trim($_GET['date_from'] ?? "");
$dateTo   = trim($_GET['date_to'] ?? "");

$perPage = 20;
$page    = max(1, (int) ($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;


$where  = "WHERE 1";
$params = [];

if ($search !== "") {
    $where   .= " AND activity LIKE ?";
    $params[] = "%{$search}%";
}

if ($dateFrom !== "") {
    $where   .= " AND DATE(date_created) >= ?";
    $params[] = $dateFrom;
}

if ($dateTo !== "") {
    $where   .= " AND DATE(date_created) <= ?";
    $params[] = $dateTo;
}


// ---- Summary cards ---------------------------------------------

$totalLogs = $conn->query("
    SELECT COUNT(*)
    FROM audit_logs
")->fetchColumn();


$todayLogs = $conn->query("
    SELECT COUNT(*)
    FROM audit_logs
    WHERE DATE(date_created) = CURDATE()
")->fetchColumn();


$weekLogs = $conn->query("
    SELECT COUNT(*)
    FROM audit_logs
    WHERE date_created >= (CURDATE() - INTERVAL 7 DAY)
")->fetchColumn();


// ---- Count for pagination ---------------------------------------

$countStmt = $conn->prepare("
    SELECT COUNT(*)
    FROM audit_logs
    {$where}
");

$countStmt->execute($params);

$totalFiltered = (int) $countStmt->fetchColumn();
$totalPages    = max(1, (int) ceil($totalFiltered / $perPage));

if ($page > $totalPages) {
    $page   = $totalPages;
    $offset = ($page - 1) * $perPage;
}


// ---- Fetch page of logs -----------------------------------------

$sql = "
    SELECT
        al.activity,
        al.date_created,
        u.full_name AS performed_by
    FROM audit_logs al
    LEFT JOIN users u ON al.user_id = u.user_id
    {$where}
    ORDER BY al.date_created DESC
    LIMIT {$perPage} OFFSET {$offset}
";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Same categorization used on the dashboard activity widget, so an
// entry looks the same whether it's seen there or here.
function auditCategory(string $activityText): array
{
    $activityText = strtolower($activityText);

    if (str_contains($activityText, "position")) {
        return ["fa-briefcase", "primary", "Position Management"];
    }

    if (str_contains($activityText, "department")) {
        return ["fa-building", "primary", "Department Management"];
    }

    if (str_contains($activityText, "employee")) {
        return ["fa-user-group", "primary", "Employee Management"];
    }

    if (str_contains($activityText, "user account")) {
        return ["fa-user-shield", "primary", "User Management"];
    }

    if (str_contains($activityText, "permission")) {
        return ["fa-key", "warning", "Permissions"];
    }

    if (
        str_contains($activityText, "payroll")
        || str_contains($activityText, "allowance")
        || str_contains($activityText, "deduction")
    ) {
        return ["fa-money-bill-wave", "warning", "Payroll Activity"];
    }

    if (str_contains($activityText, "attendance")) {
        return ["fa-calendar-check", "success", "Attendance Activity"];
    }

    if (str_contains($activityText, "leave")) {
        return ["fa-calendar-days", "primary", "Leave Management"];
    }

    if (
        str_contains($activityText, "job")
        || str_contains($activityText, "applicant")
    ) {
        return ["fa-briefcase", "danger", "Recruitment Activity"];
    }

    return ["fa-clock-rotate-left", "warning", "System Activity"];
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

    <style>

        .al-filter-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: flex-end;
            padding: 18px 20px;
            border-bottom: 1px solid #f0f0f0;
        }

        .al-filter-field {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .al-filter-field label {
            font-size: 12px;
            font-weight: 600;
            color: #888;
        }

        .al-filter-field input {
            padding: 8px 12px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
        }

        .al-filter-actions {
            display: flex;
            gap: 8px;
        }

        .al-filter-btn {
            padding: 9px 18px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
        }

        .al-filter-apply {
            background: #4a6cf7;
            color: #fff;
        }

        .al-filter-clear {
            background: #f0f0f0;
            color: #555;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .al-log-row {
            display: flex;
            gap: 14px;
            align-items: flex-start;
            padding: 16px 20px;
            border-bottom: 1px solid #f0f0f0;
        }

        .al-log-row:last-child {
            border-bottom: none;
        }

        .al-log-icon {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: #fff;
        }

        .al-log-icon.primary   { background: #4a6cf7; }
        .al-log-icon.success   { background: #2ecc71; }
        .al-log-icon.warning   { background: #f5a623; }
        .al-log-icon.danger    { background: #e74c3c; }

        .al-log-content strong {
            display: block;
            font-size: 14px;
        }

        .al-log-content p {
            margin: 2px 0 4px;
            font-size: 14px;
            color: #444;
        }

        .al-log-time {
            font-size: 12px;
            color: #888;
        }

        .al-pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            padding: 18px 20px;
        }

        .al-pagination a,
        .al-pagination span {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 14px;
            text-decoration: none;
            color: #444;
        }

        .al-pagination a:hover {
            background: #f0f0f0;
        }

        .al-pagination .current {
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
                        <i class="fa-solid fa-file-lines"></i>
                    </div>

                    <div class="admin-user-summary-content">
                        <span>Total Logs</span>
                        <h2><?= number_format($totalLogs); ?></h2>
                        <p>All recorded system activity</p>
                    </div>

                </div>

                <div class="admin-user-summary-card green">

                    <div class="admin-user-summary-icon">
                        <i class="fa-solid fa-calendar-day"></i>
                    </div>

                    <div class="admin-user-summary-content">
                        <span>Today</span>
                        <h2><?= number_format($todayLogs); ?></h2>
                        <p>Logged so far today</p>
                    </div>

                </div>

                <div class="admin-user-summary-card purple">

                    <div class="admin-user-summary-icon">
                        <i class="fa-solid fa-calendar-week"></i>
                    </div>

                    <div class="admin-user-summary-content">
                        <span>Last 7 Days</span>
                        <h2><?= number_format($weekLogs); ?></h2>
                        <p>Activity in the past week</p>
                    </div>

                </div>

            </div>

            <div class="admin-user-header">

                <div class="admin-user-title">

                    <h1>
                        <i class="fa-solid fa-file-lines"></i>
                        Audit Logs
                    </h1>

                    <p>System-wide activity trail across all modules.</p>

                </div>

            </div>

            <div class="admin-user-table-card">

                <form method="GET" action="audit_logs.php" class="al-filter-bar">

                    <div class="al-filter-field">
                        <label for="alSearch">Search</label>
                        <input
                            type="text"
                            id="alSearch"
                            name="search"
                            placeholder="Search activity..."
                            value="<?= htmlspecialchars($search); ?>"
                        >
                    </div>

                    <div class="al-filter-field">
                        <label for="alFrom">From</label>
                        <input
                            type="date"
                            id="alFrom"
                            name="date_from"
                            value="<?= htmlspecialchars($dateFrom); ?>"
                        >
                    </div>

                    <div class="al-filter-field">
                        <label for="alTo">To</label>
                        <input
                            type="date"
                            id="alTo"
                            name="date_to"
                            value="<?= htmlspecialchars($dateTo); ?>"
                        >
                    </div>

                    <div class="al-filter-actions">

                        <button type="submit" class="al-filter-btn al-filter-apply">
                            <i class="fa-solid fa-filter"></i>
                            Apply
                        </button>

                        <a href="audit_logs.php" class="al-filter-btn al-filter-clear">
                            Clear
                        </a>

                    </div>

                </form>

                <div id="alResults">

                <?php if (empty($logs)): ?>

                    <div class="admin-user-empty">

                        <i class="fa-solid fa-inbox"></i>
                        <h3>No Logs Found</h3>
                        <p>No activity matched your filters.</p>

                    </div>

                <?php else: ?>

                    <div>

                        <?php foreach ($logs as $log): ?>

                            <?php
                                [$icon, $color, $title] = auditCategory($log["activity"]);
                            ?>

                            <div class="al-log-row">

                                <div class="al-log-icon <?= $color; ?>">
                                    <i class="fa-solid <?= $icon; ?>"></i>
                                </div>

                                    <div class="al-log-content">

                                    <strong><?= htmlspecialchars($title); ?></strong>

                                    <p><?= htmlspecialchars($log["activity"]); ?></p>

                                    <div class="al-log-time">
                                        <i class="fa-regular fa-clock"></i>
                                        <?= date("M d, Y • h:i A", strtotime($log["date_created"])); ?>

                                        <span style="margin-left: 10px;">
                                            <i class="fa-regular fa-user"></i>
                                            <?= htmlspecialchars($log["performed_by"] ?? "System"); ?>
                                        </span>
                                    </div>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    </div>

                    <?php if ($totalPages > 1): ?>

                        <div class="al-pagination">

                            <?php
                                $queryBase = [
                                    "search"    => $search,
                                    "date_from" => $dateFrom,
                                    "date_to"   => $dateTo,
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

                <?php endif; ?>

                </div>

            </div>

        </section>

        </main>

    </div>

</div>

<script>

(function () {

    var searchInput = document.getElementById("alSearch");
    var fromInput    = document.getElementById("alFrom");
    var toInput      = document.getElementById("alTo");
    var resultsWrap  = document.getElementById("alResults");
    var filterForm   = document.querySelector(".al-filter-bar");

    if (!resultsWrap) {
        return;
    }

    var debounceTimer = null;

    function loadResults(page) {

        var params = new URLSearchParams({
            search: searchInput.value.trim(),
            date_from: fromInput.value,
            date_to: toInput.value,
            page: page || 1
        });

        fetch("audit_logs_search.php?" + params.toString())
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

    if (fromInput) {
        fromInput.addEventListener("change", debouncedLoad);
    }

    if (toInput) {
        toInput.addEventListener("change", debouncedLoad);
    }

    if (filterForm) {
        filterForm.addEventListener("submit", function (e) {
            e.preventDefault();
            loadResults(1);
        });
    }

    var clearLink = document.querySelector(".al-filter-clear");
    if (clearLink) {
        clearLink.addEventListener("click", function (e) {
            e.preventDefault();
            searchInput.value = "";
            fromInput.value = "";
            toInput.value = "";
            loadResults(1);
        });
    }

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

})();

</script>

<script src="../assets/js/admin.js"></script>

</body>

</html>