<?php
require_once 'includes/functions.php';

$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $order_number = trim($_POST['order_number'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    $full_name = trim($first_name . ' ' . $last_name);
    
    if($full_name && $email && $message) {
        try {
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, order_number, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([$full_name, $email, $order_number, $message]);
            $success = "Thank you for contacting us! Our support team will get back to you shortly.";
        } catch(PDOException $e) {
            $error = "Failed to send message. Please try again later.";
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}

require_once 'includes/header.php';
?>

<div class="container" style="margin-top: 3rem; margin-bottom: 3rem;">
    <div style="max-width: 800px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 3rem;">
            <i class="ph-fill ph-envelope-open" style="font-size: 3rem; color: var(--accent-color); margin-bottom: 1rem;"></i>
            <h1 class="section-title" style="margin-bottom: 0.5rem;">Contact Us</h1>
            <p style="color: var(--text-muted); font-size: 1.1rem;">Have a question about an instrument or an order? We're here to help.</p>
        </div>

        <?php if($success): ?>
            <div class="alert alert-success" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 3rem; text-align: center; background: var(--surface-color); border-color: var(--border-color);">
                <i class="ph-fill ph-check-circle" style="font-size: 4rem; color: var(--success-text); margin-bottom: 1rem;"></i>
                <h3 style="margin-bottom: 0.5rem;">Message Sent</h3>
                <p style="color: var(--text-main); margin: 0;"><?php echo htmlspecialchars($success); ?></p>
                <a href="/melody-masters/index.php" class="btn btn-primary" style="margin-top: 1.5rem;">Return to Homepage</a>
            </div>
        <?php else: ?>
            <?php if($error): ?><div class="alert alert-error"><i class="ph ph-warning-circle"></i> <?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <div style="background: var(--surface-color); padding: 2.5rem; border-radius: var(--radius-lg); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
                <form action="/melody-masters/contact.php" method="POST">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>First Name</label>
                            <input type="text" name="first_name" class="form-control" required placeholder="John">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Last Name</label>
                            <input type="text" name="last_name" class="form-control" required placeholder="Doe">
                        </div>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-control" required placeholder="john@example.com">
                    </div>

                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label>Order Number (Optional)</label>
                        <input type="text" name="order_number" class="form-control" placeholder="#123456">
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 2rem;">
                        <label>Message</label>
                        <textarea name="message" class="form-control" rows="5" required placeholder="How can we help you?"></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%; height: 50px; font-size: 1.05rem;"><i class="ph ph-paper-plane-right"></i> Send Message</button>
                </form>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 2rem; margin-top: 3rem; text-align: center;">
                <div>
                    <i class="ph ph-phone" style="font-size: 1.5rem; color: var(--primary-color); margin-bottom: 0.5rem;"></i>
                    <strong style="display: block; margin-bottom: 0.25rem;">Phone Support</strong>
                    <span style="color: var(--text-muted); font-size: 0.95rem;">057-222-8822 (Mon-Fri)</span>
                </div>
                <div>
                    <i class="ph ph-envelope-simple" style="font-size: 1.5rem; color: var(--primary-color); margin-bottom: 0.5rem;"></i>
                    <strong style="display: block; margin-bottom: 0.25rem;">Email Us</strong>
                    <span style="color: var(--text-muted); font-size: 0.95rem;">support@melodymasters.com</span>
                </div>
                <div>
                    <i class="ph ph-map-pin" style="font-size: 1.5rem; color: var(--primary-color); margin-bottom: 0.5rem;"></i>
                    <strong style="display: block; margin-bottom: 0.25rem;">Store Location</strong>
                    <span style="color: var(--text-muted); font-size: 0.95rem;">Matara, Sri Lanka</span>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
