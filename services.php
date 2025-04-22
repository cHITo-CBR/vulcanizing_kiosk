<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "vulcanizing_shop");
if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

// Get the latest service number (using the latest client ID)
$result = $conn->query("SELECT id FROM customers ORDER BY id DESC LIMIT 1");
$service_number = ($result->num_rows > 0) ? $result->fetch_assoc()['id'] : 1;

// Fetch the latest customer data (assuming customer is logged in or selected)
$result = $conn->query("SELECT name, phone, vehicle_type FROM customers ORDER BY created_at DESC LIMIT 1");
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $vehicle_type = $row['vehicle_type']; // Store vehicle type
} else {
    $vehicle_type = "Car"; // Default value if no customer is found
}
$result = $conn->query("SELECT id FROM customers ORDER BY id DESC LIMIT 1");
if (!$result) {
    die("Query failed: " . $conn->error);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vulcanizing Kiosk</title>
    <style>
        /* Your existing CSS styles */
        body {
            display: flex;
            flex-direction: column;
            margin: 0;
            font-family: Arial, sans-serif;
            background: #1E1E1E; /* Dark background */
            color: #FFD700; /* Golden text */
            height: 100vh;
            overflow: hidden;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #2D2D2D; /* Dark gray */
            padding: 15px 30px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.5);
        }

        .header h1 {
            margin: 0;
            font-size: 40px;
            text-transform: uppercase;
            text-align: center;
            color: #FFD700; /* Golden text */
        }

        .service-number {
            text-align: center;
            padding: 0px 95px;
        }

        .service-number small {
            display: block;
            font-size: 20px;
            color: #FFA500; /* Orange */
        }

        .service-number span {
            font-size: 60px;
            font-weight: bold;
            color: #FFD700;
        }

        /* Sidebar */
        .sidebar {
            width: 320px; /* Wider sidebar for a more elegant look */
            background: #2D2D2D;
            padding: 20px;
            height: calc(100vh - 60px);
            position: fixed;
            left: 0;
            top: 120px;
            overflow-y: auto;
            border-right: 5px solid #FFD700;
            box-shadow: 5px 0px 10px rgba(255, 215, 0, 0.5);
        }

        .sidebar h2 {
            color: #FFA500;
            text-transform: uppercase;
            margin-bottom: 20px;
            font-size: 26px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            padding: 15px;
            margin: 10px 0;
            background: #3D3D3D;
            cursor: pointer;
            border-radius: 10px;
            transition: 0.3s;
            color: white;
            font-weight: bold;
            font-size: 18px;
            text-align: center;
        }

        .sidebar ul li:hover {
            background: #FFA500;
            color: #1E1E1E;
            transform: scale(1.05);
        }

        .checkout-btn {
            margin-top: 20px;
            padding: 15px;
            background: #FF5733;
            text-align: center;
            border-radius: 10px;
            cursor: pointer;
            font-weight: bold;
            color: white;
            transition: 0.3s;
            font-size: 20px;
        }

        .checkout-btn:hover {
            background: #FFA500;
            color: black;
        }

        /* Main Content */
        .content {
            margin-left: 350px; /* Adjusted for wider sidebar */
            padding: 40px 30px;
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            justify-content: center;
        }

        /* Service Box */
        .service-box {
            background: #3D3D3D;
            padding: 30px;
            text-align: center;
            border-radius: 15px;
            width: 280px;
            height: 340px;
            cursor: pointer;
            transition: 0.3s;
            color: white;
            box-shadow: 0px 5px 15px rgba(255, 215, 0, 0.3);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
        }

        .service-box img {
            width: 240px;
            height: 180px;
            object-fit: cover;
            border-radius: 10px;
        }

        .service-box h3 {
            font-size: 22px;
            margin: 10px 0;
            color: #FFD700;
        }

        .service-box p {
            font-size: 20px;
            font-weight: bold;
            color: #FFA500;
        }

        .service-box:hover {
            background: #FF5733;
            transform: scale(1.05);
            box-shadow: 0px 5px 20px rgba(255, 87, 51, 0.5);
        }

        /* Footer */
        .footer {
            background: #2D2D2D;
            padding: 20px 0px;
            display: flex;
            justify-content: space-around;
            position: fixed;
            bottom: 0;
            width: 100%;
            box-shadow: 0px -4px 10px rgba(0, 0, 0, 0.5);
        }

        .footer .footer-box {
            flex: 1;
            text-align: center;
            padding: 15px;
            background: #3D3D3D;
            margin: 0 5px;
            border-radius: 10px;
            color: #FFD700;
            font-weight: bold;
            font-size: 24px;
        }

        /* Checkout Panel Styles */
        .checkout-panel {
            position: fixed;
            top: 0;
            right: -350px; /* Initially hidden */
            width: 350px;
            height: 100%;
            background: #222;
            color: #FFD700;
            box-shadow: -5px 0 15px rgba(255, 215, 0, 0.3);
            transition: 0.4s ease-in-out;
            display: flex;
            flex-direction: column;
            z-index: 1000;
            padding: 

        }

        .checkout-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: #333;
        }

        .checkout-header h2 {
            margin: 0;
        }

        .checkout-header button {
            background: none;
            border: none;
            font-size: 24px;
            color: white;
            cursor: pointer;
        }

        .checkout-content {
            flex-grow: 1;
            overflow-y: auto;
            padding: 20px;
        }

        .checkout-item {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            border-bottom: 1px solid #FFD700;
        }

        .checkout-footer {
            padding: 20px;
            background: #333;
            text-align: center;
        }

        .clear-btn {
            background: red;
            color: white;
            border: none;
            padding: 10px;
            width: 40%;
            cursor: pointer;
            font-size: 16px;
        }

        .confirm-btn {
            background: green;
            color: white;
            border: none;
            padding: 10px;
            width: 40%;
            cursor: pointer;
            font-size: 16px;
        }

        .clear-btn:hover {
            background: darkred;
        }

        .confirm-btn:hover {
            background: darkgreen;
        }

        /* Floating Receipt Styles */
