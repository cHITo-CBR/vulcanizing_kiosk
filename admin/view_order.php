<?php
require_once '../includes/header.php';
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

$database = new Database();
$conn = $database->getConnection();

$order_id = $_GET['id'] ?? null;

if (!$order_id) {
    header('Location: orders.php');
    exit();
}

// Get order details
$stmt = $conn->prepare("
    SELECT o.*, c.name as customer_name, c.phone, c.vehicle_type, qs.queue_number, qs.status as queue_status
    FROM orders o
    JOIN customers c ON o.customer_id = c.id
    LEFT JOIN queue_status qs ON o.id = qs.order_id
    WHERE o.id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header('Location: orders.php');
    exit();
}

// Get order items
$stmt = $conn->prepare("
    SELECT oi.*, s.name as service_name, s.description
    FROM order_items oi
    JOIN services s ON oi.service_id = s.id
    WHERE oi.order_id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result();
?>

<div class="row">
    <!-- Sidebar -->
    <div class="col-lg-3">
        <div class="sidebar">
            <div class="nav flex-column">
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a class="nav-link active" href="orders.php">
                    <i class="fas fa-shopping-cart"></i> Orders
                </a>
                <a class="nav-link" href="customers.php">
                    <i class="fas fa-users"></i> Customers
                </a>
                <a class="nav-link" href="services.php">
                    <i class="fas fa-cogs"></i> Services
                </a>
                <a class="nav-link" href="queue.php">
                    <i class="fas fa-list-ol"></i> Queue
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="col-lg-9">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="card-title">Order Details #<?= $order_id ?></h3>
                    <div class="d-flex gap-2">
                        <a href="orders.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Orders
                        </a>
                        <button type="button" class="btn btn-primary" onclick="printOrder()">
                            <i class="fas fa-print"></i> Print Order
                        </button>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5>Customer Information</h5>
                        <table class="table table-sm">
                            <tr>
                                <th>Name:</th>
                                <td><?= htmlspecialchars($order['customer_name']) ?></td>
                            </tr>
                            <tr>
                                <th>Phone:</th>
                                <td><?= htmlspecialchars($order['phone']) ?></td>
                            </tr>
                            <tr>
                                <th>Vehicle Type:</th>
                                <td><?= htmlspecialchars($order['vehicle_type']) ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5>Order Information</h5>
                        <table class="table table-sm">
                            <tr>
                                <th>Order Date:</th>
                                <td><?= date('M d, Y H:i', strtotime($order['created_at'])) ?></td>
                            </tr>
                            <tr>
                                <th>Queue Number:</th>
                                <td><?= $order['queue_number'] ?? 'N/A' ?></td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    <span class="badge bg-<?= $order['status'] === 'completed' ? 'success' : 
                                        ($order['status'] === 'pending' ? 'warning' : 'primary') ?>">
                                        <?= ucfirst($order['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <h5>Order Items</h5>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Service</th>
                                <th>Description</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($item = $items->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['service_name']) ?></td>
                                <td><?= htmlspecialchars($item['description']) ?></td>
                                <td><?= $item['quantity'] ?></td>
                                <td>₱<?= number_format($item['price'], 2) ?></td>
                                <td>₱<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4" class="text-end">Total Amount:</th>
                                <th>₱<?= number_format($order['total_amount'], 2) ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <?php if ($order['queue_status'] === 'pending'): ?>
                <div class="mt-4">
                    <form method="POST" action="queue.php" class="d-inline">
                        <input type="hidden" name="queue_id" value="<?= $order['id'] ?>">
                        <input type="hidden" name="status" value="processing">
                        <input type="hidden" name="update_status" value="1">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-play"></i> Start Processing
                        </button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function printOrder() {
    window.print();
}
</script>

<style>
@media print {
    .sidebar, .btn {
        display: none !important;
    }
    
    .card {
        border: none !important;
    }
    
    .table {
        border-collapse: collapse !important;
    }
    
    .table td, .table th {
        border: 1px solid #dee2e6 !important;
    }
}
</style>

<?php require_once '../includes/footer.php'; ?> 