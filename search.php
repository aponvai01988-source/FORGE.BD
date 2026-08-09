<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

$q = sanitize($_GET['q'] ?? '');
$search_result = null;

if (!empty($q)) {
    $stmt = $pdo->prepare("SELECT user_id, full_name, username, profile_picture, is_verified, id FROM users WHERE username = ? OR user_id = ? LIMIT 1");
    $stmt->execute([$q, $q]);
    $search_result = $stmt->fetch();

    if ($search_result) {
        $c_stmt = $pdo->prepare("SELECT COUNT(*) FROM referrals WHERE referrer_id = ? AND status = 'credited'");
        $c_stmt->execute([$search_result['id']]);
        $search_result['verified_referrals'] = $c_stmt->fetchColumn();
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>Public Profile Search - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container" style="max-width: 500px; margin-top: 40px;">
        <div class="card">
            <h2>Search User Profile</h2>
            <form method="GET" action="" style="margin-top: 15px; display: flex; gap: 10px;">
                <input type="text" name="q" class="form-control" placeholder="Enter Username or User ID" value="<?php echo htmlspecialchars($q); ?>" required>
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </div>

        <?php if (!empty($q)): ?>
            <?php if ($search_result): ?>
                <div class="card" style="text-align: center;">
                    <img src="assets/uploads/<?php echo htmlspecialchars($search_result['profile_picture']); ?>" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover;">
                    <h3 style="margin-top: 10px;"><?php echo htmlspecialchars($search_result['full_name']); ?></h3>
                    <p style="color: var(--text-muted); font-size: 0.9rem;">@<?php echo htmlspecialchars($search_result['username']); ?> | ID: <?php echo $search_result['user_id']; ?></p>
                    <div style="margin: 10px 0;">
                        <span class="badge badge-<?php echo $search_result['is_verified'] ? 'verified' : 'unverified'; ?>">
                            <?php echo $search_result['is_verified'] ? 'Verified User' : 'Unverified User'; ?>
                        </span>
                    </div>
                    <p style="font-size: 0.9rem;">Total Verified Referrals: <strong><?php echo $search_result['verified_referrals']; ?></strong></p>
                </div>
            <?php else: ?>
                <div class="card" style="color: var(--danger); text-align: center;">
                    কোনো ইউজার খুঁজে পাওয়া যায়নি!
                </div>
            <?php endif; ?>
        <?php endif; ?>
        <a href="dashboard.php">← Back to Dashboard</a>
    </div>
</body>
</html>
