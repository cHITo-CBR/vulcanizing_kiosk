<?php require_once '../includes/header.php'; ?>

<div class="queue-bulletin-container">
    <h2 class="text-center mb-4">Queue Bulletin</h2>
    <div class="queue-bulletin-grid">
        <div class="queue-section processing">
            <h3>Processing</h3>
            <ul>
                <li><span class="queue-num">202405010001</span> <span class="customer">Juan Dela Cruz</span></li>
                <!-- Add more processing customers here -->
            </ul>
        </div>
        <div class="queue-section pending">
            <h3>Pending</h3>
            <ul>
                <li><span class="queue-num">202405010002</span> <span class="customer">Maria Santos</span></li>
                <li><span class="queue-num">202405010003</span> <span class="customer">Pedro Reyes</span></li>
                <!-- Add more pending customers here -->
            </ul>
        </div>
        <div class="queue-section completed">
            <h3>Completed</h3>
            <ul>
                <li><span class="queue-num">202405010000</span> <span class="customer">Ana Lopez</span></li>
                <!-- Add more completed customers here -->
            </ul>
        </div>
    </div>
</div>

<style>
.queue-bulletin-container {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 1rem;
}
.queue-bulletin-grid {
    display: flex;
    gap: 2rem;
    justify-content: center;
    flex-wrap: wrap;
}
.queue-section {
    background: #fff;
    border-radius: 1rem;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    padding: 2rem 1.5rem;
    min-width: 300px;
    flex: 1 1 300px;
    max-width: 350px;
    text-align: center;
}
.queue-section h3 {
    color: #28a745;
    margin-bottom: 1rem;
    font-size: 1.5rem;
}
.queue-section ul {
    list-style: none;
    padding: 0;
    margin: 0;
}
.queue-section li {
    font-size: 1.2rem;
    margin-bottom: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8f9fa;
    border-radius: 0.5rem;
    padding: 0.75rem 1rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.04);
}
.queue-num {
    font-weight: bold;
    color: #007bff;
    margin-right: 0.5rem;
}
.customer {
    color: #495057;
}
@media (max-width: 900px) {
    .queue-bulletin-grid {
        flex-direction: column;
        gap: 1.5rem;
    }
    .queue-section {
        max-width: 100%;
    }
}
/* Hide any scrollbars on body */
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
}
</style>

<?php require_once '../includes/footer.php'; ?> 