<?php
require_once '../includes/header.php';
require_once '../config/database.php';

$database = new Database();
$conn = $database->getConnection();

// Fetch available services with error checking
$sql = "SELECT * FROM services WHERE status = 'active' ORDER BY name";
$result = $conn->query($sql);

// Debug information
if (!$result) {
    die("Error fetching services: " . $conn->error);
}

// Check if we have any services
$serviceCount = $result->num_rows;
?>

<div class="kiosk-container">
    <!-- Sidebar for Service Categories -->
    <div class="sidebar-categories">
        <button class="category-btn active" data-category="all">All Services</button>
       
       
    </div>

    <!-- Main Content -->
    <div class="kiosk-content">
        <!-- Debug Info -->
        <?php if ($serviceCount === 0): ?>
        <div class="alert alert-warning">
            No services available at the moment. Please check back later.
        </div>
        <?php endif; ?>

        <!-- Services Grid -->
        <div class="services-grid">
            <?php 
            if ($result && $result->num_rows > 0):
                while ($service = $result->fetch_assoc()): 
                    $cat = strtolower($service['category'] ?? 'other');
                    if (strpos($cat, 'tire') !== false) {
                        $catKey = 'tire';
                    } elseif (strpos($cat, 'wheel') !== false) {
                        $catKey = 'wheel';
                    } else {
                        $catKey = 'other';
                    }
            ?>
            <div class="service-card" data-category="<?= $catKey ?>">
                <div class="service-info">
                    <div class="service-header">
                        <h4><?= htmlspecialchars($service['name']) ?></h4>
                        <span class="service-duration">
                            <i class="fas fa-clock"></i> <?= $service['duration'] ?? '30' ?> min
                        </span>
                    </div>
                    <div class="service-price">
                        ₱<?= number_format($service['price'], 2) ?>
                    </div>
                    <?php if (!empty($service['description'])): ?>
                    <p class="service-description">
                        <?= htmlspecialchars($service['description']) ?>
                    </p>
                    <?php endif; ?>
                </div>
                <button class="add-btn" onclick="addToCart('<?= $service['id'] ?>', '<?= htmlspecialchars($service['name']) ?>', <?= $service['price'] ?>)">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
            <?php 
                endwhile;
            endif;
            ?>
        </div>
    </div>

    <!-- Cart Sidebar -->
    <div class="cart-sidebar">
        <div class="cart-header">
            <h3>Your Order</h3>
            <button class="clear-btn" onclick="clearCart()">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        <div id="cart-items" class="cart-items"></div>
        <div id="cart-total" class="cart-total"></div>
        <a href="checkout.php" class="checkout-btn" id="checkout-btn" style="display: none;">
            <i class="fas fa-shopping-cart"></i>
            Proceed to Checkout
        </a>
    </div>
</div>

<script>
// Cart functionality
let cart = JSON.parse(localStorage.getItem('cart')) || [];

function addToCart(id, name, price) {
    const existingItem = cart.find(item => item.id === id);
    
    if (existingItem) {
        existingItem.quantity++;
    } else {
        cart.push({ id, name, price, quantity: 1 });
    }
    
    updateCart();
    saveCart();
    
    // Show feedback
    showToast(`${name} added to cart`);
}

function removeFromCart(id) {
    cart = cart.filter(item => item.id !== id);
    updateCart();
    saveCart();
}

function updateQuantity(id, change) {
    const item = cart.find(item => item.id === id);
    if (item) {
        item.quantity += change;
        if (item.quantity <= 0) {
            removeFromCart(id);
        } else {
            updateCart();
            saveCart();
        }
    }
}

function clearCart() {
    cart = [];
    updateCart();
    saveCart();
    showToast('Cart cleared');
}

function saveCart() {
    localStorage.setItem('cart', JSON.stringify(cart));
}

function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

