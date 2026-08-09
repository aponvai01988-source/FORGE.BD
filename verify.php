<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

require_login();
$user = get_current_user_data($pdo);

$error = '';
$success = '';

// Check if pending request exists
$stmt = $pdo->prepare("SELECT * FROM verification_requests WHERE user_id = ? AND status = 'pending' LIMIT 1");
$stmt->execute([$user['id']]);
$pending_req = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$pending_req && !$user['is_verified']) {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token!';
    } else {
        $method = sanitize($_POST['payment_method']);
        $sender = sanitize($_POST['sender_number']);
        $trx_id = strtoupper(sanitize($_POST['transaction_id']));
        $amount = (float)$_POST['amount'];

        if ($amount != VERIFICATION_FEE) {
            $error = 'ভেরিফিকেশন ফি অবশ্যই ঠিক ৩০০ BDT হতে হবে!';
        } elseif (empty($sender) || empty($trx_id)) {
            $error = 'সকল তথ্য পূরণ করা আবশ্যক!';
        } else {
            // Check duplicate Trx ID
            $chk = $pdo->prepare("SELECT id FROM verification_requests WHERE transaction_id = ? LIMIT 1");
            $chk->execute([$trx_id]);
            if ($chk->fetch()) {
                $error = 'এই ট্রানজেকশন আইডিটি ইতিমধ্যে সাবমিট করা হয়েছে!';
            } else {
                $ins = $pdo->prepare("INSERT INTO verification_requests (user_id, payment_method, sender_number, amount, transaction_id, status) VALUES (?, ?, ?, ?, ?, 'pending')");
                if ($ins->execute([$user['id'], $method, $sender, $amount, $trx_id])) {
                    add_notification($pdo, $user['id'], 'Verification Request Submitted', 'আপনার ভেরিফিকেশন রিকোয়েস্ট পেন্ডিং রয়েছে। অ্যাডমিন খুব শীঘ্রই এটি যাচাই করবেন।');
                    $success = 'ভেরিফিকেশন রিকোয়েস্ট সফলভাবে জমা দেওয়া হয়েছে!';
                    header("Location: verify.php");
                    exit();
                } else {
                    $error = 'অনুরোধ জমা নিতে ব্যর্থ হয়েছে। আবার চেষ্টা করুন।';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>Account Verification - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container" style="max-width: 500px; margin-top: 30px;">
        <div class="card">
            <h2>Account Verification</h2>
            <div class="disclaimer-box" style="margin-top: 15px;">
                Send Money করে ঠিক <strong>৩০০ BDT</strong> পাঠাতে হবে। ভুল অ্যামাউন্ট বা ভুল ট্রানজেকশন আইডি দিলে রিকোয়েস্ট রিজেক্ট হতে পারে।
            </div>

            <div style="background: rgba(255,255,255,0.05); padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                <p><strong>bKash (Send Money):</strong> <?php echo BKASH_NUMBER; ?></p>
                <p style="margin-top: 6px;"><strong>Nagad (Send Money):</strong> <?php echo NAGAD_NUMBER; ?></p>
            </div>

            <?php if ($user['is_verified']): ?>
                <div style="color: var(--success); font-weight: bold; text-align: center; padding: 20px;">
                    আপনার অ্যাকাউন্ট ভেরিফাইড!
                </div>
            <?php elseif ($pending_req): ?>
                <div style="background: rgba(245, 158, 11, 0.1); color: var(--warning); padding: 15px; border-radius: 8px;">
                    আপনার একটি ভেরিফিকেশন রিকোয়েস্ট পেন্ডিং রয়েছে।<br>
                    <strong>TrxID:</strong> <?php echo htmlspecialchars($pending_req['transaction_id']); ?>
                </div>
            <?php else: ?>
                <?php if ($error): ?><div style="color: var(--danger); margin-bottom: 15px;"><?php echo $error; ?></div><?php endif; ?>
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="form-group">
                        <label>Payment Method</label>
                        <select name="payment_method" class="form-control">
                            <option value="bKash">bKash</option>
                            <option value="Nagad">Nagad</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Sender Mobile Number</label>
                        <input type="text" name="sender_number" class="form-control" required placeholder="017XXXXXXXX">
                    </div>
                    <div class="form-group">
                        <label>Amount (BDT)</label>
                        <input type="number" name="amount" class="form-control" value="300" readonly>
                    </div>
                    <div class="form-group">
                        <label>Transaction ID (TrxID)</label>
                        <input type="text" name="transaction_id" class="form-control" required placeholder="8N7X6W5Y">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Submit Request</button>
                </form>
            <?php endif; ?>
            <br>
            <a href="dashboard.php">← Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
