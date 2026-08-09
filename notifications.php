<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

require_login();
$user = get_current_user_data($pdo);

$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY id DESC LIMIT 20");
$stmt->execute([$user['id']]);
$notifs = $stmt->fetchAll();

// Mark as read
$upd = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
$upd->execute([$user['id']]);
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>Notifications - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container" style="max-width: 600px; margin-top: 30px;">
        <div class="card">
            <h2>Notifications</h2>
            <div style="margin-top: 15px;">
                <?php foreach ($notifs as $n): ?>
                    <div style="border-bottom: 1px solid var(--border-color); padding: 12px 0;">
                        <h4 style="margin-bottom: 4px;"><?php echo htmlspecialchars($n['title']); ?></h4>
                        <p style="color: var(--text-muted); font-size: 0.85rem;"><?php echo htmlspecialchars($n['message']); ?></p>
                        <span style="font-size: 0.75rem; color: #64748b;"><?php echo date('M d, H:i', strtotime($n['created_at'])); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <br>
            <a href="dashboard.php">← Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
