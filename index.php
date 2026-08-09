<?php
require_once __DIR__ . '/config/config.php';
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Professional Referral Platform</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="logo"><?php echo SITE_NAME; ?></div>
        <div>
            <a href="login.php" class="btn btn-outline">Login</a>
            <a href="register.php" class="btn btn-primary">Register</a>
        </div>
    </nav>

    <div class="container" style="margin-top: 40px;">
        <div class="disclaimer-box">
            <strong>⚠️ গুরুত্বপূর্ণ সতর্কবার্তা:</strong> RF NETWORK কোনো ইনভেস্টমেন্ট, MLM বা Guaranteed Earnings প্ল্যাটফর্ম নয়। অ্যাকাউন্ট ভেরিফিকেশন ফি ও রেফারেল রিওয়ার্ড সম্পূর্ণ স্বচ্ছ নিয়ম অনুযায়ী পরিচালিত হয়।
        </div>

        <div style="text-align: center; padding: 60px 20px;">
            <h1 style="font-size: 2.5rem; margin-bottom: 16px;">Build Your Professional Network</h1>
            <p style="color: var(--text-muted); max-width: 600px; margin: 0 auto 24px;">একটি স্বচ্ছ ও নিরাপদ রেফারেল নেটওয়ার্ক যেখানে আপনি নেটওয়ার্ক তৈরির মাধ্যমে রিওয়ার্ড অর্জন করতে পারেন।</p>
            <a href="register.php" class="btn btn-primary" style="font-size: 1.1rem;">এখনই শুরু করুন</a>
        </div>

        <div class="grid-2" style="margin-top: 40px;">
            <div class="card">
                <h3><i class="fa-solid fa-shield-halved" style="color: var(--accent-primary);"></i> ম্যানুয়াল পেমেন্ট ভেরিফিকেশন</h3>
                <p style="color: var(--text-muted); margin-top: 10px;">প্রতিটি অ্যাকাউন্ট ভেরিফিকেশন অ্যাডমিন দ্বারা ম্যানুয়ালি যাচাই করা হয় যাতে কোনো প্রকার প্রতারণা না ঘটে।</p>
            </div>
            <div class="card">
                <h3><i class="fa-solid fa-wallet" style="color: var(--success);"></i> ৩০০০ টাকা নূন্যতম উইথড্র</h3>
                <p style="color: var(--text-muted); margin-top: 10px;">৩০০ টাকা ব্যালেন্স হলেই ম্যানুয়ালি বিকাশ বা নগদের মাধ্যমে নিরাপদ উইথড্র সুবিধা।</p>
            </div>
        </div>

        <div class="card" style="margin-top: 40px; text-align: center;">
            <h2>নিয়মাবলী ও ডিসক্লেইমার</h2>
            <p style="color: var(--text-muted); margin-top: 12px; line-height: 1.6;">
                অ্যাপ্রুভড রেফারেল ব্যতীত কোনো বোনাস যোগ হবে না। অ্যাকাউন্ট তৈরি বা ভেরিফিকেশনের আগে আমাদের <a href="terms.php">Terms & Conditions</a> এবং <a href="privacy.php">Privacy Policy</a> পড়ে নেওয়ার অনুরোধ করা যাচ্ছে।
            </p>
        </div>
    </div>
</body>
</html>