function updateCart() {
    const cartItems = document.getElementById('cart-items');
    const cartTotal = document.getElementById('cart-total');
    const checkoutBtn = document.getElementById('checkout-btn');
    
    if (cart.length === 0) {
        cartItems.innerHTML = '<p class="text-muted">Your cart is empty</p>';
        cartTotal.innerHTML = '';
        checkoutBtn.style.display = 'none';
        return;
    }
    
    let total = 0;
    cartItems.innerHTML = cart.map(item => {
        const itemTotal = item.price * item.quantity;
        total += itemTotal;
        return `
            <div class="cart-item">
                <div class="cart-item-info">
                    <h6 class="mb-0">${item.name}</h6>
                    <small class="text-muted">₱${item.price.toFixed(2)} x ${item.quantity}</small>
                </div>
                <div class="cart-item-actions">
                    <button class="btn btn-sm btn-outline-secondary" onclick="updateQuantity('${item.id}', -1)">-</button>
                    <span class="mx-2">${item.quantity}</span>
                    <button class="btn btn-sm btn-outline-secondary" onclick="updateQuantity('${item.id}', 1)">+</button>
                    <button class="btn btn-sm btn-danger ms-2" onclick="removeFromCart('${item.id}')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;
    }).join('');
    
    cartTotal.innerHTML = `
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Total:</h5>
            <h5 class="mb-0">₱${total.toFixed(2)}</h5>
        </div>
    `;
    
    checkoutBtn.style.display = 'block';
}

// Notification system
let lastQueueNumber = null;

// Audio notification
const queueSound = new Audio('../assets/sounds/notification.mp3');

// Countdown timer
let countdownInterval = null;

function startCountdown(minutes) {
    if (countdownInterval) {
        clearInterval(countdownInterval);
    }
    
    let timeLeft = minutes * 60;
    const countdownElement = document.getElementById('countdown');
    
    countdownInterval = setInterval(() => {
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        
        countdownElement.innerHTML = `
            <div class="countdown-timer">
                <span class="minutes">${minutes.toString().padStart(2, '0')}</span>:
                <span class="seconds">${seconds.toString().padStart(2, '0')}</span>
            </div>
        `;
        
        if (timeLeft <= 0) {
            clearInterval(countdownInterval);
            countdownElement.innerHTML = '<p class="text-muted">Estimated time completed</p>';
        }
        
        timeLeft--;
    }, 1000);
}

// Enhanced notification system
function showNotification(title, message, playSound = true) {
    if (!("Notification" in window)) {
        return;
    }

    if (Notification.permission === "granted") {
        new Notification(title, {
            body: message,
            icon: '../assets/images/logo.png'
        });
        
        if (playSound) {
            queueSound.play().catch(error => console.log('Error playing sound:', error));
        }
    } else if (Notification.permission !== "denied") {
        Notification.requestPermission().then(permission => {
            if (permission === "granted") {
                new Notification(title, {
                    body: message,
                    icon: '../assets/images/logo.png'
                });
                
                if (playSound) {
                    queueSound.play().catch(error => console.log('Error playing sound:', error));
                }
            }
        });
    }
}

// Queue display functionality
function updateQueue() {
    fetch('../api/queue.php')
        .then(response => response.json())
        .then(data => {
            const queueDisplay = document.getElementById('queue-display');
            const countersGrid = queueDisplay.querySelector('.counters-grid');
            const nextNumbersGrid = queueDisplay.querySelector('.next-numbers-grid');
            
            // Update counters
            countersGrid.innerHTML = '';
            for (let i = 1; i <= 2; i++) { // Assuming 2 counters
                const counter = data.counters[i] || null;
                countersGrid.innerHTML += `
                    <div class="counter-card ${counter ? 'active' : 'inactive'}">
                        <div class="counter-header">
                            <h3>Counter ${i}</h3>
                            ${counter ? `
                                <span class="priority-badge ${counter.priority_level}">
                                    ${counter.priority_level.toUpperCase()}
                                </span>
                            ` : ''}
                        </div>
                        <div class="counter-content">
                            ${counter ? `
                                <div class="queue-number">${counter.queue_number}</div>
                                <div class="customer-info">
                                    <p><i class="fas fa-user"></i> ${counter.customer_name}</p>
                                    <p><i class="fas fa-car"></i> ${counter.vehicle_type}</p>
                                    <p><i class="fas fa-clock"></i> Wait: ${counter.wait_time} min</p>
                                </div>
                            ` : `
                                <div class="no-queue">No active queue</div>
                            `}
                        </div>
                    </div>
                `;
            }
            
            // Update next numbers
            nextNumbersGrid.innerHTML = data.next_numbers.map(item => `
                <div class="next-number-card ${item.priority_level}">
                    <div class="next-number-header">
                        <span class="queue-number">${item.queue_number}</span>
                        <span class="priority-badge ${item.priority_level}">
                            ${item.priority_level.toUpperCase()}
                        </span>
                    </div>
                    <div class="next-number-info">
                        <p><i class="fas fa-user"></i> ${item.customer_name}</p>
                        <p><i class="fas fa-car"></i> ${item.vehicle_type}</p>
                    </div>
                </div>
            `).join('');
            
            // Start countdown for estimated wait time
            startCountdown(Math.round(data.estimated_wait));
        })
        .catch(error => {
            console.error('Error fetching queue:', error);
        });
}

// Initialize
updateCart();
updateQueue();

// Update queue every 10 seconds
setInterval(updateQueue, 10000);

// Initialize notifications
if ("Notification" in window) {
    Notification.requestPermission();
}

// Category filtering
const categoryMap = {
    'all': null,
    'tire': ['tire', 'tire services', 'tire service'],
    'wheel': ['wheel', 'wheel services', 'wheel service'],
    'other': ['other', 'other services', 'other service']
};

document.querySelectorAll('.category-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelector('.category-btn.active').classList.remove('active');
        btn.classList.add('active');
        const category = btn.dataset.category;
        document.querySelectorAll('.service-card').forEach(card => {
            if (category === 'all' || card.dataset.category === category) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    });
});
</script>

<style>
    
.kiosk-container {
    display: grid;
    grid-template-columns: 220px 2fr 1fr;
    gap: 1rem;
    height: calc(100vh - 60px);
    padding: 1rem;
    background:rgb(236, 241, 246);
}

.sidebar-categories {
    background: #fff;
    border-radius: 1rem;
    padding: 1.5rem 1rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    gap: 1rem;
    align-items: stretch;
    height: fit-content;
}

.category-btn {
    padding: 0.75rem 1rem;
    border: none;
    border-radius: 0.5rem;
    background: #f8f9fa;
    color: #495057;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 1.1rem;
    text-align: left;
}

.category-btn.active {
    background: #495057;
    color: #fff;
}

.kiosk-content {
    background: #fff;
    border-radius: 1rem;
    padding: 1rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow-y: auto;
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1rem;
    padding: 0.5rem;
}

.service-card {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 1rem;
    background: #fff;
    border-radius: 0.5rem;
    transition: all 0.2s;
    border: 1px solid #e9ecef;
}

.service-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    border-color: #28a745;
}

.service-info {
    flex: 1;
    margin-right: 1rem;
}

.service-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.5rem;
}

.service-header h4 {
    margin: 0;
    font-size: 1.1rem;
    color: #212529;
    font-weight: 600;
}

.service-duration {
    font-size: 0.8rem;
    color: #6c757d;
    background: #f8f9fa;
    padding: 0.25rem 0.5rem;
    border-radius: 1rem;
    white-space: nowrap;
}

.service-price {
    font-size: 1.2rem;
    font-weight: bold;
    color: #28a745;
    margin-bottom: 0.5rem;
}

.service-description {
    font-size: 0.9rem;
    color: #6c757d;
    margin: 0;
    line-height: 1.4;
}

.add-btn {
    width: 36px;
    height: 36px;
    border: none;
    border-radius: 50%;
    background:rgb(65, 73, 81);
    color: #fff;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.add-btn:hover {
    transform: scale(1.1);
    background: #218838;
}

.add-btn i {
    font-size: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
}

.cart-sidebar {
    background: #fff;
    border-radius: 1rem;
    padding: 1rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
}

.cart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.clear-btn {
    background: none;
    border: none;
    color: #dc3545;
    cursor: pointer;
}

.cart-items {
    flex: 1;
    overflow-y: auto;
}

.cart-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    border-bottom: 1px solid #dee2e6;
}

.cart-item-info {
    flex: 1;
    margin-right: 1rem;
}

.cart-item-actions {
    display: flex;
    align-items: center;
}

.cart-item-actions button {
    padding: 0.25rem 0.5rem;
}

.cart-item-actions span {
    min-width: 2rem;
    text-align: center;
}

.cart-total {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 2px solid #dee2e6;
}

.checkout-btn {
    margin-top: 1rem;
    padding: 0.75rem;
    background: #28a745;
    color: #fff;
    text-align: center;
    text-decoration: none;
    border-radius: 0.5rem;
    transition: all 0.2s;
}

.checkout-btn:hover {
    background: #218838;
    color: #fff;
}

@media (max-width: 1200px) {
    .kiosk-container {
        grid-template-columns: 1fr 1fr;
    }
    .queue-display {
        grid-column: 1 / -1;
    }
}

@media (max-width: 768px) {
    .kiosk-container {
        grid-template-columns: 1fr;
    }
    .cart-sidebar {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        border-radius: 1rem 1rem 0 0;
        z-index: 1000;
    }
}

.toast {
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 12px 24px;
    border-radius: 4px;
    z-index: 1000;
    animation: fadeInOut 3s ease-in-out;
}

@keyframes fadeInOut {
    0% { opacity: 0; transform: translate(-50%, 20px); }
    10% { opacity: 1; transform: translate(-50%, 0); }
    90% { opacity: 1; transform: translate(-50%, 0); }
    100% { opacity: 0; transform: translate(-50%, -20px); }
}
</style>

<?php require_once '../includes/footer.php'; ?> 