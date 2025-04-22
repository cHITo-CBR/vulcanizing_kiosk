<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "vulcanizing_shop";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
    $services = $data['services'];
    $total = $data['total'];

    // Get the latest customer ID
    $result = $conn->query("SELECT id FROM customers ORDER BY id DESC LIMIT 1");
    $customer_id = ($result->num_rows > 0) ? $result->fetch_assoc()['id'] : null;

    if ($customer_id) {
        // Save each service to the purchased_services table
        foreach ($services as $service) {
            $service_name = $service['name'];
            $service_price = $service['price'];
            $quantity = $service['quantity'] ?? 1; // Default quantity is 1
            $total_price = $service_price * $quantity;

            $stmt = $conn->prepare("INSERT INTO purchased_services (customer_id, service_name, service_price, quantity, total_price) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issdi", $customer_id, $service_name, $service_price, $quantity, $total_price);
            if (!$stmt->execute()) {
                echo json_encode(["success" => false, "message" => "Error saving service: " . $stmt->error]);
                $stmt->close();
                $conn->close();
                exit();
            }
            $stmt->close();
        }

        echo json_encode(["success" => true, "message" => "Services saved successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "No customer found"]);
    }
}

$conn->close();
?>