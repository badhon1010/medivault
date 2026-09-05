<?php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Center | MediVault</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #eef2ff 0%, #f8fafc 40%, #ecfeff 100%); }
        .bg-arogga { background-color: #0d9488; }
        .text-arogga { color: #0d9488; }
    </style>
</head>
<body class="min-h-screen">
    <nav class="bg-white shadow-md sticky top-0 z-20">
        <div class="container mx-auto px-4 h-20 flex items-center justify-between">
            <a href="dashboard.php" class="flex items-center gap-3">
                <div class="bg-arogga text-white p-2 rounded-xl"><i class="fas fa-heartbeat"></i></div>
                <span class="text-2xl font-black text-slate-800">Medi<span class="text-arogga">Vault</span></span>
            </a>
            <div class="flex items-center gap-4">
                <a href="my_support_tickets.php" class="text-sm font-bold text-slate-500 hover:text-arogga">
                    <i class="fas fa-list mr-2"></i>My Tickets
                </a>
                <a href="dashboard.php" class="text-sm font-bold text-slate-500 hover:text-arogga">
                    <i class="fas fa-arrow-left mr-2"></i>Back
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-12 max-w-4xl">
        <div class="bg-white rounded-[36px] shadow-xl border border-slate-100 p-8 md:p-12">
            <div class="mb-8 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-violet-100 text-violet-600 rounded-full text-2xl mb-4">
                    <i class="fas fa-headset"></i>
                </div>
                <h1 class="text-3xl font-black text-slate-800 uppercase tracking-tight">Contact Support</h1>
                <p class="text-sm text-slate-500 mt-2">Tell us about an issue, delivery concern, prescription problem, or account question.</p>
            </div>

            <form action="admin_actions.php" method="POST" class="space-y-6">
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Subject</label>
                        <input type="text" name="subject" placeholder="Issue summary" required class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-teal-500 font-bold text-slate-700">
                    </div>
                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Category</label>
                        <select name="category" class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-teal-500 font-bold text-slate-700">
                            <option value="General">General</option>
                            <option value="Order">Order</option>
                            <option value="Payment">Payment</option>
                            <option value="Prescription">Prescription</option>
                            <option value="Account">Account</option>
                            <option value="Technical">Technical</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Priority</label>
                    <div class="grid grid-cols-3 gap-4">
                        <label class="cursor-pointer">
                            <input type="radio" name="priority" value="Low" class="peer sr-only" checked>
                            <div class="peer-checked:border-green-500 peer-checked:bg-green-50 border border-slate-200 bg-slate-50 rounded-2xl p-3 text-center font-black text-green-600">Low</div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="priority" value="Medium" class="peer sr-only">
                            <div class="peer-checked:border-amber-500 peer-checked:bg-amber-50 border border-slate-200 bg-slate-50 rounded-2xl p-3 text-center font-black text-amber-600">Medium</div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="priority" value="High" class="peer sr-only">
                            <div class="peer-checked:border-red-500 peer-checked:bg-red-50 border border-slate-200 bg-slate-50 rounded-2xl p-3 text-center font-black text-red-600">High</div>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Message</label>
                    <textarea name="message" rows="6" placeholder="Describe the issue in detail..." required class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-teal-500 font-medium text-slate-700"></textarea>
                </div>

                <button type="submit" name="create_support_ticket" class="w-full bg-slate-900 text-white py-4 rounded-2xl font-black uppercase tracking-widest text-xs hover:bg-arogga transition-all shadow-lg">
                    Submit Ticket <i class="fas fa-arrow-right ml-2"></i>
                </button>
            </form>
        </div>
    </div>
</body>
</html>
