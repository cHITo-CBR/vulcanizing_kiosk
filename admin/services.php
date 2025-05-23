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

// Handle service actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_service'])) {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = floatval($_POST['price']);
        $duration = intval($_POST['duration']);
        $status = $_POST['status'];
        
        $stmt = $conn->prepare("INSERT INTO services (name, description, price, duration, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdis", $name, $description, $price, $duration, $status);
        
        if ($stmt->execute()) {
            $success = "Service added successfully";
        } else {
            $error = "Error adding service";
        }
    } elseif (isset($_POST['update_service'])) {
        $id = $_POST['service_id'];
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = floatval($_POST['price']);
        $duration = intval($_POST['duration']);
        $status = $_POST['status'];
        
        $stmt = $conn->prepare("UPDATE services SET name = ?, description = ?, price = ?, duration = ?, status = ? WHERE id = ?");
        $stmt->bind_param("ssdisi", $name, $description, $price, $duration, $status, $id);
        
        if ($stmt->execute()) {
            $success = "Service updated successfully";
        } else {
            $error = "Error updating service";
        }
    } elseif (isset($_POST['delete_service'])) {
        $id = $_POST['service_id'];
        
        // Check if service has orders
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM order_items WHERE service_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result['count'] > 0) {
            $error = "Cannot delete service with existing orders";
        } else {
            $stmt = $conn->prepare("DELETE FROM services WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $success = "Service deleted successfully";
            } else {
                $error = "Error deleting service";
            }
        }
    }
}

// Get services
$services = $conn->query("SELECT * FROM services ORDER BY name ASC");
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
                <a class="nav-link active" href="services.php">
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
                    <h3 class="card-title">Services Management</h3>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                        <i class="fas fa-plus"></i> Add New Service
                    </button>
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
                                <th>Description</th>
                                <th>Price</th>
                                <th>Duration (mins)</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($service = $services->fetch_assoc()): ?>
                            <tr>
                                <td>#<?= $service['id'] ?></td>
                                <td><?= htmlspecialchars($service['name']) ?></td>
                                <td><?= htmlspecialchars($service['description']) ?></td>
                                <td>₱<?= number_format($service['price'], 2) ?></td>
                                <td><?= $service['duration'] ?></td>
                                <td>
                                    <span class="badge bg-<?= $service['status'] === 'active' ? 'success' : 'danger' ?>">
                                        <?= ucfirst($service['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary" 
                                            onclick="editService(<?= htmlspecialchars(json_encode($service)) ?>)">
                                        <i class="fas fa-edit"></i>
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

<!-- Add Service Modal -->
<div class="modal fade" id="addServiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Service Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="price" class="form-label">Price (₱)</label>
                        <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="duration" class="form-label">Duration (minutes)</label>
                        <input type="number" class="form-control" id="duration" name="duration" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_service" class="btn btn-primary">Add Service</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Service Modal -->
<div class="modal fade" id="editServiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="service_id" id="edit_service_id">
                    
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Service Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_price" class="form-label">Price (₱)</label>
                        <input type="number" class="form-control" id="edit_price" name="price" step="0.01" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_duration" class="form-label">Duration (minutes)</label>
                        <input type="number" class="form-control" id="edit_duration" name="duration" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Status</label>
                        <select class="form-select" id="edit_status" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_service" class="btn btn-primary">Update Service</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Handle service editing
function editService(service) {
    document.getElementById('edit_service_id').value = service.id;
    document.getElementById('edit_name').value = service.name;
    document.getElementById('edit_description').value = service.description;
    document.getElementById('edit_price').value = service.price;
    document.getElementById('edit_duration').value = service.duration;
    document.getElementById('edit_status').value = service.status;
    
    new bootstrap.Modal(document.getElementById('editServiceModal')).show();
}

// Handle service deletion
function deleteService(serviceId) {
    if (confirm('Are you sure you want to delete this service?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="service_id" value="${serviceId}">
            <input type="hidden" name="delete_service" value="1">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php require_once '../includes/footer.php'; ?> 