.receipt {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 300px;
    background: #fff;
    color: #000;
    border-radius: 10px;
    box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.5);
    z-index: 1000;
}

.receipt-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background: #333;
    color: #fff;
    border-top-left-radius: 10px;
    border-top-right-radius: 10px;
}

.receipt-header h2 {
    margin: 0;
    font-size: 20px;
}

.receipt-header button {
    background: none;
    border: none;
    color: #fff;
    font-size: 24px;
    cursor: pointer;
}

.receipt-content {
    padding: 15px;
}

.receipt-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}

.receipt-total {
    margin-top: 15px;
    padding-top: 10px;
    border-top: 1px solid #ccc;
    text-align: right;
    font-size: 18px;
}

#barcode {
    margin-top: 15px;
    width: 100%;
}
/* Save Button Styles */
.save-btn {
    background: #4CAF50; /* Green */
    color: white;
    border: none;
    padding: 10px;
    width: 100%;
    cursor: pointer;
    font-size: 16px;
    margin-top: 15px;
    border-radius: 5px;
}

.save-btn:hover {
    background: #45a049; /* Darker green */
}
        </style>
    <!-- Include JsBarcode Library -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <h1>VulcaPro</h1>
        <div class="service-number">
            <small>Service Number</small>
            <span><?php echo $service_number; ?></span>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Services</h2>
        <ul>
            <li onclick="showServices('repair')">🛠️ Repair Services</li>
            <li onclick="showServices('installation')">🔧 Installation Services</li>
            <li onclick="showServices('alignment')">🎯 Wheel Alignment</li>
            <li onclick="showServices('other')">⚙️ Other Services</li>
        </ul>
        <button class="checkout-btn" onclick="openCheckout()">🛒 Checkout (<span id="cartCount">0</span>)</button>
    </div>

    <!-- Main Content -->
    <div class="content" id="service-container"></div>

    <!-- Checkout Side Panel -->
    <div id="checkoutPanel" class="checkout-panel">
        <div class="checkout-header">
            <h2>Checkout</h2>
            <button onclick="closeCheckout()">×</button>
        </div>
        <div id="checkoutContent" class="checkout-content"></div>
        <div class="checkout-footer">
            <h3>Total: ₱<span id="checkoutTotal">0</span></h3>
            <button class="clear-btn" onclick="clearCheckout()">Clear</button>
            <button class="confirm-btn" onclick="confirmOrder()">Confirm</button>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <?php
        $result = $conn->query("SELECT name, phone, vehicle_type FROM customers ORDER BY created_at DESC LIMIT 1");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo "<div class='footer-box'><strong>Name:</strong> " . $row['name'] . "</div>";
            echo "<div class='footer-box'><strong>Phone:</strong> " . $row['phone'] . "</div>";
            echo "<div class='footer-box'><strong>Vehicle:</strong> " . $row['vehicle_type'] . "</div>";
        } else {
            echo "<div class='footer-box'>No client registered yet.</div>";
        }
        ?>
    </div>

    <script>
        let selectedServices = [];

        // Function to open the checkout panel
        function openCheckout() {
            let checkoutContent = document.getElementById("checkoutContent");
            let checkoutTotal = document.getElementById("checkoutTotal");
            checkoutContent.innerHTML = ""; // Clear previous items

            let total = 0;
            selectedServices.forEach((service, index) => {
                let item = document.createElement("div");
                item.className = "checkout-item";
                item.innerHTML = `
                    <span>${service.name}</span>
                    <span>₱${service.price}</span>
                    <button onclick="deleteService(${index})">❌</button>
                `;
                checkoutContent.appendChild(item);
                total += service.price;
            });

            checkoutTotal.innerText = total;
            document.getElementById("checkoutPanel").style.right = "0"; // Show panel
        }

        // Function to close the checkout panel
        function closeCheckout() {
            document.getElementById("checkoutPanel").style.right = "-350px"; // Hide panel
        }

        // Function to delete a service from the checkout panel
        function deleteService(index) {
            selectedServices.splice(index, 1); // Remove the service from the array
            document.getElementById("cartCount").innerText = selectedServices.length; // Update cart count
            openCheckout(); // Refresh the checkout panel
        }

        // Function to clear all services from the checkout panel
        function clearCheckout() {
            selectedServices = []; // Clear the array
            document.getElementById("cartCount").innerText = 0; // Reset cart count
            openCheckout(); // Refresh the checkout panel
        }
        function saveReceipt(services, total) {
    console.log("Saving receipt with data:", { services, total }); // Debugging

    fetch('save_order.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            services: services,
            total: total
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json(); // Parse the response as JSON
    })
    .then(data => {
        if (data.success) {
            alert("Receipt saved successfully!");
        } else {
            alert("Failed to save receipt: " + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert("An error occurred. Please try again.");
    });
}
// Function to confirm the order and save it to the database
// Function to confirm the order and save it to the database
function confirmOrder() {
    let total = selectedServices.reduce((sum, service) => sum + service.price, 0);
    let services = selectedServices.map(service => ({
        name: service.name,
        price: service.price,
        quantity: service.quantity || 1 // Default quantity is 1
    }));

    if (services.length === 0) {
        alert("Your cart is empty. Please add services before confirming.");
        return;
    }

    // Send the data to the database
    fetch('save_order.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            services: services,
            total: total
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            closeCheckout();
            showReceipt(services, total);
            selectedServices = []; // Clear the selected services
            document.getElementById("cartCount").innerText = 0; // Reset cart count
        } else {
            alert("Failed to save the order: " + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert("An error occurred. Please try again.");
    });
}
// Function to show the receipt
function showReceipt(services, total, orderId) {
    // Create the receipt container
    let receipt = document.createElement("div");
    receipt.className = "receipt";
    receipt.innerHTML = `
        <div class="receipt-header">
            <h2>Receipt</h2>
            <button onclick="closeReceipt()">×</button>
        </div>
        <div class="receipt-content">
            <div style="text-align: center; margin-bottom: 10px;">
                <strong>Order #${orderId}</strong><br>
                <small>${new Date().toLocaleString()}</small>
            </div>
            ${services.map(service => `
                <div class="receipt-item">
                    <span>${service.name}</span>
                    <span>₱${service.price} x ${service.quantity || 1}</span>
                </div>
            `).join("")}
            <div class="receipt-total">
                <strong>Total:</strong> ₱${total}
            </div>
            <svg id="barcode"></svg>
            <div style="text-align: center; margin-top: 10px;">
                <p>Thank you for your business!</p>
            </div>
        </div>
        <div class="receipt-footer">
            <button class="save-btn" onclick="printReceipt()">Print Receipt</button>
        </div>
    `;

    // Append the receipt to the body
    document.body.appendChild(receipt);

    // Generate barcode
    JsBarcode("#barcode", `ORDER-${orderId}`, {
        format: "CODE128",
        lineColor: "#000",
        width: 2,
        height: 40,
        displayValue: true
    });
}

// Function to print the receipt
function printReceipt() {
    const receiptContent = document.querySelector(".receipt-content").innerHTML;
    const printWindow = window.open('', '', 'width=600,height=600');
    
    printWindow.document.open();
    printWindow.document.write(`
        <html>
            <head>
                <title>Receipt</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 20px; }
                    .receipt-item { display: flex; justify-content: space-between; margin-bottom: 5px; }
                    .receipt-total { margin-top: 15px; border-top: 1px solid #ccc; padding-top: 5px; text-align: right; }
                </style>
            </head>
            <body>
                <div>${receiptContent}</div>
            </body>
        </html>
    `);
    
    printWindow.document.close();
    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 500);
}

