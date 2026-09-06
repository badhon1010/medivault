<?php
include 'config.php';

$notice = '';
$otpSent = false;
$otpVerified = $_SESSION['reset_verified'] ?? false;
$resetEmail = $_SESSION['reset_email'] ?? '';
$demoOtp = $_SESSION['reset_otp'] ?? '';

if (isset($_POST['send_otp'])) {
    $email = trim($_POST['email'] ?? '');

    try {
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $otp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_otp'] = $otp;
            $_SESSION['reset_expires_at'] = time() + 300;
            $_SESSION['reset_verified'] = false;
            $otpSent = true;
            $resetEmail = $email;
            $demoOtp = $otp;
            $notice = 'A reset code was generated. For local testing, use the demo OTP below.';
        } else {
            $notice = 'If that email exists in our database, a reset code will be generated.';
        }
    } catch (PDOException $e) {
        $notice = 'Unable to process your request right now. Please try again.';
    }
}

if (isset($_POST['verify_otp'])) {
    $submittedOtp = trim($_POST['otp_code'] ?? '');
    $storedOtp = $_SESSION['reset_otp'] ?? null;
    $storedEmail = $_SESSION['reset_email'] ?? null;
    $expiresAt = $_SESSION['reset_expires_at'] ?? 0;

    if (!$storedEmail || !$storedOtp) {
        $notice = 'Please request a new OTP first.';
    } elseif (time() > $expiresAt) {
        $notice = 'Your reset code has expired. Please request a new one.';
        unset($_SESSION['reset_otp'], $_SESSION['reset_email'], $_SESSION['reset_expires_at'], $_SESSION['reset_verified']);
    } elseif ($submittedOtp === $storedOtp) {
        $_SESSION['reset_verified'] = true;
        $otpVerified = true;
        $notice = 'OTP verified successfully. Please create a new password.';
    } else {
        $notice = 'The OTP you entered is incorrect. Please try again.';
    }
}

if (isset($_POST['reset_password'])) {
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $storedEmail = $_SESSION['reset_email'] ?? null;

    if (!$storedEmail) {
        $notice = 'The reset session expired. Please request a new code.';
    } elseif (!($_SESSION['reset_verified'] ?? false)) {
        $notice = 'Please verify the OTP before resetting your password.';
    } elseif (strlen($newPassword) < 6) {
        $notice = 'Password must be at least 6 characters long.';
    } elseif ($newPassword !== $confirmPassword) {
        $notice = 'Passwords do not match. Please try again.';
    } else {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
            $stmt->execute([$hashedPassword, $storedEmail]);

            unset($_SESSION['reset_email'], $_SESSION['reset_otp'], $_SESSION['reset_expires_at'], $_SESSION['reset_verified']);
            echo "<script>alert('Password reset successful! Please log in again.'); window.location='index.php';</script>";
            exit();
        } catch (PDOException $e) {
            $notice = 'Password could not be updated. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | MediVault</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #ecfeff 0%, #f8fafc 100%); }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden">
        <div class="bg-slate-900 px-8 py-8 text-white">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-12 h-12 rounded-2xl bg-teal-500/20 flex items-center justify-center text-teal-300">
                    <i class="fas fa-lock"></i>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[0.25em] text-slate-300">Security</p>
                    <h1 class="text-2xl font-black">Reset Password</h1>
                </div>
            </div>
            <p class="text-sm text-slate-300">Recover access to your MediVault account with a one-time code.</p>
        </div>

        <div class="p-8 space-y-6">
            <?php if (!empty($notice)): ?>
                <div class="rounded-xl border border-teal-100 bg-teal-50 text-teal-700 px-4 py-3 text-sm font-medium">
                    <?= htmlspecialchars($notice) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($demoOtp) && !$otpVerified): ?>
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    <p class="font-black uppercase tracking-wider text-[10px] mb-1">Demo OTP</p>
                    <p class="text-xl font-black tracking-[0.4em]"><?= htmlspecialchars($demoOtp) ?></p>
                </div>
            <?php endif; ?>

            <?php if (!($otpVerified || (!empty($resetEmail) && !empty($_SESSION['reset_otp'])))): ?>
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-slate-500 mb-2">Email address</label>
                        <input type="email" name="email" required placeholder="name@example.com" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none text-sm font-bold text-slate-700">
                    </div>
                    <button type="submit" name="send_otp" class="w-full bg-teal-600 text-white py-3 rounded-xl font-bold hover:bg-teal-700 transition-all shadow-lg">
                        Send OTP
                    </button>
                </form>
            <?php endif; ?>

            <?php if (!empty($resetEmail) && !empty($_SESSION['reset_otp']) && !$otpVerified): ?>
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-slate-500 mb-2">Enter OTP</label>
                        <input type="text" name="otp_code" inputmode="numeric" maxlength="6" required placeholder="123456" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none text-sm font-bold text-slate-700 tracking-[0.5em] text-center">
                    </div>
                    <button type="submit" name="verify_otp" class="w-full bg-slate-900 text-white py-3 rounded-xl font-bold hover:bg-slate-700 transition-all">
                        Verify OTP
                    </button>
                </form>
            <?php endif; ?>

            <?php if ($otpVerified): ?>
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-slate-500 mb-2">New password</label>
                        <input type="password" name="new_password" required placeholder="Enter new password" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none text-sm font-bold text-slate-700">
                    </div>
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-slate-500 mb-2">Confirm password</label>
                        <input type="password" name="confirm_password" required placeholder="Re-enter password" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none text-sm font-bold text-slate-700">
                    </div>
                    <button type="submit" name="reset_password" class="w-full bg-emerald-600 text-white py-3 rounded-xl font-bold hover:bg-emerald-700 transition-all shadow-lg">
                        Update Password
                    </button>
                </form>
            <?php endif; ?>

            <div class="text-center text-sm text-slate-500">
                Remember your password?
                <a href="index.php" class="font-bold text-teal-600 hover:underline">Back to login</a>
            </div>
        </div>
    </div>
</body>
</html>
