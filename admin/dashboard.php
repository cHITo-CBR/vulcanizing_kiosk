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

// Get statistics
$stats = [
    'total_orders' => $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'],
    'total_customers' => $conn->query("SELECT COUNT(*) as count FROM customers")->fetch_assoc()['count'],
    'pending_orders' => $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")->fetch_assoc()['count'],
    'total_revenue' => $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status = 'completed'")->fetch_assoc()['total'] ?? 0
];

// Get recent orders
$recent_orders = $conn->query("
    SELECT o.*, c.name as customer_name, c.phone, c.vehicle_type
    FROM orders o
    LEFT JOIN customers c ON o.customer_id = c.id
    ORDER BY o.created_at DESC
    LIMIT 10
");
?>

<div class="row">
    <!-- Sidebar -->
    <div class="col-lg-3">
        <div class="sidebar">
            <div class="nav flex-column">
                <a class="nav-link active" href="dashboard.php">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a class="nav-link" href="orders.php">
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
        <!-- Statistics Cards -->
        <div class="dashboard-stats">
            <div class="stat-card orders">
                <i class="fas fa-shopping-cart"></i>
                <h3>Total Orders</h3>
                <p><?= number_format($stats['total_orders']) ?></p>
            </div>
            <div class="stat-card customers">
                <i class="fas fa-users"></i>
                <h3>Total Customers</h3>
                <p><?= number_format($stats['total_customers']) ?></p>
            </div>
            <div class="stat-card pending">
                <i class="fas fa-clock"></i>
                <h3>Pending Orders</h3>
                <p><?= number_format($stats['pending_orders']) ?></p>
            </div>
            <div class="stat-card revenue">
                <i class="fas fa-money-bill-wave"></i>
                <h3>Total Revenue</h3>
                <p>₱<?= number_format($stats['total_revenue'], 2) ?></p>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="card">
            <div class="card-body">
                <h3 class="card-title mb-4">Recent Orders</h3>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Vehicle</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = $recent_orders->fetch_assoc()): ?>
                            <tr>
                                <td>#<?= $order['id'] ?></td>
                                <td>
                                    <?= htmlspecialchars($order['customer_name']) ?><br>
                                    <small class="text-muted"><?= $order['phone'] ?></small>
                                </td>
                                <td><?= htmlspecialchars($order['vehicle_type']) ?></td>
                                <td>₱<?= number_format($order['total_amount'], 2) ?></td>
                                <td>
                                    <span class="badge bg-<?= $order['status'] == 'completed' ? 'success' : 
                                        ($order['status'] == 'pending' ? 'warning' : 'primary') ?>">
                                        <?= ucfirst($order['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('M d, Y H:i', strtotime($order['created_at'])) ?></td>
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
<style>
/* Vulcanizing Kiosk Dashboard Styles */

.dashboard-stats {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  margin-bottom: 30px;
}

.stat-card {
  flex: 1;
  min-width: 220px;
  padding: 20px;
  border-radius: 8px;
  color: #fff;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
  transition: transform 0.2s, box-shadow 0.2s;
  position: relative;
  overflow: hidden;
}

.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
}

.stat-card h3 {
  font-size: 16px;
  margin-bottom: 10px;
  font-weight: 500;
}

.stat-card p {
  font-size: 24px;
  font-weight: 700;
  margin: 0;
}

.stat-card i {
  position: absolute;
  right: 20px;
  top: 20px;
  font-size: 24px;
  opacity: 0.8;
}

/* Vulcanizing-themed color scheme */
.stat-card.orders {
  background-color: #2d3b45; /* Darker blue-gray like industrial rubber */
  border-left: 5px solid #4d6370;
}

.stat-card.customers {
  background-color: #2a5d4c; /* Deep forest green like rubber trees */
  border-left: 5px solid #3a7a62;
}

.stat-card.pending {
  background-color: #b06e2a; /* Amber color like heated rubber */
  border-left: 5px solid #d48d3b;
}

.stat-card.revenue {
  background-color: #1e6e3c; /* Rich green like fresh rubber */
  border-left: 5px solid #27864a;
}

/* Add rubber texture overlay to cards */
.stat-card::after {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.05' fill-rule='evenodd'/%3E%3C/svg%3E");
  opacity: 0.4;
  pointer-events: none;
}

/* Additional styling for the dashboard */
.dashboard-container {
  background-color: #f5f7f8;
  padding: 30px;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

/* Responsive adjustments */
@media (max-width: 992px) {
  .stat-card {
    min-width: calc(50% - 20px);
  }
}

@media (max-width: 576px) {
  .stat-card {
    min-width: 100%;
  }
}
</style>

<?php require_once '../includes/footer.php'; ?> 