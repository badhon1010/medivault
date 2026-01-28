<?php
include 'config.php'; 

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $pass = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        echo "<script>alert('Error: Email not found!'); window.location='admin_login.php';</script>";
    } else {
        if (password_verify($pass, $user['password_hash'])) {
            
            if (strtolower($user['role']) == 'admin') {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                
                header("Location: adminpanel.php");
                exit();
            } else {
                echo "<script>alert('Access Denied!'); window.location='admin_login.php';</script>";
                exit();
            }
        } else {
            echo "<script>alert('Error: Incorrect Password!'); window.location='admin_login.php';</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediVault Admin | Secure Access</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .fade-in { animation: fadeIn 0.6s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="bg-slate-900 flex items-center justify-center min-h-screen relative overflow-hidden">

    <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0">
        <div class="absolute top-[-10%] right-[-5%] w-96 h-96 bg-teal-600 rounded-full blur-[150px] opacity-20"></div>
        <div class="absolute bottom-[-10%] left-[-5%] w-96 h-96 bg-blue-600 rounded-full blur-[150px] opacity-20"></div>
    </div>

    <div class="bg-white p-10 rounded-3xl shadow-2xl w-full max-w-md z-10 fade-in border border-slate-800">
        
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-slate-100 rounded-2xl mb-4 shadow-inner">
                <i class="fas fa-user-shield text-3xl text-slate-800"></i>
            </div>
            <h2 class="text-2xl font-black text-slate-800 uppercase tracking-tight">Admin Portal</h2>
            <p class="text-slate-400 text-xs font-bold mt-1 tracking-wide">Secure Access Only</p>
        </div>

        <form method="POST" action="admin_login.php" class="space-y-6">
            
            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Admin Email</label>
                <div class="relative">
                    <i class="fas fa-envelope absolute left-4 top-4 text-slate-300"></i>
                    <input type="email" name="email" placeholder="admin@medivault.com" required 
                           class="w-full pl-10 pr-4 py-3.5 bg-slate-50 border border-slate-100 rounded-xl focus:ring-2 focus:ring-slate-800 focus:bg-white outline-none font-bold text-sm text-slate-700 transition-all">
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Password</label>
                <div class="relative">
                    <i class="fas fa-lock absolute left-4 top-4 text-slate-300"></i>
                    <input type="password" name="password" placeholder="••••••••" required 
                           class="w-full pl-10 pr-4 py-3.5 bg-slate-50 border border-slate-100 rounded-xl focus:ring-2 focus:ring-slate-800 focus:bg-white outline-none font-bold text-sm text-slate-700 transition-all">
                </div>
            </div>

            <button type="submit" name="login" class="w-full bg-slate-900 text-white py-4 rounded-xl font-black uppercase tracking-widest text-xs hover:bg-black transition-all shadow-lg hover:shadow-xl active:scale-95 flex items-center justify-center gap-2">
                <i class="fas fa-sign-in-alt"></i> Login to Dashboard
            </button>

        </form>

        <div class="mt-8 text-center border-t border-slate-100 pt-6">
            <a href="index.php" class="text-slate-400 text-xs font-bold hover:text-teal-600 transition-colors flex items-center justify-center gap-2 group">
                <i class="fas fa-arrow-left group-hover:-translate-x-1 transition-transform"></i> Back to Patient Login
            </a>
        </div>

    </div>

    <div class="absolute bottom-4 text-slate-700 text-[10px] font-bold tracking-widest">
        MEDIVAULT ADMIN SYSTEM &copy; 2026
    </div>

</body>
</html>