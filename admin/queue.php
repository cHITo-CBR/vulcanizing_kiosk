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

// Handle queue actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $queue_id = $_POST['queue_id'];
        $new_status = $_POST['status']; 
        
        $stmt = $conn->prepare("UPDATE queue_status SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $queue_id);
        
        if ($stmt->execute()) {
            // If completed, update order status
            if ($new_status === 'completed') {
                $stmt = $conn->prepare("
                    UPDATE orders o 
                    JOIN queue_status qs ON o.id = qs.order_id 
                    SET o.status = 'completed' 
                    WHERE qs.id = ?
                ");
                $stmt->bind_param("i", $queue_id);
                $stmt->execute();
            }
            echo json_encode(['success' => true, 'message' => 'Queue status updated successfully']);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating queue status']);
            exit;
        }
    }
}

// Get current queue
$queue = $conn->query("
    SELECT qs.*, o.total_amount, c.name as customer_name, c.phone, c.vehicle_type
    FROM queue_status qs
    JOIN orders o ON qs.order_id = o.id
    JOIN customers c ON o.customer_id = c.id
    WHERE qs.status IN ('pending', 'processing')
    ORDER BY qs.created_at ASC
");
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
                <a class="nav-link" href="customers.php">
                    <i class="fas fa-users"></i> Customers
                </a>
                <a class="nav-link" href="services.php">
                    <i class="fas fa-cogs"></i> Services
                </a>
                <a class="nav-link active" href="queue.php">
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
                    <h3 class="card-title">Queue Management</h3>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary" onclick="refreshQueue()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                        <button type="button" class="btn btn-success" onclick="printQueue()">
                            <i class="fas fa-print"></i> Print Queue
                        </button>
                    </div>
                </div>

                <div id="alert-container"></div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Queue #</th>
                                <th>Customer</th>
                                <th>Vehicle</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Time</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="queue-table-body">
                            <?php while ($item = $queue->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <h4 class="mb-0"><?= $item['queue_number'] ?></h4>
                                </td>
                                <td>
                                    <?= htmlspecialchars($item['customer_name']) ?><br>
                                    <small class="text-muted"><?= $item['phone'] ?></small>
                                </td>
                                <td><?= htmlspecialchars($item['vehicle_type']) ?></td>
                                <td>₱<?= number_format($item['total_amount'], 2) ?></td>
                                <td>
                                    <select class="form-select form-select-sm status-select" 
                                            data-queue-id="<?= $item['id'] ?>"
                                            onchange="updateStatus(this)">
                                        <option value="pending" <?= $item['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="processing" <?= $item['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                                        <option value="completed" <?= $item['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                    </select>
                                </td>
                                <td><?= date('H:i', strtotime($item['created_at'])) ?></td>
                                <td>
                                    <a href="view_order.php?id=<?= $item['order_id'] ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-success" 
                                            onclick="callCustomer('<?= $item['queue_number'] ?>')">
                                        <i class="fas fa-bullhorn"></i>
                                    </button>
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
// Show alert message
function showAlert(message, type = 'success') {
    const alertContainer = document.getElementById('alert-container');
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    alertContainer.appendChild(alert);
    setTimeout(() => alert.remove(), 5000);
}

// Update queue status
function updateStatus(select) {
    const queueId = select.dataset.queueId;
    const newStatus = select.value;
    
    fetch('queue.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `queue_id=${queueId}&status=${newStatus}&update_status=1`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message);
            if (newStatus === 'completed') {
                select.closest('tr').remove();
            }
        } else {
            showAlert(data.message, 'danger');
            // Revert select to previous value
            select.value = select.dataset.previousValue;
        }
    })
    .catch(error => {
        showAlert('Error updating status', 'danger');
        select.value = select.dataset.previousValue;
    });
}

// Store previous value before change
document.querySelectorAll('.status-select').forEach(select => {
    select.dataset.previousValue = select.value;
    select.addEventListener('change', function() {
        // Hide any alert when changing status
        const alertContainer = document.getElementById('alert-container');
        alertContainer.innerHTML = '';
        this.dataset.previousValue = this.value;
    });
});

// Refresh queue
function refreshQueue() {
    fetch('queue.php')
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newTableBody = doc.getElementById('queue-table-body');
            document.getElementById('queue-table-body').innerHTML = newTableBody.innerHTML;
        })
        .catch(error => showAlert('Error refreshing queue', 'danger'));
}

// Print queue
function printQueue() {
    window.print();
}

// Call customer
function callCustomer(queueNumber) {
    if (confirm(`Call customer with queue number ${queueNumber}?`)) {
        // You can implement audio announcement here
        alert(`Calling customer ${queueNumber}!`);
    }
}

// Auto refresh every 10 seconds
setInterval(refreshQueue, 10000);
</script>

<style>
@media print {
    .sidebar, .btn, .status-select {
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