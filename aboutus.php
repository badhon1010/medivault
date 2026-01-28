<?php
include 'config.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | MediVault</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
        .text-arogga { color: #0d9488; }
        .bg-arogga { background-color: #0d9488; }
    </style>
</head>
<body class="bg-gray-50">

    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 h-20 flex justify-between items-center">
            <a href="dashboard.php" class="text-2xl font-bold text-slate-800">Medi<span class="text-arogga">Vault</span></a>
            <a href="dashboard.php" class="text-sm font-bold text-gray-500 hover:text-arogga">Back to Dashboard</a>
        </div>
    </nav>

    <div class="bg-arogga text-white py-20 text-center">
        <h1 class="text-4xl font-bold mb-4">Empowering Healthcare in Bangladesh</h1>
        <p class="text-teal-100 max-w-2xl mx-auto">We are building the future of healthcare by making authentic medicines accessible, affordable, and convenient for everyone.</p>
    </div>

    <div class="container mx-auto px-4 py-16">
        <div class="grid md:grid-cols-2 gap-12 items-center">
            <div>
                <img src="https://img.freepik.com/free-photo/doctors-day-cute-young-handsome-doctor-medical-gown-smiling-holding-folder_140725-162883.jpg" class="rounded-3xl shadow-2xl" alt="About Us">
            </div>
            <div>
                <h2 class="text-3xl font-bold text-slate-800 mb-6">Who We Are</h2>
                <p class="text-slate-600 mb-4 leading-relaxed">
                    MediVault is a premier online pharmacy solution designed to bridge the gap between patients and essential medication. Founded in 2026, our mission is to ensure that no one has to worry about the authenticity or availability of their medicines.
                </p>
                <p class="text-slate-600 mb-6 leading-relaxed">
                    We partner with top pharmaceutical manufacturers to deliver 100% genuine products directly to your doorstep. With our easy-to-use platform, you can upload prescriptions, consult experts, and manage your health seamlessly.
                </p>
                
                <div class="grid grid-cols-2 gap-6 mt-8">
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                        <i class="fas fa-shield-alt text-3xl text-arogga mb-2"></i>
                        <h4 class="font-bold text-slate-800">100% Authentic</h4>
                        <p class="text-xs text-gray-500">Directly from manufacturers</p>
                    </div>
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                        <i class="fas fa-shipping-fast text-3xl text-arogga mb-2"></i>
                        <h4 class="font-bold text-slate-800">Fast Delivery</h4>
                        <p class="text-xs text-gray-500">Delivery within 24 hours</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <footer class="bg-slate-900 text-slate-400 py-8 text-center text-xs">
        &copy; 2026 MediVault. All rights reserved.
    </footer>
</body>
</html>