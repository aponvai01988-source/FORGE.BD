<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token!';
    } else {
        $login_input = sanitize($_POST['login_input']);
        $password    = $_POST['password'];

        $stmt = $pdo->prepare("SELECT * FROM users WHERE (email = ? OR username = ?) AND status = 'active' LIMIT 1");
        $stmt->execute([$login_input, $login_input]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = 'ভুল ইউজারনেম/ইমেইল অথবা পাসওয়ার্ড!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container" style="max-width: 400px; margin-top: 60px;">
        <div class="card">
            <h2 style="text-align: center; margin-bottom: 20px;">User Login</h2>
            <?php if ($error): ?><div style="color: var(--danger); margin-bottom: 15px; font-size: 0.9rem;"><?php echo $error; ?></div><?php endif; ?>
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="form-group">
                    <label>Email or Username</label>
                    <input type="text" name="login_input" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
            </form>
            <p style="text-align: center; margin-top: 15px; font-size: 0.9rem; color: var(--text-muted);">
                নতুন অ্যাকাউন্ট তৈরি করতে চান? <a href="register.php">Register</a>
            </p>
        </div>
    </div>
</body>
</html>
