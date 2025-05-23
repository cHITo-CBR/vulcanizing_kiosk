<footer class="footer">
    <div class="footer-content">
        <div class="footer-section">
            <h4>Vulcanizing Kiosk</h4>
            <p>Professional tire and wheel services for your vehicle.</p>
        </div>
        <div class="footer-section">
            <h4>Quick Links</h4>
            <ul>
                <li><a href="/kiosk/index.php">Order Services</a></li>
                <li><a href="/queue_display.php">Queue Display</a></li>
                <?php if (isset($_SESSION['admin_id'])): ?>
                <li><a href="/admin/dashboard.php">Admin Dashboard</a></li>
                <?php endif; ?>
            </ul>
        </div>
        <div class="footer-section">
            <h4>Contact Us</h4>
            <ul>
                <li><i class="fas fa-phone"></i> (123) 456-7890</li>
                <li><i class="fas fa-envelope"></i> chistopher.raper@vulcanizingkiosk.com</li>
                <li><i class="fas fa-map-marker-alt"></i> Butuan, City</li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> Vulcanizing Kiosk. All rights reserved.</p>
    </div>
</footer>

<style>
.footer {
    background: var(--bg-dark);
    color: #fff;
    padding: 3rem 0 1rem;
    margin-top: 3rem;
}

.footer-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
}

.footer-section h4 {
    color: var(--primary);
    font-size: 1.2rem;
    margin-bottom: 1rem;
    font-weight: 600;
}

.footer-section p {
    color: #adb5bd;
    line-height: 1.6;
    margin: 0;
}

.footer-section ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-section ul li {
    margin-bottom: 0.75rem;
}

.footer-section ul li a {
    color: #adb5bd;
    text-decoration: none;
    transition: color 0.2s;
}

.footer-section ul li a:hover {
    color: var(--primary);
}

.footer-section ul li i {
    width: 20px;
    margin-right: 0.5rem;
    color: var(--primary);
}

.footer-bottom {
    text-align: center;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.footer-bottom p {
    color: #adb5bd;
    margin: 0;
}

@media (max-width: 768px) {
    .footer {
        padding: 2rem 0 1rem;
    }

    .footer-content {
        grid-template-columns: 1fr;
        text-align: center;
    }

    .footer-section ul li i {
        margin-right: 0.5rem;
    }
}
</style>

<!-- Bootstrap JS and dependencies -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
</body>
</html>

