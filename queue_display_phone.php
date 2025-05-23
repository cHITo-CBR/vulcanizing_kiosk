<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mobile Queue - Vulcanizing Kiosk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        :root {
            --bg-dark: #121212;
            --primary: #ff7b00;
            --primary-dark: #e06a00;
            --radius: 10px;
            --processing: #4CAF50;
            --pending: #FFC107;
            --completed: #2196F3;
            --text-light: #ffffff;
            --card-bg: rgba(255, 255, 255, 0.1);
        }

        body {
            background: var(--bg-dark);
            color: var(--text-light);
            font-family: 'Segoe UI', Arial, sans-serif;
            min-height: 100vh;
        }

        .header {
            background: rgba(0, 0, 0, 0.3);
            padding: 15px;
            text-align: center;
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(5px);
        }

        .header h1 {
            margin: 0;
            font-size: 1.5rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .nav-tabs {
            background: rgba(0, 0, 0, 0.2);
            border-bottom: none;
            display: flex;
            justify-content: space-around;
            padding: 0;
        }

        .nav-tabs .nav-link {
            border: none;
            color: var(--text-light);
            padding: 10px 0;
            width: 33.33%;
            text-align: center;
            border-radius: 0;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        .nav-tabs .nav-link.active {
            background-color: transparent;
            border-bottom: 3px solid var(--primary);
            color: var(--primary);
            font-weight: bold;
        }

        .tab-content {
            padding: 15px;
        }

        .queue-item {
            background: var(--card-bg);
            border-radius: var(--radius);
            padding: 15px;
            margin-bottom: 15px;
            animation: fadeIn 0.5s ease-out;
            border-left: 4px solid transparent;
        }

        .queue-item.processing { border-left-color: var(--processing); }
        .queue-item.pending { border-left-color: var(--pending); }
        .queue-item.completed { border-left-color: var(--completed); }

        .queue-number {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .processing .queue-number { color: var(--processing); }
        .pending .queue-number { color: var(--pending); }
        .completed .queue-number { color: var(--completed); }

        .customer-info p {
            margin: 5px 0;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
        }

        .customer-info i {
            width: 20px;
            margin-right: 10px;
        }

        .processing .customer-info i { color: var(--processing); }
        .pending .customer-info i { color: var(--pending); }
        .completed .customer-info i { color: var(--completed); }

        .qr-section {
            text-align: center;
            margin-top: 20px;
            padding: 15px;
            background: var(--card-bg);
            border-radius: var(--radius);
        }

        .qr-container {
            margin: 15px auto;
            max-width: 200px;
        }

        .qr-info {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .refresh-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            border: none;
            animation: pulse 2s infinite;
        }

        .last-updated {
            font-size: 0.8rem;
            text-align: center;
            color: rgba(255, 255, 255, 0.5);
            padding: 10px;
            margin-top: 20px;
        }

        .no-entries {
            text-align: center;
            padding: 30px 15px;
            color: rgba(255, 255, 255, 0.5);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .refreshing {
            animation: refresh 1s ease-in-out;
        }

        @keyframes refresh {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        .footer {
            text-align: center;
            padding: 15px;
            margin-top: 30px;
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.5);
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Vulcanizing Queue</h1>
    </div>

    <ul class="nav nav-tabs" id="queueTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="processing-tab" data-bs-toggle="tab" data-bs-target="#processing" type="button" role="tab">
                Processing <span class="badge bg-success processing-count">0</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
                Pending <span class="badge bg-warning text-dark pending-count">0</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed" type="button" role="tab">
                Completed <span class="badge bg-primary completed-count">0</span>
            </button>
        </li>
    </ul>

    <div class="tab-content" id="queueTabContent">
        <div class="tab-pane fade" id="processing" role="tabpanel">
            <div id="processing-list"></div>
        </div>
        <div class="tab-pane fade show active" id="pending" role="tabpanel">
            <div id="pending-list"></div>
        </div>
        <div class="tab-pane fade" id="completed" role="tabpanel">
            <div id="completed-list"></div>
        </div>
    </div>

    <div class="last-updated">
        Last updated: <span id="update-time"></span>
    </div>

    <button class="refresh-button" id="refresh-btn">
        <i class="fas fa-sync-alt"></i>
    </button>

    <div class="footer">
        © 2025 Vulcanizing Kiosk System
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Queue data management
        let queueData = {
            processing: [],
            pending: [],
            completed: []
        };

        // Render a single queue section
        function renderQueue(listId, orders, queueType) {
            const list = document.getElementById(listId);
            if (!orders || orders.length === 0) {
                list.innerHTML = '<div class="no-entries"><i class="fas fa-info-circle"></i><br>No entries in queue</div>';
                return;
            }
            
            list.innerHTML = orders.map(order => `
                <div class="queue-item ${queueType}">
                    <div class="queue-number">#${String(order.id).padStart(6, '0')}</div>
                    <div class="customer-info">
                        <p><i class="fas fa-user"></i> ${order.customer_name || 'Customer'}</p>
                        <p><i class="fas fa-car"></i> ${order.vehicle_type || 'Unknown Vehicle'}</p>
                        <p><i class="fas fa-clock"></i> ${formatTime(order.created_at)}</p>
                    </div>
                </div>
            `).join('');
        }

        // Format time properly
        function formatTime(timestamp) {
            const date = new Date(timestamp);
            return date.toLocaleTimeString('en-US', {
                hour: '2-digit', 
                minute: '2-digit',
                hour12: true
            });
        }

        // Update queue counts in tabs
        function updateQueueCounts() {
            document.querySelector('.processing-count').textContent = queueData.processing.length;
            document.querySelector('.pending-count').textContent = queueData.pending.length;
            document.querySelector('.completed-count').textContent = queueData.completed.length;
        }

        // Fetch queue data from server
        function refreshQueues() {
            document.body.classList.add('refreshing');
            
            // In production, use the API endpoint
            fetch('api/queue_full.php')
                .then(response => response.json())
                .then(data => {
                    queueData = data;
                    renderQueue('processing-list', data.processing, 'processing');
                    renderQueue('pending-list', data.pending, 'pending');
                    renderQueue('completed-list', data.completed, 'completed');
                    updateQueueCounts();
                    
                    // Update last updated time
                    const now = new Date();
                    document.getElementById('update-time').textContent = now.toLocaleTimeString();
                    
                    document.body.classList.remove('refreshing');
                })
                .catch(error => {
                    console.error('Error fetching queue data:', error);
                    document.body.classList.remove('refreshing');
                    
                    // For demo/development purposes
                    loadDemoData();
                });
        }

        // For development/demo only - remove in production
        function loadDemoData() {
            queueData = {
                processing: [
                    {id: 1001, customer_name: "John Doe", vehicle_type: "Sedan", created_at: new Date().toISOString()},
                    {id: 1002, customer_name: "Jane Smith", vehicle_type: "SUV", created_at: new Date().toISOString()}
                ],
                pending: [
                    {id: 1003, customer_name: "Bob Johnson", vehicle_type: "Pickup", created_at: new Date().toISOString()},
                    {id: 1004, customer_name: "Alice Brown", vehicle_type: "Hatchback", created_at: new Date().toISOString()},
                    {id: 1005, customer_name: "Mike Wilson", vehicle_type: "Van", created_at: new Date().toISOString()}
                ],
                completed: [
                    {id: 996, customer_name: "Sarah Miller", vehicle_type: "Motorcycle", created_at: new Date().toISOString()},
                    {id: 997, customer_name: "David Clark", vehicle_type: "Truck", created_at: new Date().toISOString()},
                    {id: 998, customer_name: "Emma Davis", vehicle_type: "Sedan", created_at: new Date().toISOString()}
                ]
            };
            
            renderQueue('processing-list', queueData.processing, 'processing');
            renderQueue('pending-list', queueData.pending, 'pending');
            renderQueue('completed-list', queueData.completed, 'completed');
            updateQueueCounts();
            
            const now = new Date();
            document.getElementById('update-time').textContent = now.toLocaleTimeString();
        }

        // Generate QR code for this page
        function generateQRCode() {
            const qrContainer = document.getElementById('qrcode');
            qrContainer.innerHTML = '';
            
            // Get the current URL
            const currentUrl = window.location.href;
            
            // Create QR code
            new QRCode(qrContainer, {
                text: currentUrl,
                width: 180,
                height: 180,
                colorDark: "#ffffff",
                colorLight: "#121212",
                correctLevel: QRCode.CorrectLevel.H
            });
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            // Initial load of queue data
            refreshQueues();
            
            // Generate QR code
            generateQRCode();
            
            // Set up refresh button
            document.getElementById('refresh-btn').addEventListener('click', refreshQueues);
            
            // Auto refresh every 30 seconds
            setInterval(refreshQueues, 30000);
        });
    </script>
</body>
</html>