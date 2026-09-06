<?php
include 'config.php';

if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) != 'admin') {
    header('Location: admin_login.php');
    exit();
}

if (isset($_GET['download']) && $_GET['download'] === 'csv') {
    $stmt = $pdo->query("SELECT o.order_id, u.full_name, o.order_date, o.order_type, o.total_amount, o.order_status
        FROM orders o
        JOIN users u ON u.user_id = o.user_id
        ORDER BY o.order_date DESC");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="medivault_sales_report.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Order ID', 'Customer', 'Order Date', 'Order Type', 'Total Amount', 'Status']);
    foreach ($rows as $row) {
        fputcsv($output, [
            $row['order_id'],
            $row['full_name'],
            $row['order_date'],
            $row['order_type'],
            number_format((float) $row['total_amount'], 2, '.', ''),
            $row['order_status'],
        ]);
    }
    fclose($output);
    exit();
}

$report = $pdo->query("SELECT COUNT(order_id) as total_orders, SUM(total_amount) as total_revenue, ROUND(AVG(total_amount), 2) as avg_order_value FROM orders WHERE order_status = 'Delivered'")->fetch();
$recentOrders = $pdo->query("SELECT o.order_id, u.full_name, o.order_date, o.order_type, o.total_amount, o.order_status FROM orders o JOIN users u ON u.user_id = o.user_id ORDER BY o.order_date DESC LIMIT 10")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Report | MediVault</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f3f4f6; }
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
        }
    </style>
</head>
<body class="min-h-screen px-4 py-10">
    <div class="max-w-6xl mx-auto">
        <div class="bg-white rounded-[32px] shadow-sm border border-slate-200 overflow-hidden">
            <div class="bg-slate-900 text-white p-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <p class="text-[10px] uppercase tracking-[0.3em] text-slate-300 font-black">MediVault</p>
                    <h1 class="text-3xl font-black mt-2">Pharmacy Sales Report</h1>
                </div>
                <div class="flex gap-3 no-print">
                    <a href="adminpanel.php" class="bg-white/10 border border-white/10 text-white px-5 py-3 rounded-xl font-bold text-xs uppercase hover:bg-white/20 transition-all">
                        <i class="fas fa-arrow-left mr-2"></i> Back
                    </a>
                    <a href="export_reports.php?download=csv" class="bg-emerald-500 text-white px-5 py-3 rounded-xl font-bold text-xs uppercase hover:bg-emerald-400 transition-all">
                        <i class="fas fa-download mr-2"></i> Export CSV
                    </a>
                    <button onclick="window.print()" class="bg-white text-slate-900 px-5 py-3 rounded-xl font-bold text-xs uppercase hover:bg-slate-100 transition-all">
                        <i class="fas fa-print mr-2"></i> Print
                    </button>
                </div>
            </div>

            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-6">
                        <p class="text-[10px] uppercase tracking-[0.25em] text-emerald-500 font-black">Total Revenue</p>
                        <h2 class="text-3xl font-black text-emerald-700 mt-4"><?= number_format((float) ($report['total_revenue'] ?? 0), 2) ?> ৳</h2>
                    </div>
                    <div class="bg-sky-50 border border-sky-100 rounded-2xl p-6">
                        <p class="text-[10px] uppercase tracking-[0.25em] text-sky-500 font-black">Completed Orders</p>
                        <h2 class="text-3xl font-black text-sky-700 mt-4"><?= (int) ($report['total_orders'] ?? 0) ?></h2>
                    </div>
                    <div class="bg-amber-50 border border-amber-100 rounded-2xl p-6">
                        <p class="text-[10px] uppercase tracking-[0.25em] text-amber-500 font-black">Avg. Order</p>
                        <h2 class="text-3xl font-black text-amber-700 mt-4"><?= number_format((float) ($report['avg_order_value'] ?? 0), 2) ?> ৳</h2>
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-slate-200">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-[10px] uppercase tracking-[0.2em] text-slate-400 font-black">
                            <tr>
                                <th class="p-4">Order</th>
                                <th class="p-4">Customer</th>
                                <th class="p-4">Date</th>
                                <th class="p-4">Payment</th>
                                <th class="p-4 text-right">Amount</th>
                                <th class="p-4 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td class="p-4 font-bold text-slate-800">#<?= $order['order_id'] ?></td>
                                    <td class="p-4 text-slate-600"><?= htmlspecialchars($order['full_name']) ?></td>
                                    <td class="p-4 text-slate-500"><?= date('d M Y', strtotime($order['order_date'])) ?></td>
                                    <td class="p-4 text-slate-500"><?= htmlspecialchars($order['order_type']) ?></td>
                                    <td class="p-4 text-right font-black text-emerald-600"><?= number_format((float) $order['total_amount'], 2) ?> ৳</td>
                                    <td class="p-4 text-right">
                                        <span class="inline-flex px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider
                                            <?= $order['order_status'] == 'Delivered' ? 'bg-emerald-100 text-emerald-700' :
                                               ($order['order_status'] == 'Pending' ? 'bg-orange-100 text-orange-600' :
                                               ($order['order_status'] == 'Cancelled' ? 'bg-red-100 text-red-600' : 'bg-blue-100 text-blue-600')) ?>">
                                            <?= htmlspecialchars($order['order_status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
