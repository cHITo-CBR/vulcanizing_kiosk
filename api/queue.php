<?php

require_once '../config/database.php';

header('Content-Type: application/json');

$database = new Database();
$conn = $database->getConnection();

// Get current queue number and next in line
$stmt = $conn->prepare("
    SELECT 
        qs.queue_number,
        qs.status,
        TIMESTAMPDIFF(MINUTE, qs.created_at, NOW()) as wait_time,
        c.name as customer_name,
        c.vehicle_type
    FROM queue_status qs
    JOIN orders o ON qs.order_id = o.id
    JOIN customers c ON o.customer_id = c.id
    WHERE qs.status IN ('processing', 'pending')
    ORDER BY 
        CASE 
            WHEN qs.status = 'processing' THEN 1
            WHEN qs.status = 'pending' THEN 2
        END,
        qs.created_at ASC
    LIMIT 3
");
$stmt->execute();
$result = $stmt->get_result();

$queue = [];
while ($row = $result->fetch_assoc()) {
    $queue[] = $row;
}

// Get estimated wait time
$stmt = $conn->prepare("
    SELECT AVG(duration) as avg_duration
    FROM services s
    JOIN order_items oi ON s.id = oi.service_id
    JOIN orders o ON oi.order_id = o.id
    JOIN queue_status qs ON o.id = qs.order_id
    WHERE qs.status = 'processing'
    AND qs.created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)
");
$stmt->execute();
$avg_duration = $stmt->get_result()->fetch_assoc()['avg_duration'] ?? 30;

echo json_encode([
    'current' => $queue[0] ?? null,
    'next' => array_slice($queue, 1),
    'estimated_wait' => $avg_duration,
    'timestamp' => date('Y-m-d H:i:s')
]);
?> 