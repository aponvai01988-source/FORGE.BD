<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

require_login();
$user = get_current_user_data($pdo);

$stmt = $pdo->prepare("SELECT * FROM wallet_transactions WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$user['id']]);
$txs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>Wallet History - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container" style="max-width: 700px; margin-top: 30px;">
        <div class="card">
            <h2>Wallet Transactions</h2>
            <table style="width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 0.85rem;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border-color); text-align: left;">
                        <th style="padding: 8px;">Date</th>
                        <th style="padding: 8px;">Type</th>
                        <th style="padding: 8px;">Amount</th>
                        <th style="padding: 8px;">Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($txs as $t): ?>
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 8px;"><?php echo date('M d, H:i', strtotime($t['created_at'])); ?></td>
                            <td style="padding: 8px;"><?php echo $t['type']; ?></td>
                            <td style="padding: 8px; color: <?php echo in_array($t['type'], ['referral_reward', 'withdrawal_refund']) ? 'var(--success)' : 'var(--danger)'; ?>;">
                                ৳ <?php echo number_format($t['amount'], 2); ?>
                            </td>
                            <td style="padding: 8px;"><?php echo htmlspecialchars($t['description']); ?></td>
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
