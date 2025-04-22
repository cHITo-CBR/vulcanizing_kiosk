<?php
// Database Connection
$conn = new mysqli("localhost", "root", "", "vulcanizing_shop");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the ID is provided
if (!isset($_GET['id'])) {
    die("Invalid request.");
}

$purchased_id = $_GET['id'];

// Fetch the current status
$sql = "SELECT service_status FROM queue_status WHERE purchased_service_id = $purchased_id";
$result = $conn->query($sql);
$current_status = $result->fetch_assoc()['service_status'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_status = $_POST['status'];

    // Update the status
    $update_sql = "UPDATE queue_status SET service_status = '$new_status' WHERE purchased_service_id = $purchased_id";
    if ($conn->query($update_sql) === TRUE) {
        echo "<script>alert('Status updated successfully!'); window.location.href = 'admin_dashboard.php';</script>";
    } else {
        echo "<script>alert('Failed to update status.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Status</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Update Service Status</h2>
        <form method="POST">
            <div class="mb-3">
                <label for="status" class="form-label">New Status</label>
                <select class="form-select" id="status" name="status" required>
                    <option value="pending" <?= $current_status === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="in_progress" <?= $current_status === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                    <option value="completed" <?= $current_status === 'completed' ? 'selected' : '' ?>>Completed</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update Status</button>
        </form>
    </div>
</body>
</html>