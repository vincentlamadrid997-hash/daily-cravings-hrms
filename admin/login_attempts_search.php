<?php

session_start();

require_once "../auth/admin_auth.php";
require_once "../config/db.php";

$search = trim($_GET['search'] ?? "");
$status = trim($_GET['status'] ?? "");

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