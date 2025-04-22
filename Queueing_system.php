<?php
session_start();
$conn = new mysqli("localhost", "root", "", "vulcanizing_shop");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create queue table if it doesn't exist
$conn->query("CREATE TABLE IF NOT EXISTS queue_status (
    id INT PRIMARY KEY AUTO_INCREMENT,
    queue_number INT NOT NULL DEFAULT 0
)");

// Get current queue from database
$result = $conn->query("SELECT queue_number FROM queue_status ORDER BY id DESC LIMIT 1");
$row = $result->fetch_assoc();
$current_queue = $row ? $row['queue_number'] : 0;

// Handle queue actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['next'])) {
        $current_queue++;
    } elseif (isset($_POST['reset'])) {
        $current_queue = 0;
    }

    // Update queue number in the database
    $stmt = $conn->prepare("INSERT INTO queue_status (queue_number) VALUES (?)");
    $stmt->bind_param("i", $current_queue);
    $stmt->execute();
    $stmt->close();

    // Refresh page to show the updated queue number
    header("Location: queue.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Queueing System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white flex flex-col items-center justify-center h-screen">
    <div class="text-center">
        <h1 class="text-4xl font-bold mb-4">Now Serving</h1>
        <div class="text-6xl font-extrabold bg-blue-600 text-white px-10 py-5 rounded-lg shadow-lg">
            <?= htmlspecialchars($current_queue); ?>
        </div>
        <form method="POST" class="mt-6">
            <button name="next" class="bg-green-500 px-6 py-3 text-xl rounded-lg shadow-lg hover:bg-green-700">Next</button>
            <button name="reset" class="bg-red-500 px-6 py-3 text-xl rounded-lg shadow-lg hover:bg-red-700 ml-4">Reset</button>
        </form>
    </div>
</body>
</html>
