<?php
include 'config.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms & Conditions | MediVault</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
        .text-arogga { color: #0d9488; }
    </style>
</head>
<body class="bg-gray-50">

    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 h-20 flex justify-between items-center">
            <a href="dashboard.php" class="text-2xl font-bold text-slate-800">Medi<span class="text-arogga">Vault</span></a>
            <a href="dashboard.php" class="text-sm font-bold text-gray-500 hover:text-arogga">Back to Dashboard</a>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-16 max-w-4xl">
        <div class="bg-white p-10 rounded-[40px] shadow-sm border border-gray-100">
            <h1 class="text-3xl font-bold text-slate-800 mb-2">Terms and Conditions</h1>
            <p class="text-sm text-gray-400 mb-8">Last updated: January 2026</p>

            <div class="space-y-8 text-slate-600 leading-relaxed">
                <div>
                    <h3 class="text-lg font-bold text-slate-800 mb-2">1. Introduction</h3>
                    <p>Welcome to MediVault. By accessing or using our website and services, you agree to be bound by these Terms and Conditions. If you disagree with any part of these terms, you may not access the service.</p>
                </div>

                <div>
                    <h3 class="text-lg font-bold text-slate-800 mb-2">2. Prescription Policy</h3>
                    <p>Valid prescriptions from a registered doctor are mandatory for purchasing certain medicines. MediVault reserves the right to cancel orders if the prescription provided is invalid, unclear, or expired.</p>
                </div>

                <div>
                    <h3 class="text-lg font-bold text-slate-800 mb-2">3. Pricing and Payments</h3>
                    <p>All prices are listed in Bangladeshi Taka (BDT). Prices are subject to change without notice. We accept Cash on Delivery and various digital payment methods.</p>
                </div>

                <div>
                    <h3 class="text-lg font-bold text-slate-800 mb-2">4. Return and Refund</h3>
                    <p>Medicines can be returned within 24 hours of delivery if they are damaged, incorrect, or expired. Please contact our support team immediately for assistance.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>