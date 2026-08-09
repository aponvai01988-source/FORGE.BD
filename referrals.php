<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

require_login();
$user = get_current_user_data($pdo);

$stmt = $pdo->prepare("
    SELECT r.*, u.full_name, u.username, u.user_id, u.is_verified, u.created_at as joined_date 
    FROM referrals r
    JOIN users u ON r.referred_user_id = u.id
    WHERE r.referrer_id = ?
    ORDER BY r.id DESC
");
$stmt->execute([$user['id']]);
$referrals = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>My Referrals - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container" style="max-width: 800px; margin-top: 30px;">
        <div class="card">
            <h2>My Referral Network</h2>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 4px;">সফলভাবে ভেরিফাইড রেফারেলের জন্য ২৫০ BDT অটো-ক্রেডিট হয়।</p>
            
            <table style="width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 0.9rem;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border-color); text-align: left;">
                        <th style="padding: 10px;">User</th>
                        <th style="padding: 10px;">User ID</th>
                        <th style="padding: 10px;">Status</th>
                        <th style="padding: 10px;">Joined</th>
                        <th style="padding: 10px;">Reward</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($referrals as $ref): ?>
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 10px;">
                                <strong><?php echo htmlspecialchars($ref['full_name']); ?></strong><br>
                                <span style="color: var(--text-muted); font-size: 0.8rem;">@<?php echo htmlspecialchars($ref['username']); ?></span>
                            </td>
                            <td style="padding: 10px;"><?php echo $ref['user_id']; ?></td>
                            <td style="padding: 10px;">
                                <span class="badge badge-<?php echo $ref['is_verified'] ? 'verified' : 'unverified'; ?>">
                                    <?php echo $ref['is_verified'] ? 'Verified' : 'Unverified'; ?>
                                </span>
                            </td>
                            <td style="padding: 10px;"><?php echo date('M d, Y', strtotime($ref['joined_date'])); ?></td>
                            <td style="padding: 10px; color: <?php echo $ref['status'] == 'credited' ? 'var(--success)' : 'var(--text-muted)'; ?>;">
                                ৳ <?php echo number_format($ref['reward_amount'], 2); ?> (<?php echo ucfirst($ref['status']); ?>)
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <br>
            <a href="dashboard.php">← Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
