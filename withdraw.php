<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

require_login();
$user = get_current_user_data($pdo);
require_verified($user);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token!';
    } else {
        $method = sanitize($_POST['method']);
        $account = sanitize($_POST['account_number']);
        $amount = (float)$_POST['amount'];

        if ($amount < MIN_WITHDRAWAL) {
            $error = 'সর্বনিম্ন উইথড্র অ্যামাউন্ট ' . MIN_WITHDRAWAL . ' BDT!';
        } elseif ($amount > $user['balance']) {
            $error = 'আপনার অ্যাকাউন্টে পর্যাপ্ত ব্যালেন্স নেই!';
        } elseif (empty($account)) {
            $error = 'অ্যাকাউন্ট নম্বর আবশ্যক!';
        } else {
            // DB Transaction to prevent Race Condition
            try {
                $pdo->beginTransaction();

                // Re-check balance with row lock
                $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ? FOR UPDATE");
                $stmt->execute([$user['id']]);
                $current_bal = $stmt->fetchColumn();

                if ($current_bal < $amount) {
                    throw new Exception('পর্যাপ্ত ব্যালেন্স নেই!');
                }

                $new_bal = $current_bal - $amount;

                // Deduct Balance
                $upd = $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?");
                $upd->execute([$new_bal, $user['id']]);

                // Create Withdrawal Record
                $w_stmt = $pdo->prepare("INSERT INTO withdrawals (user_id, method, account_number, amount, status) VALUES (?, ?, ?, ?, 'pending')");
                $w_stmt->execute([$user['id'], $method, $account, $amount]);
                $withdraw_id = $pdo->lastInsertId();

                // Create Wallet Transaction Record
                $t_stmt = $pdo->prepare("INSERT INTO wallet_transactions (user_id, type, amount, reference_id, description, balance_before, balance_after) VALUES (?, 'withdrawal_request', ?, ?, ?, ?, ?)");
                $t_stmt->execute([$user['id'], $amount, $withdraw_id, "Withdrawal Request to $method ($account)", $current_bal, $new_bal]);

                $pdo->commit();

                add_notification($pdo, $user['id'], 'Withdrawal Request Submitted', "আপনার $amount BDT উইথড্র রিকোয়েস্ট প্রসেসিংয়ে রয়েছে।");
                $success = 'উইথড্র রিকোয়েস্ট সফলভাবে সাবমিট হয়েছে!';
                $user['balance'] = $new_bal; // Update local array
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = $e->getMessage();
            }
        }
    }
}

// Fetch Withdrawal History
$history = $pdo->prepare("SELECT * FROM withdrawals WHERE user_id = ? ORDER BY id DESC");
$history->execute([$user['id']]);
$withdrawals = $history->fetchAll();
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>Withdraw - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container" style="max-width: 600px; margin-top: 30px;">
        <div class="card">
            <h2>Withdraw Funds</h2>
            <p style="color: var(--text-muted); margin-bottom: 15px;">Available Balance: <strong>৳ <?php echo number_format($user['balance'], 2); ?></strong></p>

            <?php if ($error): ?><div style="color: var(--danger); margin-bottom: 15px;"><?php echo $error; ?></div><?php endif; ?>
            <?php if ($success): ?><div style="color: var(--success); margin-bottom: 15px;"><?php echo $success; ?></div><?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="form-group">
                    <label>Payment Method</label>
                    <select name="method" class="form-control">
                        <option value="bKash">bKash Personal</option>
                        <option value="Nagad">Nagad Personal</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Account Number</label>
                    <input type="text" name="account_number" class="form-control" required placeholder="017XXXXXXXX">
                </div>
                <div class="form-group">
                    <label>Amount (Min <?php echo MIN_WITHDRAWAL; ?> BDT)</label>
                    <input type="number" step="0.01" name="amount" class="form-control" required placeholder="300">
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Submit Withdrawal</button>
            </form>
        </div>

        <div class="card">
            <h3>Withdrawal History</h3>
            <table style="width: 100%; border-collapse: collapse; margin-top: 12px; font-size: 0.9rem;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border-color); text-align: left;">
                        <th style="padding: 8px;">Date</th>
                        <th style="padding: 8px;">Method</th>
                        <th style="padding: 8px;">Amount</th>
                        <th style="padding: 8px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($withdrawals as $w): ?>
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 8px;"><?php echo date('M d, H:i', strtotime($w['created_at'])); ?></td>
                            <td style="padding: 8px;"><?php echo $w['method']; ?> (<?php echo htmlspecialchars($w['account_number']); ?>)</td>
                            <td style="padding: 8px;">৳ <?php echo number_format($w['amount'], 2); ?></td>
                            <td style="padding: 8px;">
                                <span class="badge badge-<?php echo $w['status'] == 'paid' ? 'verified' : ($w['status'] == 'rejected' ? 'unverified' : 'pending'); ?>">
                                    <?php echo ucfirst($w['status']); ?>
                                </span>
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
