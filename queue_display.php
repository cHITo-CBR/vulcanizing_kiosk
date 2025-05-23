<?php
require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();

// Fetch queue data
$sql = "SELECT o.*, c.name as customer_name, c.vehicle_type 
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Queue Display - Vulcanizing Kiosk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/vulcanizing-theme.css" rel="stylesheet">
    <style>
        body {
            background: var(--bg-dark);
            color: #fff;
            font-family: 'Segoe UI', Arial, sans-serif;
            overflow: hidden;
        }

        .queue-display {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            padding: 2rem;
            height: 100vh;
        }

        .queue-section {
            background: rgba(255, 255, 255, 0.1);
            border-radius: var(--radius);
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
        }

        .queue-header {
            text-align: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
        }

        .queue-header h2 {
            margin: 0;
            font-size: 2rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .queue-list {
            flex: 1;
            overflow-y: auto;
            padding-right: 0.5rem;
        }

        .queue-item {
            background: rgba(255, 255, 255, 0.1);
            border-radius: var(--radius);
            padding: 1rem;
            margin-bottom: 1rem;
            animation: fadeIn 0.5s ease-out;
        }

        .queue-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--primary);
            text-align: center;
            margin-bottom: 0.5rem;
        }

        .customer-info {
            text-align: center;
        }

        .customer-info p {
            margin: 0.25rem 0;
            font-size: 1.1rem;
        }

        .customer-info i {
            width: 20px;
            margin-right: 0.5rem;
            color: var(--primary);
        }

        .processing .queue-header h2 { color: var(--processing); }
        .pending .queue-header h2 { color: var(--pending); }
        .completed .queue-header h2 { color: var(--completed); }

        .processing .queue-number { color: var(--processing); }
        .pending .queue-number { color: var(--pending); }
        .completed .queue-number { color: var(--completed); }

        .processing .customer-info i { color: var(--processing); }
        .pending .customer-info i { color: var(--pending); }
        .completed .customer-info i { color: var(--completed); }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Custom Scrollbar */
        .queue-list::-webkit-scrollbar {
            width: 8px;
        }

        .queue-list::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }

        .queue-list::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 4px;
        }

        .queue-list::-webkit-scrollbar-thumb:hover {
            background: var(--primary-dark);
        }

        /* Auto-refresh animation */
        .refreshing {
            animation: refresh 1s ease-in-out;
        }

        @keyframes refresh {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .queue-display {
                grid-template-columns: 1fr;
                height: auto;
                min-height: 100vh;
            }

            .queue-section {
                min-height: 400px;
            }
        }
    </style>
</head>
<body>
    <div class="queue-display">
        <!-- Pending Queue (Left) -->
        <div class="queue-section pending">
            <div class="queue-header">
                <h2>Pending</h2>
            </div>
            <div class="queue-list" id="pending-list"></div>
        </div>
        <!-- Processing Queue (Center) -->
        <div class="queue-section processing">
            <div class="queue-header">
                <h2>Processing</h2>
            </div>
            <div class="queue-list" id="processing-list"></div>
        </div>
        <!-- Completed Queue (Right) -->
        <div class="queue-section completed">
            <div class="queue-header">
                <h2>Completed</h2>
            </div>
            <div class="queue-list" id="completed-list"></div>
        </div>
    </div>

    <script>
    function renderQueue(listId, orders) {
        const list = document.getElementById(listId);
        if (!orders || orders.length === 0) {
            list.innerHTML = '<div class="text-center text-muted">No entries</div>';
            return;
        }
        list.innerHTML = orders.map(order => `
            <div class="queue-item">
                <div class="queue-number">#${String(order.id).padStart(6, '0')}</div>
                <div class="customer-info">
                    <p><i class="fas fa-user"></i> ${order.customer_name || ''}</p>
                    <p><i class="fas fa-car"></i> ${order.vehicle_type || ''}</p>
                    <p><i class="fas fa-clock"></i> ${new Date(order.created_at).toLocaleTimeString('en-US', {hour: '2-digit', minute:'2-digit'})}</p>
                </div>
            </div>
        `).join('');
    }

    function refreshQueues() {
        document.querySelector('.queue-display').classList.add('refreshing');
        fetch('api/queue_full.php')
            .then(response => response.json())
            .then(data => {
                renderQueue('pending-list', data.pending);
                renderQueue('processing-list', data.processing);
                renderQueue('completed-list', data.completed);
                document.querySelector('.queue-display').classList.remove('refreshing');
            })
            .catch(error => {
                console.error('Error fetching queue data:', error);
                document.querySelector('.queue-display').classList.remove('refreshing');
            });
    }

    // Initial load
    refreshQueues();

    // Refresh every 5 seconds
    setInterval(refreshQueues, 5000);

    // Fullscreen toggle on double click
    document.addEventListener('dblclick', () => {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen();
        } else {
            document.exitFullscreen();
        }
    });
    </script>
</body>
</html> 