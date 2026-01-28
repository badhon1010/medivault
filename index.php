<?php
include 'config.php'; 

if (isset($_POST['signup'])) {
    try {
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);
        $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT); 

        $sql = "INSERT INTO users (full_name, email, password_hash, phone, address, role) 
                VALUES (:name, :email, :pass, :phone, :address, 'patient')";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            ':name'    => $full_name,
            ':email'   => $email,
            ':pass'    => $password_hash,
            ':phone'   => $phone,
            ':address' => $address
        ]);

        if ($result) {
            echo "<script>alert('Account created! You are now registered as a Patient.'); window.location='index.php';</script>";
            exit();
        }
    } catch (PDOException $e) {
        die("Registration Error: " . $e->getMessage());
    }
}

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $pass = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($pass, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];

            if (strtolower($user['role']) == 'admin') {
                echo "<script>alert('Invalid email or password!'); window.location='index.php';</script>";
                exit();
            } else {
                header("Location: dashboard.php");
            }
            exit();
        } else {
            echo "<script>alert('Invalid email or password!'); window.location='index.php';</script>";
            exit();
        }
    } catch (PDOException $e) {
        die("Login Error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to MediVault</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        .bg-arogga { background-color: #0d9488; }
        .text-arogga { color: #0d9488; }
        .hover-arogga:hover { background-color: #0f766e; }
        
        /* Animation */
        .fade-in { animation: fadeIn 0.5s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="bg-gray-50 h-screen w-full overflow-hidden">

    <div class="flex h-full w-full">
        
        <div class="hidden lg:flex w-1/2 bg-gradient-to-br from-teal-600 to-emerald-800 items-center justify-center relative overflow-hidden">
            <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>
            <div class="z-10 text-center text-white p-12">
                <div class="bg-white/10 backdrop-blur-lg p-4 rounded-2xl inline-block mb-6 shadow-xl border border-white/10">
                    <i class="fas fa-heartbeat text-6xl text-white"></i>
                </div>
                <h1 class="text-5xl font-black mb-4 tracking-tight">MediVault</h1>
                <p class="text-lg text-teal-100 font-medium max-w-md mx-auto leading-relaxed">
                    Your trusted partner for authentic medicines and healthcare products delivered to your doorstep.
                </p>
                <div class="mt-8 flex justify-center gap-2">
                    <div class="h-2 w-2 bg-white rounded-full opacity-50"></div>
                    <div class="h-2 w-2 bg-white rounded-full"></div>
                    <div class="h-2 w-2 bg-white rounded-full opacity-50"></div>
                </div>
            </div>
        </div>

        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-white overflow-y-auto">
            <div class="w-full max-w-md space-y-8 fade-in">
                
                <div class="text-center">
                    <h2 class="text-3xl font-black text-slate-800 tracking-tight">Welcome Back!</h2>
                    <p class="text-slate-400 text-sm mt-2 font-medium">Please enter your details to continue</p>
                </div>

                <div class="bg-gray-100 p-1 rounded-xl flex font-bold text-sm">
                    <button onclick="switchForm('login')" id="tab-login" class="flex-1 py-3 rounded-lg bg-white text-slate-800 shadow-sm transition-all">Log In</button>
                    <button onclick="switchForm('signup')" id="tab-signup" class="flex-1 py-3 rounded-lg text-slate-500 hover:text-slate-700 transition-all">Sign Up</button>
                </div>

                <form id="login-form" method="POST" class="space-y-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2 ml-1">Email Address</label>
                        <div class="relative">
                            <i class="fas fa-envelope absolute left-4 top-3.5 text-gray-400"></i>
                            <input type="email" name="email" required placeholder="name@example.com" 
                                class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 focus:bg-white outline-none transition-all text-sm font-bold text-slate-700">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2 ml-1">Password</label>
                        <div class="relative">
                            <i class="fas fa-lock absolute left-4 top-3.5 text-gray-400"></i>
                            <input type="password" name="password" required placeholder="••••••••" 
                                class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 focus:bg-white outline-none transition-all text-sm font-bold text-slate-700">
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between text-xs font-bold">
                        <label class="flex items-center text-slate-500 cursor-pointer">
                            <input type="checkbox" class="mr-2 rounded text-teal-600 focus:ring-teal-500"> Remember me
                        </label>
                        <a href="#" class="text-arogga hover:underline">Forgot Password?</a>
                    </div>

                    <button type="submit" name="login" class="w-full bg-slate-900 text-white py-3.5 rounded-xl font-bold hover:bg-arogga transition-all shadow-lg active:scale-95">
                        Log In <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </form>

                <form id="signup-form" method="POST" class="space-y-4 hidden">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1 ml-1">Full Name</label>
                        <input type="text" name="full_name" required placeholder="John Doe" 
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none text-sm font-bold">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1 ml-1">Email</label>
                            <input type="email" name="email" required placeholder="mail@site.com" 
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none text-sm font-bold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1 ml-1">Phone</label>
                            <input type="text" name="phone" required placeholder="017..." 
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none text-sm font-bold">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1 ml-1">Address</label>
                        <input type="text" name="address" required placeholder="House, Road, Area" 
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none text-sm font-bold">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1 ml-1">Password</label>
                        <input type="password" name="password" required placeholder="Create a password" 
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none text-sm font-bold">
                    </div>

                    <button type="submit" name="signup" class="w-full bg-arogga text-white py-3.5 rounded-xl font-bold hover:bg-teal-700 transition-all shadow-lg active:scale-95">
                        Create Account
                    </button>
                </form>

                <div class="mt-6 text-center border-t border-gray-100 pt-4">
                    <a href="admin_login.php" class="text-xs font-bold text-slate-400 hover:text-arogga transition-colors flex items-center justify-center gap-2">
                        <i class="fas fa-user-shield"></i> Admin Portal Access
                    </a>
                </div>

                <p class="text-center text-xs font-bold text-slate-400 mt-8">
                    &copy; 2026 MediVault. All rights reserved.
                </p>
            </div>
        </div>
    </div>

    <script>
        function switchForm(form) {
            const loginForm = document.getElementById('login-form');
            const signupForm = document.getElementById('signup-form');
            const loginTab = document.getElementById('tab-login');
            const signupTab = document.getElementById('tab-signup');

            if (form === 'login') {
                loginForm.classList.remove('hidden');
                signupForm.classList.add('hidden');
                
                loginTab.classList.add('bg-white', 'text-slate-800', 'shadow-sm');
                loginTab.classList.remove('text-slate-500');
                
                signupTab.classList.remove('bg-white', 'text-slate-800', 'shadow-sm');
                signupTab.classList.add('text-slate-500');
            } else {
                loginForm.classList.add('hidden');
                signupForm.classList.remove('hidden');
                
                signupTab.classList.add('bg-white', 'text-slate-800', 'shadow-sm');
                signupTab.classList.remove('text-slate-500');
                
                loginTab.classList.remove('bg-white', 'text-slate-800', 'shadow-sm');
                loginTab.classList.add('text-slate-500');
            }
        }
    </script>
</body>
</html>