<?php

/**
 * HR Audit Logger
 * 
 * Helper functions to log HR activities to the audit_logs table.
 * Used across all HR management modules (Users, Employees, Departments, Positions, etc.)
 */

/**
 * Log an HR activity to the audit_logs table
 * 
 * @param PDO $conn Database connection
 * @param string $activity Description of the activity
 * @return bool Success status
 */
function logHRActivity($conn, $activity)
{
    try {
        $performedBy = $_SESSION['user_id'] ?? null;

        $stmt = $conn->prepare("
            INSERT INTO audit_logs (user_id, activity, date_created)
            VALUES (?, ?, NOW())
        ");
        
        return $stmt->execute([$performedBy, $activity]);
    } catch (PDOException $e) {
        error_log("HR Audit Logging Error: " . $e->getMessage());
        return false;
    }
}

// =====================================================
// USER MANAGEMENT FUNCTIONS
// =====================================================

function logUserCreation($conn, $userName, $userEmail, $userRole)
{
    $activity = "Added new user account for {$userName} ({$userEmail}) with role: {$userRole}";
    return logHRActivity($conn, $activity);
}

function logUserEdit($conn, $userName, $userEmail, $changes = [])
{
    $changesList = !empty($changes) ? ": " . implode("; ", $changes) : "";
    $activity = "Updated user account for {$userName} ({$userEmail}){$changesList}";
    return logHRActivity($conn, $activity);
}

function logUserStatusChange($conn, $userName, $userEmail, $fromStatus, $toStatus)
{
    $activity = "Changed user account status for {$userName} ({$userEmail}) from {$fromStatus} to {$toStatus}";
    return logHRActivity($conn, $activity);
}

function logUserDeletion($conn, $userName, $userEmail)
{
    $activity = "Deleted user account for {$userName} ({$userEmail})";
    return logHRActivity($conn, $activity);
}

// =====================================================
// EMPLOYEE MANAGEMENT FUNCTIONS
// =====================================================

/**
 * Log employee creation
 */
function logEmployeeCreation($conn, $fullName, $email, $employeeCode)
{
    $activity = "Added new employee {$fullName} ({$email}) - Code: {$employeeCode}";
    return logHRActivity($conn, $activity);
}

/**
 * Log employee edit/update
 */
function logEmployeeEdit($conn, $fullName, $email, $changes = [])
{
    $changesList = !empty($changes) ? ": " . implode("; ", $changes) : "";
    $activity = "Updated employee {$fullName} ({$email}){$changesList}";
    return logHRActivity($conn, $activity);
}

/**
 * Log employee status change
 */
function logEmployeeStatusChange($conn, $fullName, $email, $fromStatus, $toStatus)
{
    $activity = "Changed employee status for {$fullName} ({$email}) from {$fromStatus} to {$toStatus}";
    return logHRActivity($conn, $activity);
}

/**
 * Log employee deletion
 */
function logEmployeeDeletion($conn, $fullName, $email)
{
    $activity = "Deleted employee {$fullName} ({$email})";
    return logHRActivity($conn, $activity);
}

// =====================================================
// DEPARTMENT MANAGEMENT FUNCTIONS
// =====================================================

/**
 * Log department creation
 */
function logDepartmentCreation($conn, $departmentName, $description = "")
{
    $descInfo = !empty($description) ? " - Description: " . substr($description, 0, 50) : "";
    $activity = "Created new department '{$departmentName}'{$descInfo}";
    return logHRActivity($conn, $activity);
}

/**
 * Log department edit/update
 */
function logDepartmentEdit($conn, $departmentName, $changes = [])
{
    $changesList = !empty($changes) ? ": " . implode("; ", $changes) : "";
    $activity = "Updated department '{$departmentName}'{$changesList}";
    return logHRActivity($conn, $activity);
}

/**
 * Log department status change
 */
function logDepartmentStatusChange($conn, $departmentName, $fromStatus, $toStatus)
{
    $activity = "Changed department status for '{$departmentName}' from {$fromStatus} to {$toStatus}";
    return logHRActivity($conn, $activity);
}

/**
 * Log department deletion
 */
function logDepartmentDeletion($conn, $departmentName)
{
    $activity = "Deleted department '{$departmentName}'";
    return logHRActivity($conn, $activity);
}

// =====================================================
// POSITION MANAGEMENT FUNCTIONS
// =====================================================

/**
 * Log position creation
 */
function logPositionCreation($conn, $positionName, $departmentName, $description = "")
{
    $descInfo = !empty($description) ? " - Description: " . substr($description, 0, 50) : "";
    $activity = "Created new position '{$positionName}' in {$departmentName}{$descInfo}";
    return logHRActivity($conn, $activity);
}

/**
 * Log position edit/update
 */
function logPositionEdit($conn, $positionName, $changes = [])
{
    $changesList = !empty($changes) ? ": " . implode("; ", $changes) : "";
    $activity = "Updated position '{$positionName}'{$changesList}";
    return logHRActivity($conn, $activity);
}

/**
 * Log position status change
 */
function logPositionStatusChange($conn, $positionName, $fromStatus, $toStatus)
{
    $activity = "Changed position status for '{$positionName}' from {$fromStatus} to {$toStatus}";
    return logHRActivity($conn, $activity);
}

/**
 * Log position deletion
 */
function logPositionDeletion($conn, $positionName)
{
    $activity = "Deleted position '{$positionName}'";
    return logHRActivity($conn, $activity);
}

// =====================================================
// PERMISSION MANAGEMENT FUNCTIONS
// =====================================================

function logPermissionChange($conn, $role, $changes = [])
{
    $changesList = !empty($changes) ? ": " . implode("; ", $changes) : "";
    $activity = "Updated permissions for role '{$role}'{$changesList}";
    return logHRActivity($conn, $activity);
}

// =====================================================
// PAYROLL FUNCTIONS
// =====================================================

function logAllowanceChange($conn, $action, $employeeName, $type, $amount)
{
    $actionText = ucfirst($action);
    $activity = "{$actionText} {$type} allowance for {$employeeName} - Amount: {$amount}";
    return logHRActivity($conn, $activity);
}

// =====================================================
// ATTENDANCE FUNCTIONS
// =====================================================

function logAttendanceChange($conn, $action, $employeeName, $date, $status)
{
    $actionText = ucfirst($action);
    $activity = "{$actionText} attendance for {$employeeName} on {$date} - Status: {$status}";
    return logHRActivity($conn, $activity);
}

// =====================================================
// LEAVE FUNCTIONS
// =====================================================

function logLeaveChange($conn, $action, $employeeName, $leaveType, $dates)
{
    $actionText = ucfirst($action);
    $activity = "{$actionText} {$leaveType} leave request for {$employeeName} - Dates: {$dates}";
    return logHRActivity($conn, $activity);
}

// =====================================================
// BULK OPERATIONS
// =====================================================

function logBulkOperation($conn, $operation, $recordCount, $details = [])
{
    $detailsList = !empty($details) ? " - " . implode("; ", $details) : "";
    $activity = "{$operation} affecting {$recordCount} record(s){$detailsList}";
    return logHRActivity($conn, $activity);
}

?>