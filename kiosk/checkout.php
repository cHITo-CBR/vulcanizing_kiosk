<?php
require_once '../includes/header.php';
require_once '../config/database.php';

$database = new Database();
$conn = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $vehicle_type = trim($_POST['vehicle_type']);
    $cart = json_decode($_POST['cart'], true);
    
    if (!empty($name) && !empty($phone) && !empty($vehicle_type) && !empty($cart)) {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Insert customer
            $stmt = $conn->prepare("INSERT INTO customers (name, phone, vehicle_type) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $phone, $vehicle_type);
            $stmt->execute();
            $customer_id = $conn->insert_id;
            
            // Calculate total
            $total = 0;
            foreach ($cart as $item) {
                $total += $item['price'] * $item['quantity'];
            }
            
            // Insert order
            $stmt = $conn->prepare("INSERT INTO orders (customer_id, total_amount, status) VALUES (?, ?, 'pending')");
            $stmt->bind_param("id", $customer_id, $total);
            $stmt->execute();
            $order_id = $conn->insert_id;
            
            // Insert order items
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, service_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($cart as $item) {
                $stmt->bind_param("iiid", $order_id, $item['id'], $item['quantity'], $item['price']);
                $stmt->execute();
            }
            
            // Generate queue number
            $queue_number = date('Ymd') . str_pad($order_id, 4, '0', STR_PAD_LEFT);
            $stmt = $conn->prepare("INSERT INTO queue_status (order_id, queue_number, status) VALUES (?, ?, 'pending')");
            $stmt->bind_param("is", $order_id, $queue_number);
            $stmt->execute();
            
            // Commit transaction
            $conn->commit();
            
            // Clear cart and redirect to success page
            echo "<script>
                localStorage.removeItem('cart');
                window.location.href = 'success.php?queue=" . $queue_number . "';
            </script>";
            exit();
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $error = "Error processing order: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all fields and add items to cart";
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="card-title mb-0">Checkout</h2>
                    <button type="button" class="btn btn-outline-danger" id="cancel-order-btn">
                        <i class="fas fa-arrow-left me-2"></i> Cancel
                    </button>
                </div>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <form method="POST" id="checkout-form">
                    <input type="hidden" name="cart" id="cart-data">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="vehicle_type" class="form-label">Vehicle Type</label>
                        <select class="form-select" id="vehicle_type" name="vehicle_type" required>
                            <option value="">Select vehicle type</option>
                            <option value="Car">Car</option>
                            <option value="Motorcycle">Motorcycle</option>
                            <option value="Truck">Truck</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <h4>Order Summary</h4>
                        <div id="order-summary"></div>
                        <div id="order-total" class="cart-total mt-3"></div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-check-circle me-2"></i> Place Order
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const cartData = document.getElementById('cart-data');
    const orderSummary = document.getElementById('order-summary');
    const orderTotal = document.getElementById('order-total');
    
    // Update cart data
    cartData.value = JSON.stringify(cart);
    
    // Update order summary
    orderSummary.innerHTML = cart.map(item => `
        <div class="cart-item">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">${item.name}</h5>
                    <p class="mb-0">₱${item.price} × ${item.quantity}</p>
                </div>
                <div>
                    ₱${(item.price * item.quantity).toFixed(2)}
                </div>
            </div>
        </div>
    `).join('');
    
    // Update total
    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    orderTotal.innerHTML = `Total: ₱${total.toFixed(2)}`;

    // Cancel button logic
    const cancelBtn = document.getElementById('cancel-order-btn');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            localStorage.removeItem('cart');
            window.location.href = 'index.php';
        });
    }
});
</script>
<style>/* Hide any scrollbars on body */
body::-webkit-scrollbar {
    display: none;
}

/* Hide the footer if it exists */
footer {
    display: none !important;
}

/* Hide the header if it exists */
header {
    display: none !important;
}

/* Ensure any other fixed elements don't interfere */
.container-fluid, .container {
    padding: 0 !important;
    margin: 0 !important;
    max-width: 100% !important;
    width: 100% !important;
}

/* Remove any unwanted page padding/margins */
.main-content, main, .page-content {
    padding: 0 !important;
    margin: 0 !important;
}</style>

<?php require_once '../includes/footer.php'; ?> 