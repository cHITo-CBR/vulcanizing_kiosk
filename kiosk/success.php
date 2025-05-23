<?php
require_once '../includes/header.php';
require_once '../config/database.php';

$database = new Database();
$conn = $database->getConnection();

$queue_number = $_GET['queue'] ?? null;

if (!$queue_number) {
    header('Location: index.php');
    exit();
}

// Get order details
$stmt = $conn->prepare("
    SELECT o.*, c.name, c.phone, c.vehicle_type
    FROM orders o
    JOIN customers c ON o.customer_id = c.id
    JOIN queue_status qs ON o.id = qs.order_id
    WHERE qs.queue_number = ?
");
$stmt->bind_param("s", $queue_number);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
?>

<div class="row justify-content-center">
    <div class="col-md-8 text-center">
        <div class="card">
            <div class="card-body">
                <div class="mb-4">
                    <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                </div>
                
                <h2 class="card-title mb-4">Order Placed Successfully!</h2>
                           <!-- QR Code Section -->
                <div class="qr-code mt-4">
    <img src="../images/qrcode_205425134_fcf510b6aaea2d1e4db5b848b7be26b6.png" alt="Queue Status QR Code">
</div>
                
                <div class="queue-number mb-4">
                    <h3>Your Queue Number</h3>
                    <h1 class="display-1"><?= htmlspecialchars($queue_number) ?></h1>
                </div>
                
                <?php if ($order): ?>
                <div class="order-details mb-4">
                    <h4>Order Details</h4>
                    <p><strong>Customer:</strong> <?= htmlspecialchars($order['name']) ?></p>
                    <p><strong>Vehicle:</strong> <?= htmlspecialchars($order['vehicle_type']) ?></p>
                    <p><strong>Total Amount:</strong> ₱<?= number_format($order['total_amount'], 2) ?></p>
                
                <?php endif; ?>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Please wait for your queue number to be called. You can check the queue status on the main screen.
                </div>
                
                <div class="mt-4">
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-home me-2"></i> Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .qr-code {
        margin: 20px 0; /* Add some spacing around the QR code */
        text-align: center; /* Center the QR code */
    }
    
    .qr-code img {
        width: 200px; /* Set the width to 200px */
        height: 200px; /* Set the height to 200px */
    }
</style>

<?php require_once '../includes/footer.php'; ?> 