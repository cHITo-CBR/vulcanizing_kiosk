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

$customer_id = $_GET['id'] ?? null;

if (!$customer_id) {
    header('Location: customers.php');
    exit();
}

// Get customer details
$stmt = $conn->prepare("
    SELECT c.*, 
           COUNT(o.id) as total_orders,
           SUM(o.total_amount) as total_spent,
           MAX(o.created_at) as last_visit
    FROM customers c
    LEFT JOIN orders o ON c.id = o.customer_id
    WHERE c.id = ?
    GROUP BY c.id
");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();

if (!$customer) {
    header('Location: customers.php');
    exit();
}

// Get customer's order history
$stmt = $conn->prepare("
    SELECT o.*, qs.queue_number
    FROM orders o
    LEFT JOIN queue_status qs ON o.id = qs.order_id
    WHERE o.customer_id = ?
    ORDER BY o.created_at DESC
");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$orders = $stmt->get_result();
?>

<div class="row">
    <!-- Sidebar -->
    <div class="col-lg-3">
        <div class="sidebar">
            <div class="nav flex-column">
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a class="nav-link" href="orders.php">
                    <i class="fas fa-shopping-cart"></i> Orders
                </a>
                <a class="nav-link active" href="customers.php">
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
                    <h3 class="card-title">Customer Details</h3>
                    <div class="d-flex gap-2">
                        <a href="customers.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Customers
                        </a>
                        <button type="button" class="btn btn-primary" onclick="printCustomer()">
                            <i class="fas fa-print"></i> Print Details
                        </button>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5>Personal Information</h5>
                        <table class="table table-sm">
                            <tr>
                                <th>Name:</th>
                                <td><?= htmlspecialchars($customer['name']) ?></td>
                            </tr>
                            <tr>
                                <th>Phone:</th>
                                <td><?= htmlspecialchars($customer['phone']) ?></td>
                            </tr>
                            <tr>
                                <th>Vehicle Type:</th>
                                <td><?= htmlspecialchars($customer['vehicle_type']) ?></td>
                            </tr>
                            <tr>
                                <th>Member Since:</th>
                                <td><?= date('M d, Y', strtotime($customer['created_at'])) ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5>Order Statistics</h5>
                        <table class="table table-sm">
                            <tr>
                                <th>Total Orders:</th>
                                <td><?= $customer['total_orders'] ?></td>
                            </tr>
                            <tr>
                                <th>Total Spent:</th>
                                <td>₱<?= number_format($customer['total_spent'] ?? 0, 2) ?></td>
                            </tr>
                            <tr>
                                <th>Last Visit:</th>
                                <td><?= $customer['last_visit'] ? date('M d, Y H:i', strtotime($customer['last_visit'])) : 'N/A' ?></td>
                            </tr>
                            <tr>
                                <th>Average Order:</th>
                                <td>₱<?= $customer['total_orders'] > 0 ? 
                                    number_format(($customer['total_spent'] ?? 0) / $customer['total_orders'], 2) : '0.00' ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <h5>Order History</h5>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Queue #</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = $orders->fetch_assoc()): ?>
                            <tr>
                                <td>#<?= $order['id'] ?></td>
                                <td><?= $order['queue_number'] ?? 'N/A' ?></td>
                                <td><?= date('M d, Y H:i', strtotime($order['created_at'])) ?></td>
                                <td>₱<?= number_format($order['total_amount'], 2) ?></td>
                                <td>
                                    <span class="badge bg-<?= $order['status'] === 'completed' ? 'success' : 
                                        ($order['status'] === 'pending' ? 'warning' : 'primary') ?>">
                                        <?= ucfirst($order['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="view_order.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function printCustomer() {
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