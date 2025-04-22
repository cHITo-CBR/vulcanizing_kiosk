<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vulcanizing Shop</title>
    <style>
         body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background: radial-gradient(circle, rgba(30, 30, 30, 0.9) 20%, rgba(15, 15, 15, 1) 100%), 
                        url('metallic1.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            text-align: center;
            font-family: 'Poppins', sans-serif;
            color: #FFD700;
            overflow: hidden;
        }
        .container {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 50px;
            background: rgba(45, 45, 45, 0.95);
            border: 2px solid #555;
            border-radius: 15px;
            box-shadow: 0px 0px 20px rgba(255, 165, 0, 0.7);
        }
        .title {
            font-size: 48px;
            font-weight: bold;
            color: #FFA500;
            text-shadow: 0px 0px 15px #FFA500;
            margin-bottom: 20px;
        }
        .start-button {
            padding: 20px 50px;
            background: linear-gradient(145deg, #FFD700, #FF4500);
            color: black;
            font-size: 24px;
            font-weight: bold;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            box-shadow: 0px 0px 20px rgba(255, 165, 0, 0.8);
            transition: all 0.3s ease-in-out;
        }
        .start-button:hover {
            background: linear-gradient(145deg, #FF8C00, #FF4500);
            box-shadow: 0px 0px 30px rgba(255, 165, 0, 1);
            transform: scale(1.1);
        }
        .glow-ring {
            position: absolute;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: rgba(255, 140, 0, 0.1);
            box-shadow: 0px 0px 50px rgba(255, 140, 0, 0.5);
            animation: pulse 1.5s infinite alternate;
        }
        @keyframes pulse {
            from {
                transform: scale(1);
                opacity: 0.8;
            }
            to {
                transform: scale(1.2);
                opacity: 0.5;
            }
        }
        
    </style>
</head>
<body>
    <div class="container">
        <div class="glow-ring"></div>
        <h1 class="title">VULCANIZING SHOP</h1>
        <button class="start-button" onclick="redirectToClientAdd()">TAP TO START</button>
    </div>

    <script>
        function redirectToClientAdd() {
            window.location.href = "client_add.php";
        }
    </script>
</body>
</html>
