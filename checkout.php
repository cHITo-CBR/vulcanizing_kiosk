<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "vulcanizing_shop");
if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

// Fetch ordered services
$result = $conn->query("SELECT * FROM orders ORDER BY id DESC");

$total = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Your Order</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #fff;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 22px;
            font-weight: bold;
            padding-bottom: 10px;
        }
        .order-container {
            max-width: 600px;
            margin: auto;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            background: #fff;
        }
        .order-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px;
            border-bottom: 1px solid #ddd;
        }
        .order-item img {
            width: 50px;
            height: 50px;
            border-radius: 5px;
        }
        .order-actions {
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 10px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
        .edit-btn { background: green; color: white; }
        .remove-btn { background: red; color: white; }
        .footer {
            display: flex;
            justify-content: space-between;
            padding-top: 20px;
        }
        .total { font-size: 20px; font-weight: bold; }
        .go-back, .proceed {
            padding: 15px;
            font-size: 18px;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
        }
        .go-back { background: red; color: white; }
        .proceed { background: green; color: white; }
    </style>
</head>
<body>

<div class="order-container">
    <div class="header">Review Your Order</div>

    <?php while ($row = $result->fetch_assoc()) : ?>
        <div class="order-item">
            <div>
                <img src="<?= $row['image'] ?>" alt="<?= $row['service_name'] ?>">
                <div><?= $row['service_name'] ?></div>
                <small>₱<?= number_format($row['price'], 2) ?></small>
            </div>
            <div class="order-actions">
                <button class="btn edit-btn">Edit</button>
                <button class="btn remove-btn">Remove</button>
            </div>
        </div>
        <?php $total += $row['price']; ?>
    <?php endwhile; ?>

    <div class="footer">
        <div class="total">Total: ₱<?= number_format($total, 2) ?></div>
    </div>

    <div class="footer">
        <button class="go-back" onclick="window.history.back()">Go Back</button>
        <button class="proceed">Proceed to Payment</button>
    </div>
</div>

</body>
</html>
