<?php
// Database Connection
$conn = new mysqli("localhost", "root", "", "vulcanizing_shop");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch orders with services & queue status
$sql = "SELECT 
            ps.id AS purchased_id, ps.service_name, ps.price, ps.quantity, ps.total_price, ps.created_at AS order_date,
            c.name AS customer_name,
            qs.queue_number, qs.service_status
        FROM purchased_services ps
        LEFT JOIN customers c ON ps.order_id = c.id
        LEFT JOIN queue_status qs ON ps.id = qs.purchased_service_id
        ORDER BY ps.id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Admin Dashboard</h2>
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Purchased ID</th>
                    <th>Customer Name</th>
                    <th>Order Date</th>
                    <th>Service Name</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total Price</th>
                    <th>Queue Number</th>
                    <th>Service Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?= $row['purchased_id'] ?></td>
                    <td><?= $row['customer_name'] ?: 'N/A' ?></td>
                    <td><?= $row['order_date'] ?></td>
                    <td><?= $row['service_name'] ?></td>
                    <td>₱<?= $row['price'] ?></td>
                    <td><?= $row['quantity'] ?></td>
                    <td>₱<?= $row['total_price'] ?></td>
                    <td><?= $row['queue_number'] ?: 'N/A' ?></td>
                    <td><?= ucfirst($row['service_status'] ?: 'pending') ?></td>
                    <td>
                        <a href="update_status.php?id=<?= $row['purchased_id'] ?>" class="btn btn-primary btn-sm">Update Status</a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>