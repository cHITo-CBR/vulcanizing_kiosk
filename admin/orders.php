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

// Handle status updates
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $order_id);
    $stmt->execute();
    
    // If order is completed, update queue status
    if ($new_status === 'completed') {
        $stmt = $conn->prepare("UPDATE queue_status SET status = 'completed' WHERE order_id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
    }
}

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$date_filter = $_GET['date'] ?? 'all';

// Build query
$query = "
    SELECT o.*, c.name as customer_name, c.phone, c.vehicle_type, qs.queue_number
    FROM orders o
    LEFT JOIN customers c ON o.customer_id = c.id
    LEFT JOIN queue_status qs ON o.id = qs.order_id
    WHERE 1=1
";

if ($status_filter !== 'all') {
    $query .= " AND o.status = '$status_filter'";
}

if ($date_filter === 'today') {
    $query .= " AND DATE(o.created_at) = CURDATE()";
} elseif ($date_filter === 'week') {
    $query .= " AND o.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
} elseif ($date_filter === 'month') {
    $query .= " AND o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
}

$query .= " ORDER BY o.created_at DESC";

$orders = $conn->query($query);
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
                    <h3 class="card-title">Orders Management</h3>
                    
                    <!-- Filters -->
                    <div class="d-flex gap-2">
                        <select class="form-select" id="status-filter">
                            <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>All Status</option>
                            <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="processing" <?= $status_filter === 'processing' ? 'selected' : '' ?>>Processing</option>
                            <option value="completed" <?= $status_filter === 'completed' ? 'selected' : '' ?>>Completed</option>
                        </select>
                        
                        <select class="form-select" id="date-filter">
                            <option value="all" <?= $date_filter === 'all' ? 'selected' : '' ?>>All Time</option>
                            <option value="today" <?= $date_filter === 'today' ? 'selected' : '' ?>>Today</option>
                            <option value="week" <?= $date_filter === 'week' ? 'selected' : '' ?>>Last 7 Days</option>
                            <option value="month" <?= $date_filter === 'month' ? 'selected' : '' ?>>Last 30 Days</option>
                        </select>
                    </div>
                </div>

                <div class="table-responsive" data-aos="fade-up">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <input type="text" id="order-search" class="form-control form-control-sm" placeholder="Search orders..." style="width: 220px;">
                        </div>
                        <small class="text-muted">Tip: You can search and filter like a database table.</small>
                    </div>
                    <table class="table table-hover table-striped align-middle">
                        <thead class="table-dark sticky-top">
                            <tr>
                                <th>Order ID</th>
                                <th>Queue #</th>
                                <th>Customer</th>
                                <th>Vehicle</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="orders-table-body">
                            <?php while ($order = $orders->fetch_assoc()): ?>
                            <tr>
                                <td>#<?= $order['id'] ?></td>
                                <td><?= $order['queue_number'] ?></td>
                                <td>
                                    <?= htmlspecialchars($order['customer_name']) ?><br>
                                    <small class="text-muted"><?= $order['phone'] ?></small>
                                </td>
                                <td><?= htmlspecialchars($order['vehicle_type']) ?></td>
                                <td>₱<?= number_format($order['total_amount'], 2) ?></td>
                                <td>
                                    <form method="POST" class="status-form">
                                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                                            <option value="completed" <?= $order['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                        </select>
                                        <input type="hidden" name="update_status" value="1">
                                    </form>
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
                    <div class="mt-2"><span id="order-count"></span></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Handle filters
document.getElementById('status-filter').addEventListener('change', updateFilters);
document.getElementById('date-filter').addEventListener('change', updateFilters);

function updateFilters() {
    const status = document.getElementById('status-filter').value;
    const date = document.getElementById('date-filter').value;
    window.location.href = `orders.php?status=${status}&date=${date}`;
}

// Handle order deletion
function deleteOrder(orderId) {
    if (confirm('Are you sure you want to delete this order?')) {
        fetch('delete_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ order_id: orderId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error deleting order: ' + data.message);
            }
        });
    }
}

// Client-side search for orders table
const searchInput = document.getElementById('order-search');
const tableBody = document.getElementById('orders-table-body');
const orderCount = document.getElementById('order-count');

function updateOrderCount() {
    const visibleRows = tableBody.querySelectorAll('tr:not([style*="display: none"])');
    orderCount.textContent = `${visibleRows.length} record(s) shown`;
}

if (searchInput && tableBody) {
    searchInput.addEventListener('input', function() {
        const val = this.value.toLowerCase();
        let shown = 0;
        tableBody.querySelectorAll('tr').forEach(row => {
            const text = row.textContent.toLowerCase();
            if (text.includes(val)) {
                row.style.display = '';
                shown++;
            } else {
                row.style.display = 'none';
            }
        });
        updateOrderCount();
    });
    updateOrderCount();
}
</script>

<?php require_once '../includes/footer.php'; ?> 