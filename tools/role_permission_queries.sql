-- ============================================================================
-- Role & Permission Management - Quick Reference Queries
-- Use these queries to audit, manage, and troubleshoot permissions
-- ============================================================================

-- ═══════════════════════════════════════════════════════════
-- 1. AUDIT QUERIES - View Current Permission State
-- ═══════════════════════════════════════════════════════════

-- See all roles with their permission counts
SELECT 
    r.id,
    r.name as role_name,
    r.description,
    COUNT(rp.permission_id) as permission_count
FROM roles r
LEFT JOIN role_permissions rp ON r.id = rp.role_id
GROUP BY r.id, r.name, r.description
ORDER BY r.id;

-- List all permissions for a specific role (change role_id as needed)
SELECT 
    r.name as role_name,
    p.id as permission_id,
    p.name as permission_name,
    p.description
FROM role_permissions rp
JOIN roles r ON rp.role_id = r.id
JOIN permissions p ON rp.permission_id = p.id
WHERE rp.role_id = 3  -- Finance Officer
ORDER BY p.name;

-- See users by role and their permission counts
SELECT 
    u.user_id,
    u.full_name,
    r.name as role_name,
    COUNT(DISTINCT rp.permission_id) as role_permissions,
    COUNT(DISTINCT up.permission_id) as user_overrides
FROM users u
LEFT JOIN roles r ON u.role_id = r.id
LEFT JOIN role_permissions rp ON r.id = rp.role_id
LEFT JOIN user_permissions up ON u.user_id = up.user_id
GROUP BY u.user_id, u.full_name, r.name
ORDER BY u.full_name;

-- Check a specific user's effective permissions (role + overrides)
SELECT 
    u.full_name,
    r.name as role_name,
    GROUP_CONCAT(DISTINCT p.name ORDER BY p.name SEPARATOR ', ') as effective_permissions
FROM users u
LEFT JOIN roles r ON u.role_id = r.id
LEFT JOIN role_permissions rp ON r.id = rp.role_id
LEFT JOIN permissions p ON rp.permission_id = p.id
WHERE u.user_id = 5  -- Change to desired user
GROUP BY u.user_id, u.full_name, r.name;

-- Find users missing required permission (example: approve_request)
SELECT 
    u.user_id,
    u.full_name,
    r.name as role_name,
    'Missing: approve_request' as issue
FROM users u
LEFT JOIN roles r ON u.role_id = r.id
WHERE u.is_active = 1
  AND NOT EXISTS (
      SELECT 1 FROM role_permissions rp
      JOIN permissions p ON rp.permission_id = p.id
      WHERE rp.role_id = u.role_id
        AND p.name = 'approve_request'
  )
ORDER BY u.full_name;

-- ═══════════════════════════════════════════════════════════
-- 2. COMPARISON QUERIES - See Permission Differences
-- ═══════════════════════════════════════════════════════════

-- Compare permissions between two roles
SELECT 
    COALESCE(p1.name, p2.name) as permission_name,
    CASE WHEN p1.name IS NOT NULL THEN 'Yes' ELSE 'No' END as 'In Role 3 (Finance)',
    CASE WHEN p2.name IS NOT NULL THEN 'Yes' ELSE 'No' END as 'In Role 4 (HOD)'
FROM (
    SELECT DISTINCT p.id, p.name
    FROM role_permissions rp
    JOIN permissions p ON rp.permission_id = p.id
    WHERE rp.role_id = 3
) p1
FULL OUTER JOIN (
    SELECT DISTINCT p.id, p.name
    FROM role_permissions rp
    JOIN permissions p ON rp.permission_id = p.id
    WHERE rp.role_id = 4
) p2 ON p1.id = p2.id
ORDER BY permission_name;

-- Find permissions granted to multiple roles (common patterns)
SELECT 
    p.name as permission_name,
    GROUP_CONCAT(DISTINCT r.name ORDER BY r.name SEPARATOR ', ') as roles_with_permission,
    COUNT(DISTINCT rp.role_id) as role_count
FROM permissions p
LEFT JOIN role_permissions rp ON p.id = rp.permission_id
LEFT JOIN roles r ON rp.role_id = r.id
GROUP BY p.id, p.name
HAVING role_count > 0
ORDER BY role_count DESC, p.name;

