// Cart Management
let cart = JSON.parse(localStorage.getItem('cart')) || [];

function addToCart(serviceId, name, price) {
    const existingItem = cart.find(item => item.id === serviceId);
    
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({
            id: serviceId,
            name: name,
            price: price,
            quantity: 1
        });
    }
    
    updateCart();
    saveCart();
}

function removeFromCart(serviceId) {
    cart = cart.filter(item => item.id !== serviceId);
    updateCart();
    saveCart();
}

function updateQuantity(serviceId, quantity) {
    const item = cart.find(item => item.id === serviceId);
    if (item) {
        item.quantity = parseInt(quantity);
        if (item.quantity <= 0) {
            removeFromCart(serviceId);
        }
    }
    updateCart();
    saveCart();
}

function updateCart() {
    const cartItems = document.getElementById('cart-items');
    const cartTotal = document.getElementById('cart-total');
    
    if (cartItems) {
        cartItems.innerHTML = cart.map(item => `
            <div class="cart-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">${item.name}</h5>
                        <p class="mb-0">₱${item.price}</p>
                    </div>
                    <div class="d-flex align-items-center">
                        <input type="number" class="form-control form-control-sm me-2" 
                               style="width: 60px" value="${item.quantity}" 
                               onchange="updateQuantity('${item.id}', this.value)">
                        <button class="btn btn-danger btn-sm" onclick="removeFromCart('${item.id}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
    }
    
    if (cartTotal) {
        const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        cartTotal.innerHTML = `Total: ₱${total.toFixed(2)}`;
    }
}

function saveCart() {
    localStorage.setItem('cart', JSON.stringify(cart));
}

function clearCart() {
    cart = [];
    saveCart();
    updateCart();
}

// Queue Management
function updateQueueStatus() {
    fetch('/vulcanizing_kiosk/api/queue.php')
        .then(response => response.json())
        .then(data => {
            const queueDisplay = document.getElementById('queue-display');
            if (queueDisplay) {
                queueDisplay.innerHTML = `
                    <div class="queue-number">
                        ${data.current_number || 'No active queue'}
                    </div>
                `;
            }
        })
        .catch(error => console.error('Error:', error));
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateCart();
    
    // Update queue status every 30 seconds
    setInterval(updateQueueStatus, 30000);
    updateQueueStatus();
}); 