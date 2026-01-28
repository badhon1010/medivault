<?php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
$user_id = $_SESSION['user_id'];

if (isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    try {
        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, phone = ?, address = ? WHERE user_id = ?");
        if ($stmt->execute([$full_name, $phone, $address, $user_id])) {
            $_SESSION['user_name'] = $full_name; // সেশনের নামও আপডেট করা হলো
            echo "<script>alert('Profile updated successfully!'); window.location='profile.php';</script>";
        }
    } catch (PDOException $e) {
        echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
    }
}

if (isset($_POST['change_password'])) {
    $old_pass = $_POST['old_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $currentUser = $stmt->fetch();

    if (password_verify($old_pass, $currentUser['password_hash'])) {
        if ($new_pass === $confirm_pass) {
            $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
            $updateStmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
            if ($updateStmt->execute([$new_hash, $user_id])) {
                echo "<script>alert('Password changed successfully!'); window.location='profile.php';</script>";
            }
        } else {
            echo "<script>alert('New password and confirm password do not match!');</script>";
        }
    } else {
        echo "<script>alert('Old password is incorrect!');</script>";
    }
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | MediVault</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
        .bg-arogga { background-color: #0d9488; }
        .text-arogga { color: #0d9488; }
    </style>
</head>
<body class="flex flex-col min-h-screen">

    <nav class="bg-white shadow-md sticky top-0 z-50 w-full">
        <div class="container mx-auto px-4 h-20 flex justify-between items-center">
            <a href="dashboard.php" class="text-2xl font-black text-slate-800 tracking-tight">Medi<span class="text-arogga">Vault</span></a>
            <a href="dashboard.php" class="flex items-center gap-2 text-slate-500 hover:text-arogga font-bold text-sm transition-colors">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-10 max-w-4xl">
        
        <div class="bg-arogga rounded-t-3xl p-10 text-center text-white relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-full bg-white/10 opacity-50"></div>
            <div class="relative z-10">
                <div class="w-24 h-24 bg-white text-arogga rounded-full flex items-center justify-center text-4xl font-black mx-auto shadow-lg mb-4 uppercase">
                    <?= substr($user['full_name'], 0, 1) ?>
                </div>
                <h1 class="text-3xl font-bold"><?= htmlspecialchars($user['full_name']) ?></h1>
                <p class="text-teal-100 text-sm"><?= htmlspecialchars($user['email']) ?></p>
            </div>
        </div>

        <div class="bg-white rounded-b-3xl shadow-sm p-8 md:p-12 mb-8">
            <h3 class="text-xl font-black text-slate-800 uppercase tracking-tight mb-6 border-b pb-2">Personal Information</h3>
            
            <form method="POST" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2 ml-1">Full Name</label>
                        <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required 
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none font-bold text-slate-700">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2 ml-1">Email (Cannot be changed)</label>
                        <input type="email" value="<?= htmlspecialchars($user['email']) ?>" readonly 
                            class="w-full px-4 py-3 bg-gray-100 border border-gray-200 rounded-xl text-slate-400 font-bold cursor-not-allowed select-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2 ml-1">Phone Number</label>
                        <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="Enter phone number" 
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none font-bold text-slate-700">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2 ml-1">Address</label>
                        <input type="text" name="address" value="<?= htmlspecialchars($user['address'] ?? '') ?>" placeholder="Enter address" 
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none font-bold text-slate-700">
                    </div>
                </div>

                <button type="submit" name="update_profile" class="w-full bg-slate-900 text-white py-4 rounded-xl font-bold uppercase tracking-widest hover:bg-arogga transition-all shadow-lg mt-4">
                    Update Profile Info
                </button>
            </form>
        </div>

        <div class="bg-white rounded-3xl shadow-sm p-8 md:p-12 border border-gray-100">
            <h3 class="text-xl font-black text-slate-800 uppercase tracking-tight mb-6 border-b pb-2 flex items-center gap-2">
                <i class="fas fa-lock text-arogga"></i> Security & Password
            </h3>

            <form method="POST" class="space-y-6 max-w-2xl">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2 ml-1">Old Password</label>
                    <input type="password" name="old_password" required placeholder="••••••••" 
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none font-bold text-slate-700">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2 ml-1">New Password</label>
                        <input type="password" name="new_password" required placeholder="New password" 
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none font-bold text-slate-700">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2 ml-1">Confirm New Password</label>
                        <input type="password" name="confirm_password" required placeholder="Confirm new password" 
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none font-bold text-slate-700">
                    </div>
                </div>

                <button type="submit" name="change_password" class="bg-red-50 text-red-500 px-8 py-3 rounded-xl font-bold uppercase text-xs hover:bg-red-500 hover:text-white transition-all shadow-sm">
                    Change Password
                </button>
            </form>
        </div>

    </div>

</body>
</html>