-- ═══════════════════════════════════════════════════════════
-- 3. PERMISSION GRANT/REVOKE - Modify Permissions
-- ═══════════════════════════════════════════════════════════

-- Grant permission to a role (safe - uses INSERT IGNORE)
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 12, id FROM permissions WHERE name = 'approve_request'
LIMIT 1;

-- Revoke permission from a role
DELETE FROM role_permissions
WHERE role_id = 12
  AND permission_id = (
      SELECT id FROM permissions WHERE name = 'approve_request'
  );

-- Grant all approval permissions to a role
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 10, id FROM permissions
WHERE name LIKE 'approve_%';

-- ═══════════════════════════════════════════════════════════
-- 4. USER OVERRIDE QUERIES - User-Specific Permissions
-- ═══════════════════════════════════════════════════════════

-- See all active user permission overrides
SELECT 
    u.user_id,
    u.full_name,
    p.name as permission_name,
    up.is_granted,
    up.expires_at
FROM user_permissions up
JOIN users u ON up.user_id = u.user_id
JOIN permissions p ON up.permission_id = p.id
WHERE u.is_active = 1
ORDER BY u.full_name, p.name;

-- Find expired permission overrides (cleanup candidates)
SELECT 
    u.user_id,
    u.full_name,
    p.name as permission_name,
    up.expires_at,
    DATEDIFF(NOW(), up.expires_at) as days_expired
FROM user_permissions up
JOIN users u ON up.user_id = u.user_id
JOIN permissions p ON up.permission_id = p.id
WHERE up.expires_at < NOW()
ORDER BY up.expires_at DESC;

-- Grant permission to specific user (temporary with expiration)
INSERT INTO user_permissions (user_id, permission_id, is_granted, expires_at)
SELECT 5, id, 1, DATE_ADD(NOW(), INTERVAL 30 DAY)
FROM permissions WHERE name = 'author_override'
LIMIT 1;

-- Deny permission to specific user (override role)
INSERT INTO user_permissions (user_id, permission_id, is_granted)
SELECT 5, id, 0
FROM permissions WHERE name = 'approve_request'
LIMIT 1;

-- Revoke user permission override
DELETE FROM user_permissions
WHERE user_id = 5
  AND permission_id = (
      SELECT id FROM permissions WHERE name = 'approve_request'
  );

-- ═══════════════════════════════════════════════════════════
-- 5. ROLE DELETION/MODIFICATION - Careful Operations
-- ═══════════════════════════════════════════════════════════

-- Show all permissions about to be removed when deleting a role
SELECT 
    p.id,
    p.name,
    p.description
FROM role_permissions rp
JOIN permissions p ON rp.permission_id = p.id
WHERE rp.role_id = 7  -- Check before deleting
ORDER BY p.name;

-- Safe delete: First revoke all permissions
-- (optional, just for cleanup - can re-assign after)
DELETE FROM role_permissions
WHERE role_id = 7;

-- Then delete unused role (if no users have it)
-- DELETE FROM roles WHERE id = 7;

-- ═══════════════════════════════════════════════════════════
-- 6. MIGRATION & MAINTENANCE - Bulk Operations
-- ═══════════════════════════════════════════════════════════

