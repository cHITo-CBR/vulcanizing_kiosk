<?php
require_once '../includes/header.php';
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin'])) {
    header('Location: customers.php');
    exit();
}

$database = new Database();
$conn = $database->getConnection();

// Handle customer deletion
if (isset($_POST['delete_customer'])) {
    $customer_id = $_POST['customer_id'];
    
    // Check if customer has orders
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE customer_id = ?");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if ($result['count'] > 0) {
        $error = "Cannot delete customer with existing orders";
    } else {
        $stmt = $conn->prepare("DELETE FROM customers WHERE id = ?");
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $success = "Customer deleted successfully";
    }
}

// Get search parameters
$search = $_GET['search'] ?? '';
$vehicle_type = $_GET['vehicle_type'] ?? 'all';

// Build query
$query = "
    SELECT c.*, 
           COUNT(o.id) as total_orders,
           SUM(o.total_amount) as total_spent
    FROM customers c
    LEFT JOIN orders o ON c.id = o.customer_id
    WHERE 1=1
";

if (!empty($search)) {
    $search = "%$search%";
    $query .= " AND (c.name LIKE ? OR c.phone LIKE ?)";
}

if ($vehicle_type !== 'all') {
    $query .= " AND c.vehicle_type = ?";
}

$query .= " GROUP BY c.id ORDER BY c.name ASC";

$stmt = $conn->prepare($query);

if (!empty($search)) {
    if ($vehicle_type !== 'all') {
        $stmt->bind_param("sss", $search, $search, $vehicle_type);
    } else {
        $stmt->bind_param("ss", $search, $search);
    }
} elseif ($vehicle_type !== 'all') {
    $stmt->bind_param("s", $vehicle_type);
}

$stmt->execute();
$customers = $stmt->get_result();
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
                    <h3 class="card-title">Customers Management</h3>
                    
                    <!-- Search and Filter -->
                    <div class="d-flex gap-2">
                        <input type="text" class="form-control" id="search" placeholder="Search customers..." 
                               value="<?= htmlspecialchars($search) ?>">
                        
                        <select class="form-select" id="vehicle-type">
                            <option value="all" <?= $vehicle_type === 'all' ? 'selected' : '' ?>>All Vehicles</option>
                            <option value="Car" <?= $vehicle_type === 'Car' ? 'selected' : '' ?>>Car</option>
                            <option value="Motorcycle" <?= $vehicle_type === 'Motorcycle' ? 'selected' : '' ?>>Motorcycle</option>
                            <option value="Truck" <?= $vehicle_type === 'Truck' ? 'selected' : '' ?>>Truck</option>
                        </select>
                    </div>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Vehicle Type</th>
                                <th>Total Orders</th>
                                <th>Total Spent</th>
                                <th>Last Visit</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($customer = $customers->fetch_assoc()): ?>
                            <tr>
                                <td>#<?= $customer['id'] ?></td>
                                <td><?= htmlspecialchars($customer['name']) ?></td>
                                <td><?= htmlspecialchars($customer['phone']) ?></td>
                                <td><?= htmlspecialchars($customer['vehicle_type']) ?></td>
                                <td><?= $customer['total_orders'] ?></td>
                                <td>₱<?= number_format($customer['total_spent'] ?? 0, 2) ?></td>
                                <td><?= date('M d, Y', strtotime($customer['created_at'])) ?></td>
                                <td>
                                    <a href="view_customer.php?id=<?= $customer['id'] ?>" class="btn btn-sm btn-primary">
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
// Handle search and filter
let searchTimeout;
document.getElementById('search').addEventListener('input', function(e) {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        updateFilters();
    }, 500);
});

document.getElementById('vehicle-type').addEventListener('change', updateFilters);

function updateFilters() {
    const search = document.getElementById('search').value;
    const vehicleType = document.getElementById('vehicle-type').value;
    window.location.href = `customers.php?search=${encodeURIComponent(search)}&vehicle_type=${vehicleType}`;
}

// Handle customer deletion
function deleteCustomer(customerId) {
    if (confirm('Are you sure you want to delete this customer?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="customer_id" value="${customerId}">
            <input type="hidden" name="delete_customer" value="1">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php require_once '../includes/footer.php'; ?> 