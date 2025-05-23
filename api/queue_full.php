<?php
require_once '../config/database.php';
header('Content-Type: application/json');

$database = new Database();
$conn = $database->getConnection();

$sql = "SELECT o.id, o.status, o.created_at, c.name as customer_name, c.vehicle_type
        FROM orders o
        LEFT JOIN customers c ON o.customer_id = c.id
        WHERE o.status IN ('processing', 'pending', 'completed')
        ORDER BY 
            CASE o.status 
                WHEN 'processing' THEN 1 
                WHEN 'pending' THEN 2 
                WHEN 'completed' THEN 3 
            END,
            o.created_at DESC";

$result = $conn->query($sql);

$queues = [
    'processing' => [],
    'pending' => [],
    'completed' => []
];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $queues[$row['status']][] = $row;
    }
}

echo json_encode($queues); 