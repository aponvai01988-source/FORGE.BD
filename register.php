<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

$error = '';
$ref_code = sanitize($_GET['ref'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token!';
    } else {
        $full_name = sanitize($_POST['full_name']);
        $email     = sanitize($_POST['email']);
        $username  = strtolower(sanitize($_POST['username']));
        $password  = $_POST['password'];
        $confirm_pass = $_POST['confirm_password'];
        $input_ref = sanitize($_POST['referral_code']);

        if (strlen($password) < 8) {
            $error = 'পাসওয়ার্ড কমপক্ষে ৮ অক্ষরের হতে হবে!';
        } elseif ($password !== $confirm_pass) {
            $error = 'পাসওয়ার্ড দুটি মেলেনি!';
        } else {
            // Check email & username unique
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1");
            $stmt->execute([$email, $username]);
            if ($stmt->fetch()) {
                $error = 'ইমেইল বা ইউজারনেম ইতিমধ্যে ব্যবহৃত হচ্ছে!';
            } else {
                $referrer_id = null;
                if (!empty($input_ref)) {
                    $ref_stmt = $pdo->prepare("SELECT id FROM users WHERE referral_code = ? OR username = ? LIMIT 1");
                    $ref_stmt->execute([$input_ref, $input_ref]);
                    $ref_user = $ref_stmt->fetch();
                    if ($ref_user) {
                        $referrer_id = $ref_user['id'];
                    }
                }

                $user_id = generate_unique_code('RF', 6);
                $my_ref_code = generate_unique_code('REF', 6);
                $password_hash = password_hash($password, PASSWORD_BCRYPT);

                $insert = $pdo->prepare("INSERT INTO users (user_id, full_name, email, username, password_hash, referral_code, referrer_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
                if ($insert->execute([$user_id, $full_name, $email, $username, $password_hash, $my_ref_code, $referrer_id])) {
                    $new_id = $pdo->lastInsertId();
                    
                    // If referred, insert into referrals table as pending
                    if ($referrer_id) {
                        $ref_ins = $pdo->prepare("INSERT INTO referrals (referrer_id, referred_user_id, reward_amount, status) VALUES (?, ?, ?, 'pending')");
                        $ref_ins->execute([$referrer_id, $new_id, REFERRAL_REWARD]);
                    }

                    $_SESSION['user_id'] = $new_id;
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error = 'রেজিস্ট্রেশন ব্যর্থ হয়েছে। আবার চেষ্টা করুন।';
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
    <title>Register - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container" style="max-width: 450px; margin-top: 40px;">
        <div class="card">
            <h2 style="text-align: center; margin-bottom: 20px;">Account Registration</h2>
            <?php if ($error): ?><div style="color: var(--danger); margin-bottom: 15px; font-size:0.9rem;"><?php echo $error; ?></div><?php endif; ?>
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Password (Min 8 Chars)</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Referral Code (Optional)</label>
                    <input type="text" name="referral_code" class="form-control" value="<?php echo htmlspecialchars($ref_code); ?>">
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Create Account</button>
            </form>
            <p style="text-align: center; margin-top: 15px; font-size: 0.9rem; color: var(--text-muted);">
                ইতিমধ্যে অ্যাকাউন্ট আছে? <a href="login.php">Login</a>
            </p>
        </div>
    </div>
</body>
</html>
