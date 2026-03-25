<?php
require_once '../includes/functions.php';
requireRole('Admin');

$error = '';
$success = '';
$action = $_GET['action'] ?? 'list';

// Handle delete message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_message'])) {
    $id = (int)$_POST['message_id'];
    $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
    if($stmt->execute([$id])) {
        $success = "Message has been successfully deleted.";
    } else {
        $error = "Failed to delete message.";
    }
}

// Handle mark as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
    $id = (int)$_POST['message_id'];
    $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?");
    $stmt->execute([$id]);
}

$stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
$messages = $stmt->fetchAll();

// Count unread messages
$stmtUnread = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0");
$unread_count = $stmtUnread->fetchColumn();

require_once '../includes/header.php';
?>

<div class="dashboard-layout">
        <?php include '../includes/admin_sidebar.php'; ?>

        <div class="dashboard-content" style="padding: 2.5rem;">
            <div class="dashboard-header" style="justify-content: space-between;">
                <div>
                    <h1 style="font-size: 1.5rem; margin-bottom: 0.5rem;">Contact Messages</h1>
                    <p style="color: var(--text-muted); margin: 0;">Review and manage inquiries submitted by customers.</p>
                </div>
            </div>

            <?php if($error): ?><div class="alert alert-error"><i class="ph ph-warning-circle"></i> <?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <?php if($success): ?><div class="alert alert-success"><i class="ph ph-check-circle"></i> <?php echo htmlspecialchars($success); ?></div><?php endif; ?>

            <?php if(empty($messages)): ?>
                <div class="alert alert-info" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 4rem 2rem; background: var(--surface-color); border-color: var(--border-color);">
                    <i class="ph ph-envelope-open" style="font-size: 4rem; color: var(--text-light); margin-bottom: 1rem;"></i>
                    <h3 style="margin-bottom: 0.5rem;">No Messages</h3>
                    <p style="color: var(--text-muted); margin: 0;">You have no customer support inquiries at the moment.</p>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <?php foreach($messages as $msg): ?>
                        <div style="background: var(--surface-color); border: 1px solid <?php echo $msg['is_read'] ? 'var(--border-color)' : 'var(--accent-color)'; ?>; border-radius: var(--radius-md); padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem; position: relative;">
                            
                            <?php if(!$msg['is_read']): ?>
                                <span style="position: absolute; top: 1.5rem; right: 1.5rem; width: 10px; height: 10px; background: var(--accent-color); border-radius: 50%;"></span>
                            <?php endif; ?>
                            
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; padding-right: 2rem;">
                                <div>
                                    <h3 style="font-size: 1.1rem; margin-bottom: 0.25rem; display: flex; align-items: center; gap: 0.5rem;">
                                        <?php echo htmlspecialchars($msg['name']); ?> 
                                    </h3>
                                    <div style="color: var(--text-muted); font-size: 0.9rem; display: flex; gap: 1rem; align-items: center;">
                                        <span><i class="ph ph-envelope-simple"></i> <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>" style="color: inherit; text-decoration: underline;"><?php echo htmlspecialchars($msg['email']); ?></a></span>
                                        <?php if(!empty($msg['order_number'])): ?>
                                            <span><i class="ph ph-receipt"></i> Order: <?php echo htmlspecialchars($msg['order_number']); ?></span>
                                        <?php endif; ?>
                                        <span><i class="ph ph-clock"></i> <?php echo date('M d, Y g:i A', strtotime($msg['created_at'])); ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div style="background: var(--bg-color); padding: 1rem; border-radius: var(--radius-sm); color: var(--text-main); line-height: 1.6; font-size: 0.95rem;">
                                <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                            </div>
                            
                            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 0.5rem;">
                                <?php if(!$msg['is_read']): ?>
                                    <form method="POST">
                                        <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                                        <button type="submit" name="mark_read" class="btn btn-secondary" style="padding: 0.35rem 0.75rem; font-size: 0.85rem;"><i class="ph ph-check"></i> Mark as Read</button>
                                    </form>
                                <?php endif; ?>
                                
                                <form method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this message?');">
                                    <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                                    <button type="submit" name="delete_message" class="btn btn-danger" style="padding: 0.35rem 0.75rem; font-size: 0.85rem;"><i class="ph ph-trash"></i> Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
</div>

<?php require_once '../includes/footer.php'; ?>
