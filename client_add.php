<?php
ob_start();
$servername = "localhost";
$username = "root";
$password = "";
$database = "vulcanizing_shop";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $vehicle_type = trim($_POST['vehicle_type']);

    if (!empty($name) && !empty($phone) && !empty($vehicle_type)) {
        $stmt = $conn->prepare("INSERT INTO customers (name, phone, vehicle_type) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $phone, $vehicle_type);
        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            header("Location: services.php");
            ob_end_flush();
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
    } else {
        echo "<script>alert('Please fill in all fields');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Vulcanizing Shop</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background: radial-gradient(circle, rgba(30, 30, 30, 0.9) 20%, rgba(15, 15, 15, 1) 100%),
                        url('metallic1.jpg');
            background-size: cover;
            background-position: center;
            text-align: center;
            color: #FFD700;
        }

        .container {
            background: rgba(45, 45, 45, 0.95);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0px 0px 25px rgba(255, 165, 0, 0.7);
            width: 350px;
        }

        .title {
            font-size: 28px;
            font-weight: 600;
            color: #FFA500;
            text-shadow: 0px 0px 10px #FFA500;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
            position: relative;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #FFA500;
            border-radius: 8px;
            background: rgba(30, 30, 30, 0.9);
            color: white;
            font-size: 16px;
            outline: none;
            transition: 0.3s;
        }

        .form-group select {
            cursor: pointer;
        }

        .form-group input:focus, .form-group select:focus {
            border-color: #FFD700;
            box-shadow: 0px 0px 10px rgba(255, 215, 0, 0.8);
        }

        .register-button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(145deg, #FFA500, #FF5733);
            color: black;
            font-size: 18px;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            box-shadow: 0px 0px 20px rgba(255, 165, 0, 0.8);
            transition: all 0.3s ease-in-out;
        }

        .register-button:hover {
            background: linear-gradient(145deg, #FFD700, #FF4500);
            box-shadow: 0px 0px 30px rgba(255, 165, 0, 1);
            transform: scale(1.05);
        }

        .back-link {
            margin-top: 15px;
            display: block;
            color: #FFA500;
            font-size: 14px;
            text-decoration: none;
            transition: 0.3s;
        }

        .back-link:hover {
            color: #FFD700;
            text-shadow: 0px 0px 10px #FFD700;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="title">Register</h1>
        <form method="POST">
            <div class="form-group">
                <input type="text" name="name" placeholder="Enter your name" required>
            </div>
            <div class="form-group">
                <input type="text" name="phone" placeholder="Enter your phone number" required>
            </div>
            <div class="form-group">
                <select name="vehicle_type" required>
                    <option value="" disabled selected>Select your vehicle type</option>
                    <option value="Car">Car</option>
                    <option value="Motorcycle">Motorcycle</option>
                    <option value="Truck">Truck</option>
                </select>
            </div>
            <button type="submit" class="register-button">Register & Proceed</button>
        </form>
        <a href="index.php" class="back-link">← Back to Home</a>
    </div>
</body>
</html>
