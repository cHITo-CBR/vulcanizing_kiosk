<?php
require_once '../config/database.php';
$database = new Database();
$conn = $database->getConnection();

function getQueue($conn, $status) {
    $stmt = $conn->prepare("
        SELECT qs.queue_number, c.name
        FROM queue_status qs
        JOIN orders o ON qs.order_id = o.id
        JOIN customers c ON o.customer_id = c.id
        WHERE qs.status = ?
        ORDER BY qs.id ASC
    ");
    $stmt->bind_param('s', $status);
    $stmt->execute();
    return $stmt->get_result();
}

$processing = getQueue($conn, 'processing');
$pending = getQueue($conn, 'pending');
$completed = getQueue($conn, 'completed');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Queue Display - Vulcanizing Kiosk</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@700&display=swap" rel="stylesheet">
    <link href="../assets/css/vulcanizing-theme.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <style>
        body {
            background: var(--bg-dark);
            color: #fff;
            font-family: 'Roboto', 'Segoe UI', Arial, sans-serif;
            min-height: 100vh;
            margin: 0;
        }
        .queue-bulletin-container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 1rem;
        }
        .queue-bulletin-grid {
            display: flex;
            gap: 2.5rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        .queue-section {
            background: #fff;
            border-radius: 1.5rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.18);
            padding: 2.5rem 2rem;
            min-width: 320px;
            flex: 1 1 340px;
            max-width: 400px;
            text-align: center;
            color: #222;
            position: relative;
            overflow: hidden;
        }
        .queue-section.processing {
            border-top: 8px solid var(--processing);
        }
        .queue-section.pending {
            border-top: 8px solid var(--pending);
        }
        .queue-section.completed {
            border-top: 8px solid var(--completed);
        }
        .queue-section h3 {
            font-size: 2rem;
            margin-bottom: 1.5rem;
            font-weight: 700;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.7rem;
        }
        .queue-section.processing h3 i { color: var(--processing); }
        .queue-section.pending h3 i { color: var(--pending); }
        .queue-section.completed h3 i { color: var(--completed); }
        .queue-section ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .queue-section li {
            font-size: 1.5rem;
            margin-bottom: 1.2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8f9fa;
            border-radius: 0.7rem;
            padding: 1.1rem 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            font-weight: 600;
            color: #222;
            animation: fadeIn 0.7s;
        }
        .queue-num {
            font-size: 2.2rem;
            font-weight: bold;
            color: var(--primary);
            margin-right: 1.2rem;
            letter-spacing: 2px;
        }
        .customer {
            color: #495057;
            font-size: 1.2rem;
            font-weight: 500;
        }
        @media (max-width: 900px) {
            .queue-bulletin-grid { flex-direction: column; gap: 1.5rem; }
            .queue-section { max-width: 100%; }
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
<div class="queue-bulletin-container">
    <h2 class="text-center mb-5" style="font-size:2.5rem; font-weight:700; letter-spacing:2px; color:var(--accent);">
        <i class="fas fa-list-ol me-2"></i> Queue Status
    </h2>
    <div class="queue-bulletin-grid">
        <div class="queue-section processing" data-aos="fade-up">
            <h3><i class="fas fa-cogs"></i> Processing</h3>
            <ul>
                <?php while ($row = $processing->fetch_assoc()): ?>
                    <li><span class="queue-num"><?= htmlspecialchars($row['queue_number']) ?></span> <span class="customer"><i class="fas fa-user"></i> <?= htmlspecialchars($row['name']) ?></span></li>
                <?php endwhile; ?>
            </ul>
        </div>
        <div class="queue-section pending" data-aos="fade-up" data-aos-delay="100">
            <h3><i class="fas fa-hourglass-half"></i> Pending</h3>
            <ul>
                <?php while ($row = $pending->fetch_assoc()): ?>
                    <li><span class="queue-num"><?= htmlspecialchars($row['queue_number']) ?></span> <span class="customer"><i class="fas fa-user"></i> <?= htmlspecialchars($row['name']) ?></span></li>
                <?php endwhile; ?>
            </ul>
        </div>
        <div class="queue-section completed" data-aos="fade-up" data-aos-delay="200">
            <h3><i class="fas fa-check-circle"></i> Completed</h3>
            <ul>
                <?php while ($row = $completed->fetch_assoc()): ?>
                    <li><span class="queue-num"><?= htmlspecialchars($row['queue_number']) ?></span> <span class="customer"><i class="fas fa-user"></i> <?= htmlspecialchars($row['name']) ?></span></li>
                <?php endwhile; ?>
            </ul>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>
AOS.init();
// Auto-refresh every 10 seconds
setInterval(() => { window.location.reload(); }, 10000);
</script>
</body>
</html> 