// Function to close the receipt
function closeReceipt() {
    let receipt = document.querySelector(".receipt");
    if (receipt) {
        receipt.remove();
    }
}
        // Function to show services based on category
        function showServices(category) {
            const services = {
                'repair': [
                    { name: 'Tire Patching', price: 150, image: 'images/tirepatching.jpg' },
                    { name: 'Rim Repair', price: 300, image: 'images/rim.webp' }
                ],
                'installation': [
                    { name: 'Tire Mounting', price: 100, image: 'images/mounting.jpg' },
                    { name: 'Dismounting', price: 100, image: 'images/dismounting.jpg' }
                ],
                'alignment': [
                    { name: 'Wheel Alignment', price: 500, image: 'images/wheelalign.webp' }
                ],
                'other': [
                    { name: 'Pressure Check', price: 50, image: 'images/pressurecheck.png' },
                    { name: 'Tube Replacement', price: 200, image: 'images/tube_patch_and_repair.webp' },
                    { name: 'Tire Replacement', price: 1000, image: 'images/tire.jpg' }
                ]
            };

            let container = document.getElementById("service-container");
            container.innerHTML = ""; // Clear previous services

            if (services[category]) {
                services[category].forEach(service => {
                    let box = document.createElement("div");
                    box.classList.add("service-box");
                    box.innerHTML = `
                        <img src="${service.image}" alt="${service.name}">
                        <h3>${service.name}</h3>
                        <p>₱${service.price}</p>
                    `;
                    box.onclick = () => addToCheckout(service.name, service.price);
                    container.appendChild(box);
                });
            } else {
                container.innerHTML = "<p>No services found.</p>";
            }
        }

        // Function to add a service to the checkout panel
        function addToCheckout(serviceName, servicePrice) {
            selectedServices.push({ name: serviceName, price: servicePrice }); // Add service to the array
            document.getElementById("cartCount").innerText = selectedServices.length; // Update cart count
            openCheckout(); // Refresh the checkout panel
        }

        // Make functions globally accessible
        window.showServices = showServices;
        window.addToCheckout = addToCheckout;
    </script>
</body>
</html>