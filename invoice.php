<?php
include 'config.php';

$order_id = isset($_GET['id']) ? $_GET['id'] : die("Error: Order ID missing.");

// Order Details Fetch
$order_query = $pdo->prepare("SELECT o.*, u.full_name, u.address, u.phone 
                              FROM orders o 
                              JOIN users u ON o.user_id = u.user_id 
                              WHERE o.order_id = ?");
$order_query->execute([$order_id]);
$order = $order_query->fetch();

if (!$order) {
    die("Error: Order not found.");
}

// Order Items Fetch
$items_query = $pdo->prepare("SELECT oi.*, m.medicine_name, m.generic_name 
                               FROM order_items oi 
                               JOIN medicines m ON oi.medicine_id = m.medicine_id 
                               WHERE oi.order_id = ?");
$items_query->execute([$order_id]);
$items = $items_query->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #<?= $order_id ?> | MediVault</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
        .bg-teal-brand { background-color: #0d9488; }
        .text-teal-brand { color: #0d9488; }
        
        @media print {
            .no-print { display: none !important; }
            body { 
                background-color: white; 
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;        
            }
            .invoice-container { box-shadow: none; border: none; padding: 0; margin: 0; max-width: 100%; }
        }
    </style>
</head>
<body class="py-10 px-4">

    <div class="max-w-4xl mx-auto bg-white shadow-lg rounded-lg overflow-hidden invoice-container border border-gray-200">
        
        <div class="bg-slate-900 text-white p-8 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold tracking-tight flex items-center gap-2">
                    <i class="fas fa-heartbeat text-teal-400"></i> MediVault
                </h1>
                <p class="text-slate-400 text-sm mt-1">Trusted Digital Pharmacy</p>
            </div>
            <div class="text-right">
                <h2 class="text-2xl font-bold uppercase tracking-widest text-slate-200">Invoice</h2>
                <p class="text-teal-400 font-mono font-bold mt-1">#INV-<?= str_pad($order_id, 6, '0', STR_PAD_LEFT) ?></p>
            </div>
        </div>

        <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8 border-b border-gray-100">
            <div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Billed To</h3>
                <div class="text-slate-800">
                    <p class="text-lg font-bold"><?= htmlspecialchars($order['full_name']) ?></p>
                    <p class="text-sm text-gray-600 mt-1"><i class="fas fa-map-marker-alt w-4 text-center mr-1"></i> <?= htmlspecialchars($order['address']) ?></p>
                    <p class="text-sm text-gray-600 mt-1"><i class="fas fa-phone w-4 text-center mr-1"></i> <?= htmlspecialchars($order['phone']) ?></p>
                </div>
            </div>

            <div class="md:text-right">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Order Details</h3>
                <div class="space-y-2">
                    <div class="flex justify-between md:justify-end gap-4">
                        <span class="text-sm text-gray-500">Order Date:</span>
                        <span class="text-sm font-bold text-slate-800"><?= date('M d, Y', strtotime($order['order_date'])) ?></span>
                    </div>
                    <div class="flex justify-between md:justify-end gap-4">
                        <span class="text-sm text-gray-500">Payment Type:</span>
                        <span class="text-sm font-bold text-slate-800"><?= $order['order_type'] ?? 'Cash On Delivery' ?></span>
                    </div>
                    <div class="flex justify-between md:justify-end gap-4 items-center">
                        <span class="text-sm text-gray-500">Status:</span>
                        <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase 
                            <?= $order['order_status'] == 'Pending' ? 'bg-orange-100 text-orange-600' : 
                               ($order['order_status'] == 'Confirmed' ? 'bg-blue-100 text-blue-600' : 
                               ($order['order_status'] == 'Delivered' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600')) ?>">
                            <?= $order['order_status'] ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-8">
            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="w-full text-left text-sm text-gray-600">
                    <thead class="bg-gray-50 text-xs uppercase font-bold text-gray-500">
                        <tr>
                            <th class="px-6 py-4">Item Description</th>
                            <th class="px-6 py-4 text-center">Qty</th>
                            <th class="px-6 py-4 text-right">Unit Price</th>
                            <th class="px-6 py-4 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach($items as $item): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <p class="font-bold text-slate-800"><?= htmlspecialchars($item['medicine_name']) ?></p>
                                <p class="text-xs text-gray-400 mt-0.5"><?= htmlspecialchars($item['generic_name']) ?></p>
                            </td>
                            <td class="px-6 py-4 text-center font-medium"><?= $item['quantity'] ?></td>
                            <td class="px-6 py-4 text-right"><?= number_format($item['unit_price'], 2) ?> ৳</td>
                            <td class="px-6 py-4 text-right font-bold text-slate-800"><?= number_format($item['subtotal'], 2) ?> ৳</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="px-8 pb-8 flex justify-end">
            <div class="w-full md:w-1/3 bg-gray-50 rounded-xl p-6 border border-gray-100">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm text-gray-500 font-medium">Subtotal</span>
                    <span class="text-sm font-bold text-slate-800"><?= number_format($order['total_amount'], 2) ?> ৳</span>
                </div>
                <div class="flex justify-between items-center mb-4 pb-4 border-b border-gray-200">
                    <span class="text-sm text-gray-500 font-medium">Discount</span>
                    <span class="text-sm font-bold text-slate-800">0.00 ৳</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-base font-bold text-slate-800 uppercase">Grand Total</span>
                    <span class="text-2xl font-black text-teal-600"><?= number_format($order['total_amount'], 2) ?> ৳</span>
                </div>
            </div>
        </div>

        <div class="bg-slate-50 p-6 text-center border-t border-gray-200">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Thank you for choosing MediVault!</p>
            <p class="text-[10px] text-gray-400">For support, contact us at help@medivault.com or call +880 1234 567890</p>
        </div>
    </div>

    <div class="max-w-4xl mx-auto mt-6 flex justify-end gap-4 no-print px-4 md:px-0">
        <a href="dashboard.php" class="bg-white border border-gray-300 text-slate-600 px-6 py-3 rounded-lg font-bold text-sm hover:bg-gray-50 transition-all shadow-sm">
            <i class="fas fa-arrow-left mr-2"></i> Back
        </a>
        <button onclick="window.print()" class="bg-slate-900 text-white px-6 py-3 rounded-lg font-bold text-sm hover:bg-black transition-all shadow-lg flex items-center">
            <i class="fas fa-print mr-2"></i> Print Invoice
        </button>
    </div>

</body>
</html>