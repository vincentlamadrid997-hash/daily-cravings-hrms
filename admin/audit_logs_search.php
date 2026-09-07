<?php

session_start();

require_once "../auth/admin_auth.php";
require_once "../config/db.php";

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

$sql = "
    SELECT
        activity,
        date_created
    FROM audit_logs
    {$where}
    ORDER BY date_created DESC
    LIMIT {$perPage} OFFSET {$offset}
";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

function auditCategory(string $activityText): array
{
    $activityText = strtolower($activityText);

    if (str_contains($activityText, "employee")) {
        return ["fa-user-group", "primary", "Employee Management"];
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
<?php if (empty($logs)): ?>

    <div class="admin-user-empty">
        <i class="fa-solid fa-inbox"></i>
        <h3>No Logs Found</h3>
        <p>No activity matched your filters.</p>
    </div>

<?php else: ?>

    <div>

        <?php foreach ($logs as $log): ?>

            <?php [$icon, $color, $title] = auditCategory($log["activity"]); ?>

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