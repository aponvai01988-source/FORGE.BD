<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

require_login();
$user = get_current_user_data($pdo);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token!';
    } else {
        $full_name = sanitize($_POST['full_name']);

        // Profile Picture Upload Logic
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['profile_pic']['tmp_name'];
            $fileName = $_FILES['profile_pic']['name'];
            $fileSize = $_FILES['profile_pic']['size'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            if (in_array($fileExtension, $allowedExtensions) && $fileSize <= 2 * 1024 * 1024) {
                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                $dest_path = UPLOAD_DIR . $newFileName;

                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $upd_pic = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
                    $upd_pic->execute([$newFileName, $user['id']]);
                }
            } else {
                $error = 'অবৈধ ফাইল টাইপ অথবা ফাইল সাইজ ২MB এর বেশি!';
            }
        }

        if (!$error) {
            $upd = $pdo->prepare("UPDATE users SET full_name = ? WHERE id = ?");
            $upd->execute([$full_name, $user['id']]);
            $success = 'প্রোফাইল আপডেট হয়েছে!';
            $user = get_current_user_data($pdo);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>Profile - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container" style="max-width: 500px; margin-top: 30px;">
        <div class="card">
            <h2>User Profile</h2>
            <?php if ($error): ?><div style="color: var(--danger); margin-bottom: 10px;"><?php echo $error; ?></div><?php endif; ?>
            <?php if ($success): ?><div style="color: var(--success); margin-bottom: 10px;"><?php echo $success; ?></div><?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data" style="margin-top: 15px;">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="form-group" style="text-align: center;">
                    <img src="assets/uploads/<?php echo htmlspecialchars($user['profile_picture']); ?>" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover;">
                    <br><br>
                    <input type="file" name="profile_pic" class="form-control" accept="image/*">
                </div>
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Username (Read Only)</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Email (Read Only)</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Save Changes</button>
            </form>
            <br>
            <a href="dashboard.php">← Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
