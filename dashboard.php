<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

require_login();
$user = get_current_user_data($pdo);

// Statistics
$total_refs = $pdo->prepare("SELECT COUNT(*) FROM referrals WHERE referrer_id = ?");
$total_refs->execute([$user['id']]);
$total_referrals_count = $total_refs->fetchColumn();

$verified_refs = $pdo->prepare("SELECT COUNT(*) FROM referrals WHERE referrer_id = ? AND status = 'credited'");
$verified_refs->execute([$user['id']]);
$verified_referrals_count = $verified_refs->fetchColumn();

$pending_with = $pdo->prepare("SELECT SUM(amount) FROM withdrawals WHERE user_id = ? AND status = 'pending'");
$pending_with->execute([$user['id']]);
$pending_withdrawal_amount = $pending_with->fetchColumn() ?: 0.00;

$ref_link = BASE_URL . "/register.php?ref=" . $user['referral_code'];
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="padding-bottom: 70px;">
    <nav class="navbar">
        <div class="logo"><?php echo SITE_NAME; ?></div>
        <div>
            <a href="search.php" style="color: var(--text-main); margin-right: 15px;"><i class="fa-solid fa-magnifying-glass"></i></a>
            <a href="notifications.php" style="color: var(--text-main); margin-right: 15px;"><i class="fa-solid fa-bell"></i></a>
            <a href="logout.php" style="color: var(--danger);"><i class="fa-solid fa-power-off"></i></a>
        </div>
    </nav>

    <div class="container">
        <div class="card" style="display: flex; align-items: center; gap: 15px;">
            <img src="assets/uploads/<?php echo htmlspecialchars($user['profile_picture']); ?>" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover;">
            <div>
                <h3 style="margin-bottom: 4px;"><?php echo htmlspecialchars($user['full_name']); ?></h3>
                <p style="color: var(--text-muted); font-size: 0.85rem;">ID: <?php echo $user['user_id']; ?> | @<?php echo htmlspecialchars($user['username']); ?></p>
                <div style="margin-top: 6px;">
                    <?php if ($user['is_verified']): ?>
                        <span class="badge badge-verified"><i class="fa-solid fa-check-circle"></i> Verified</span>
                    <?php else: ?>
                        <span class="badge badge-unverified"><i class="fa-solid fa-clock"></i> Unverified</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if (!$user['is_verified']): ?>
            <div class="disclaimer-box" style="border-left-color: var(--danger);">
                <strong>⚠️ আপনার অ্যাকাউন্ট এখনো ভেরিফাইড নয়!</strong><br>
                ড্যাশবোর্ডের পূর্ণাঙ্গ সুবিধা ও উইথড্র অপশন ব্যবহার করতে অ্যাকাউন্ট ভেরিফিকেশন সম্পন্ন করুন।
                <div style="margin-top: 10px;">
                    <a href="verify.php" class="btn btn-primary" style="padding: 8px 16px; font-size: 0.85rem;">Verify Account (300 BDT)</a>
                </div>
            </div>
        <?php endif; ?>

        <div class="grid-4">
            <div class="card">
                <p style="color: var(--text-muted); font-size: 0.85rem;">Available Balance</p>
                <h2 style="color: var(--success); margin-top: 8px;">৳ <?php echo number_format($user['balance'], 2); ?></h2>
            </div>
            <div class="card">
                <p style="color: var(--text-muted); font-size: 0.85rem;">Total Earnings</p>
                <h2 style="color: var(--accent-primary); margin-top: 8px;">৳ <?php echo number_format($user['total_earnings'], 2); ?></h2>
            </div>
            <div class="card">
                <p style="color: var(--text-muted); font-size: 0.85rem;">Verified Referrals</p>
                <h2 style="margin-top: 8px;"><?php echo $verified_referrals_count; ?> / <?php echo $total_referrals_count; ?></h2>
            </div>
            <div class="card">
                <p style="color: var(--text-muted); font-size: 0.85rem;">Pending Withdrawal</p>
                <h2 style="color: var(--warning); margin-top: 8px;">৳ <?php echo number_format($pending_withdrawal_amount, 2); ?></h2>
            </div>
        </div>

        <div class="card">
            <h3>My Referral Link</h3>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 4px;">আপনার রেফারেল কোড: <strong><?php echo $user['referral_code']; ?></strong></p>
            <div style="display: flex; gap: 10px; margin-top: 12px;">
                <input type="text" readonly value="<?php echo $ref_link; ?>" class="form-control" id="refLinkInput">
                <button onclick="copyToClipboard('<?php echo $ref_link; ?>')" class="btn btn-primary"><i class="fa-regular fa-copy"></i> Copy</button>
            </div>
        </div>
    </div>

    <div class="bottom-nav">
        <a href="dashboard.php" class="active"><i class="fa-solid fa-house"></i><br>Home</a>
        <a href="referrals.php"><i class="fa-solid fa-users"></i><br>Referrals</a>
        <a href="withdraw.php"><i class="fa-solid fa-wallet"></i><br>Withdraw</a>
        <a href="profile.php"><i class="fa-solid fa-user"></i><br>Profile</a>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>
