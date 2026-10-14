<?php
/**
 * Practical 12: Get Event Audit Logs (JSON) - Advanced Extension
 */

require_once __DIR__ . "/db.php";

header("Content-Type: application/json; charset=UTF-8");

$stmt = $pdo->query("
    SELECT log_id, event_id, action, details, performed_by, ip_address, created_at
    FROM event_audit_logs
    ORDER BY log_id DESC
    LIMIT 40
");
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(["status" => "success", "data" => $logs]);