-- Check for orphaned role_permissions (role doesn't exist)
SELECT 
    rp.role_id,
    rp.permission_id,
    'Orphaned - Role missing' as issue
FROM role_permissions rp
WHERE NOT EXISTS (
    SELECT 1 FROM roles r WHERE r.id = rp.role_id
);

-- Check for orphaned user_permissions (user or permission missing)
SELECT 
    up.user_id,
    up.permission_id,
    'Orphaned - User or Permission missing' as issue
FROM user_permissions up
WHERE NOT EXISTS (
    SELECT 1 FROM users u WHERE u.user_id = up.user_id
)
   OR NOT EXISTS (
    SELECT 1 FROM permissions p WHERE p.id = up.permission_id
);

-- Clean up expired permission overrides
DELETE FROM user_permissions
WHERE expires_at < NOW();

-- ═══════════════════════════════════════════════════════════
-- 7. REPORTING - Permission Analysis
-- ═══════════════════════════════════════════════════════════

-- Permission usage report - which roles have which permissions
SELECT 
    r.id,
    r.name as role,
    SUM(CASE WHEN p.name LIKE 'create_%' THEN 1 ELSE 0 END) as create_count,
    SUM(CASE WHEN p.name LIKE 'edit_%' THEN 1 ELSE 0 END) as edit_count,
    SUM(CASE WHEN p.name LIKE 'approve_%' THEN 1 ELSE 0 END) as approve_count,
    SUM(CASE WHEN p.name LIKE 'view_%' THEN 1 ELSE 0 END) as view_count,
    SUM(CASE WHEN p.name LIKE 'print_%' THEN 1 ELSE 0 END) as print_count,
    COUNT(DISTINCT p.name) as total_permissions
FROM roles r
LEFT JOIN role_permissions rp ON r.id = rp.role_id
LEFT JOIN permissions p ON rp.permission_id = p.id
GROUP BY r.id, r.name
ORDER BY r.id;

-- Users and their role assignments
SELECT 
    r.name as role,
    COUNT(u.user_id) as user_count,
    GROUP_CONCAT(DISTINCT u.full_name ORDER BY u.full_name SEPARATOR ', ') as users
FROM roles r
LEFT JOIN users u ON r.id = u.role_id AND u.is_active = 1
GROUP BY r.id, r.name
ORDER BY r.id;

-- ═══════════════════════════════════════════════════════════
-- 8. DEBUGGING - Troubleshoot Permission Issues
-- ═══════════════════════════════════════════════════════════

-- Why is user 5 not seeing "approve_request"?
SELECT 
    'User has role' as check_type,
    u.role_id,
    r.name as role_name
FROM users u
LEFT JOIN roles r ON u.role_id = r.id
WHERE u.user_id = 5
UNION ALL
SELECT 
    'Role has permission' as check_type,
    rp.role_id,
    p.name
FROM role_permissions rp
JOIN permissions p ON rp.permission_id = p.id
WHERE rp.role_id = (SELECT role_id FROM users WHERE user_id = 5)
  AND p.name = 'approve_request'
UNION ALL
SELECT 
    'User override (if blocked)' as check_type,
    up.user_id,
    p.name
FROM user_permissions up
JOIN permissions p ON up.permission_id = p.id
WHERE up.user_id = 5
  AND p.name = 'approve_request'
  AND up.is_granted = 0;

-- Show all ways a user has a permission (role or override)
SELECT 
    u.full_name,
    'via Role' as grant_source,
    r.name as source_name,
    p.name as permission
FROM users u
JOIN roles r ON u.role_id = r.id
JOIN role_permissions rp ON r.id = rp.role_id
JOIN permissions p ON rp.permission_id = p.id
WHERE u.user_id = 5 AND p.name = 'approve_request'
UNION ALL
SELECT 
    u.full_name,
    'via Override' as grant_source,
    'User Override' as source_name,
    p.name as permission
FROM users u
JOIN user_permissions up ON u.user_id = up.user_id
JOIN permissions p ON up.permission_id = p.id
WHERE u.user_id = 5
  AND p.name = 'approve_request'
  AND up.is_granted = 1;

-- ═══════════════════════════════════════════════════════════
-- 9. VALIDATION - Integrity Checks
-- ═══════════════════════════════════════════════════════════

-- Ensure all permissions referenced in page_guard exist
SELECT COUNT(*) as total_permissions FROM permissions;

-- Check for inactive users with permission overrides
SELECT 
    u.user_id,
    u.full_name,
    u.is_active,
    COUNT(up.permission_id) as override_count
FROM users u
LEFT JOIN user_permissions up ON u.user_id = up.user_id
WHERE u.is_active = 0
GROUP BY u.user_id
HAVING override_count > 0;

-- Verify core roles exist
SELECT 
    id,
    name,
    CASE 
        WHEN id = 1 THEN 'Viewer'
        WHEN id = 2 THEN 'Procurement Officer'
        WHEN id = 3 THEN 'Finance Officer'
        WHEN id = 4 THEN 'HOD'
        WHEN id = 5 THEN 'Admin'
        WHEN id = 6 THEN 'SuperAdmin'
        WHEN id = 9 THEN 'Deputy Government Chemist'
        WHEN id = 10 THEN 'Director HRM&A'
        WHEN id = 11 THEN 'Director Procurement'
        WHEN id = 12 THEN 'Requestor'
        ELSE 'Other'
    END as expected_role
FROM roles
WHERE id IN (1, 2, 3, 4, 5, 6, 9, 10, 11, 12)
ORDER BY id;
