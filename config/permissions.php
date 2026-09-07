<?php

/**
 * Roles & Permissions helper functions.
 *
 * These functions read/write the `permissions` and `role_permissions`
 * tables only. They do NOT touch `users` or `users.role`.
 *
 * Requires $conn (PDO) from config/db.php to already be available.
 */


/**
 * Check whether a given role has a given permission.
 *
 * Admin is a hardcoded superuser and always has every permission,
 * regardless of what (if anything) is stored in role_permissions
 * for the "admin" role. Only HR and Employee are actually gated
 * by the role_permissions table.
 */
function userHasPermission(PDO $conn, string $role, string $permissionKey): bool
{
    if ($role === 'admin') {
        return true;
    }

    static $cache = [];

    $cacheKey = $role . "|" . $permissionKey;

    if (isset($cache[$cacheKey])) {
        return $cache[$cacheKey];
    }

    $stmt = $conn->prepare("
        SELECT COUNT(*)
        FROM role_permissions rp
        INNER JOIN permissions p
            ON p.permission_id = rp.permission_id
        WHERE rp.role = ?
          AND p.permission_key = ?
    ");

    $stmt->execute([$role, $permissionKey]);

    $result = (bool) $stmt->fetchColumn();

    $cache[$cacheKey] = $result;

    return $result;
}


/**
 * Enforce a permission for the currently logged-in user's session role.
 * If the permission is missing, stop execution and redirect away with
 * an error message.
 */
function requirePermission(PDO $conn, string $permissionKey, string $redirectTo = "../admin/dashboard.php"): void
{
    $role = $_SESSION['role'] ?? "";

    if ($role === "" || !userHasPermission($conn, $role, $permissionKey)) {

        $_SESSION['user_error'] = "You do not have permission to perform this action.";

        header("Location: " . $redirectTo);
        exit();

    }
}


/**
 * Return the full permission catalog.
 */
function getAllPermissions(PDO $conn): array
{
    return $conn->query("
        SELECT permission_id, permission_key, permission_label
        FROM permissions
        ORDER BY permission_id
    ")->fetchAll(PDO::FETCH_ASSOC);
}


/**
 * Return a [role => [permission_key => true]] map of every
 * currently saved role/permission assignment.
 *
 * Admin is intentionally excluded: it's a hardcoded superuser
 * (see userHasPermission()) and is not configurable via
 * role_permissions.
 */
function getRolePermissionMap(PDO $conn): array
{
    $map = [
        'hr'       => [],
        'employee' => [],
    ];

    $stmt = $conn->query("
        SELECT rp.role, p.permission_key
        FROM role_permissions rp
        INNER JOIN permissions p
            ON p.permission_id = rp.permission_id
    ");

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        if (isset($map[$row['role']])) {
            $map[$row['role']][$row['permission_key']] = true;
        }
    }

    return $map;
}

?>