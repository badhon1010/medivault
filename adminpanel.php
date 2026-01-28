<?php
include 'config.php';

if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) != 'admin') {
    header("Location: admin_login.php"); exit();
}

// Data Fetching Queries (Unchanged Logic)
$inventory = $pdo->query("SELECT m.*, c.category_name, b.total_stock, b.expiry_date 
    FROM medicines m 
    LEFT JOIN categories c ON m.category_id = c.category_id 
    LEFT JOIN (SELECT medicine_id, SUM(quantity_instock) as total_stock, MAX(expiry_date) as expiry_date 
               FROM inventory_batches GROUP BY medicine_id) b ON m.medicine_id = b.medicine_id")->fetchAll();

$orders = $pdo->query("SELECT o.*, u.full_name, p.image_path 
    FROM orders o 
    JOIN users u ON o.user_id = u.user_id 
    LEFT JOIN prescriptions p ON o.prescription_id = p.prescription_id 
    ORDER BY o.order_date DESC")->fetchAll();

$order_items_query = $pdo->query("SELECT oi.*, m.medicine_name 
    FROM order_items oi 
    JOIN medicines m ON oi.medicine_id = m.medicine_id");
$all_items = $order_items_query->fetchAll(PDO::FETCH_ASSOC);

$order_details_map = [];
foreach($all_items as $item) {
    $order_details_map[$item['order_id']][] = $item;
}

$batches_query = $pdo->query("SELECT * FROM inventory_batches ORDER BY expiry_date ASC");
$all_batches = $batches_query->fetchAll(PDO::FETCH_ASSOC);
$medicine_batches = [];
foreach($all_batches as $b) {
    $medicine_batches[$b['medicine_id']][] = $b;
}

$coupons = $pdo->query("SELECT * FROM coupons ORDER BY expiry_date DESC")->fetchAll();
$reviews = $pdo->query("SELECT r.*, u.full_name, m.medicine_name FROM medicine_reviews r 
    JOIN users u ON r.user_id = u.user_id 
    JOIN medicines m ON r.medicine_id = m.medicine_id ORDER BY r.created_at DESC")->fetchAll();
$suppliers = $pdo->query("SELECT * FROM suppliers")->fetchAll();
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
$interactions = $pdo->query("SELECT di.*, m1.medicine_name as med1, m2.medicine_name as med2 
    FROM drug_interactions di 
    JOIN medicines m1 ON di.medicine_id_1 = m1.medicine_id 
    JOIN medicines m2 ON di.medicine_id_2 = m2.medicine_id")->fetchAll();
$report = $pdo->query("SELECT COUNT(order_id) as total_orders, SUM(total_amount) as total_revenue FROM orders WHERE order_status = 'Delivered'")->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MediVault Admin | Full Control</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>.active-tab { background: #16a34a !important; font-weight: bold; transform: scale(1.05); }</style>
</head>
<body class="bg-gray-100 flex min-h-screen font-sans">

    <div class="w-72 bg-slate-900 p-8 text-white sticky top-0 h-screen shadow-2xl flex flex-col">
        <h2 class="text-3xl font-black italic text-green-400 mb-12 tracking-tighter uppercase text-center">MEDIVAULT</h2>
        <nav class="space-y-3 flex-1 overflow-y-auto pr-2">
            <button onclick="showTab('inv')" class="tab-btn active-tab w-full text-left py-4 px-6 rounded-2xl flex items-center transition-all"><i class="fas fa-boxes mr-4"></i> Inventory</button>
            <button onclick="showTab('ord')" class="tab-btn w-full text-left py-4 px-6 rounded-2xl flex items-center transition-all"><i class="fas fa-shopping-cart mr-4 text-blue-400"></i> Orders</button>
            <button onclick="showTab('stock')" class="tab-btn w-full text-left py-4 px-6 rounded-2xl flex items-center transition-all"><i class="fas fa-truck-loading mr-4 text-purple-400"></i> Add Stock</button>
            <button onclick="showTab('coup')" class="tab-btn w-full text-left py-4 px-6 rounded-2xl flex items-center transition-all"><i class="fas fa-ticket-alt mr-4 text-pink-400"></i> Coupons</button>
            <button onclick="showTab('rev')" class="tab-btn w-full text-left py-4 px-6 rounded-2xl flex items-center transition-all"><i class="fas fa-star mr-4 text-yellow-400"></i> Reviews</button>
            <button onclick="showTab('int')" class="tab-btn w-full text-left py-4 px-6 rounded-2xl flex items-center transition-all"><i class="fas fa-exclamation-triangle mr-4 text-red-400"></i> Conflicts</button>
            <button onclick="showTab('rep')" class="tab-btn w-full text-left py-4 px-6 rounded-2xl flex items-center transition-all"><i class="fas fa-chart-line mr-4 text-orange-400"></i> Reports</button>
        </nav>
        <div class="pt-10 border-t border-slate-800"><a href="logout.php" class="flex items-center py-3 px-6 text-red-400 font-bold"><i class="fas fa-power-off mr-4"></i> Logout</a></div>
    </div>

    <div class="flex-1 p-10 pt-20 overflow-y-auto bg-slate-50">
        
        <div id="inv-tab" class="content-tab space-y-8">
            <div class="flex justify-between items-center">
                <h1 class="text-3xl font-black text-slate-800 uppercase tracking-tighter italic">Inventory Control</h1>
                <button onclick="document.getElementById('addMedModal').classList.remove('hidden')" class="bg-slate-900 text-white px-6 py-3 rounded-2xl font-black text-xs uppercase shadow-xl">+ New Medicine</button>
            </div>
            <div class="bg-white rounded-[35px] shadow-sm overflow-hidden border">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 border-b text-[10px] uppercase font-black text-slate-400 tracking-widest">
                        <tr><th class="p-8">Medicine Name</th><th class="p-8 text-center">Stock</th><th class="p-8">Expiry</th><th class="p-8 text-center">Action</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php foreach($inventory as $item): ?>
                        <tr class="hover:bg-gray-50 transition-all">
                            <td class="p-8 font-bold text-slate-800"><?= $item['medicine_name'] ?><br><span class="text-[10px] text-slate-400 uppercase"><?= $item['generic_name'] ?></span></td>
                            <td class="p-8 text-center"><span class="px-4 py-1.5 rounded-full text-[10px] font-black <?= $item['total_stock'] < $item['min_stock_level'] ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600' ?>"><?= $item['total_stock'] ?? 0 ?> UNITS</span></td>
                            <td class="p-8 text-sm font-bold text-slate-500 italic"><?= $item['expiry_date'] ?? 'N/A' ?></td>
                            <td class="p-8 text-center flex justify-center space-x-3">
                                <button onclick='showBatchDetails(<?= json_encode($medicine_batches[$item['medicine_id']] ?? []) ?>)' 
                                        class="text-purple-500 hover:scale-110 transition-transform" title="View Batches">
                                    <i class="fas fa-layer-group"></i>
                                </button>
                                <button onclick='openEditMedModal(<?= json_encode($item) ?>)' class="text-blue-500 hover:scale-110 transition-transform">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="addMedModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-[60] flex items-center justify-center p-4">
        <div class="bg-white p-10 rounded-[45px] shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <h2 class="text-2xl font-black text-slate-800 mb-8 uppercase tracking-tighter italic">Add New Medicine</h2>
            <form action="admin_actions.php" method="POST" class="space-y-4">
                <input type="hidden" name="action" value="add_medicine"> 
                
                <div class="grid grid-cols-2 gap-4">
                    <input type="text" name="medicine_name" placeholder="Medicine Name" required class="p-4 bg-slate-50 rounded-2xl outline-none font-bold text-sm">
                    <input type="text" name="generic_name" placeholder="Generic Name" required class="p-4 bg-slate-50 rounded-2xl outline-none font-bold text-sm">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <select name="category_id" class="p-4 bg-slate-50 rounded-2xl outline-none font-bold text-sm">
                        <?php foreach($categories as $c): ?><option value="<?= $c['category_id'] ?>"><?= $c['category_name'] ?></option><?php endforeach; ?>
                    </select>
                    <input type="number" step="0.01" name="unit_price" placeholder="Unit Price" required class="p-4 bg-slate-50 rounded-2xl outline-none font-bold text-sm">
                </div>

                <input type="number" name="min_stock_level" placeholder="Min Stock Level" class="w-full p-4 bg-slate-50 rounded-2xl outline-none font-bold text-sm">
                
                <textarea name="indications" placeholder="Indications (e.g., Fever, Headache, Pain)" class="w-full p-4 bg-slate-50 rounded-2xl outline-none font-bold text-sm h-20"></textarea>
                <textarea name="description" placeholder="Full Description / Dosage Info" class="w-full p-4 bg-slate-50 rounded-2xl outline-none font-bold text-sm h-24"></textarea>

                <div class="flex gap-4 pt-4">
                    <button type="button" onclick="document.getElementById('addMedModal').classList.add('hidden')" class="flex-1 bg-gray-100 text-gray-500 py-4 rounded-2xl font-black uppercase text-xs">Cancel</button>
                    <button type="submit" name="add_medicine" class="flex-1 bg-green-600 text-white py-4 rounded-2xl font-black uppercase text-xs shadow-xl">Save Medicine</button>
                </div>
            </form>
        </div>
    </div>

        <div id="ord-tab" class="content-tab hidden space-y-8">
            <h1 class="text-3xl font-black text-slate-800 uppercase italic tracking-tighter">Orders & Prescriptions</h1>
            <div class="grid grid-cols-1 gap-6">
                <?php foreach($orders as $order): ?>
                <div class="bg-white p-8 rounded-[40px] border flex flex-col md:flex-row justify-between items-center shadow-sm hover:shadow-xl transition-all border-l-8 <?= $order['order_status'] == 'Pending' ? 'border-l-orange-400' : 'border-l-green-500' ?>">
                    <div class="flex-1 cursor-pointer" onclick='showOrderItems(<?= $order['order_id'] ?>, <?= json_encode($order_details_map[$order['order_id']] ?? []) ?>)'>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Order #<?= $order['order_id'] ?> | <?= date('d M Y', strtotime($order['order_date'])) ?></p>
                        <h3 class="text-xl font-black text-slate-800 mt-1"><?= htmlspecialchars($order['full_name']) ?></h3>
                        <div class="flex items-center space-x-3 mt-2">
                            <span class="text-sm font-black text-green-600"><?= $order['total_amount'] ?> ৳</span>
                            <span class="px-3 py-1 bg-slate-100 rounded-full text-[10px] font-black text-slate-500 uppercase"><?= $order['order_status'] ?></span>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-4 mt-6 md:mt-0">
                        <?php if(!empty($order['image_path'])): ?>
                            <a href="<?= $order['image_path'] ?>" target="_blank" 
                               class="bg-blue-50 text-blue-600 px-6 py-3 rounded-2xl font-black text-[10px] uppercase hover:bg-blue-600 hover:text-white transition-all flex items-center">
                                <i class="fas fa-file-medical mr-2 text-sm"></i> View Prescription
                            </a>
                        <?php else: ?>
                            <span class="text-[10px] font-bold text-gray-300 uppercase italic">No Prescription</span>
                        <?php endif; ?>

                        <form action="admin_actions.php" method="POST" class="flex items-center space-x-2 border-l pl-4 border-gray-100">
                            <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                            <select name="status" class="bg-gray-50 p-3 rounded-xl text-xs font-black border-none outline-none ring-1 ring-gray-100">
                                <option value="Pending" <?= $order['order_status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="Confirmed" <?= $order['order_status'] == 'Confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                <option value="Delivered" <?= $order['order_status'] == 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                                <option value="Cancelled" <?= $order['order_status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                            <button type="submit" name="update_order_status" class="bg-slate-900 text-white p-3.5 rounded-xl hover:bg-green-600 transition-all shadow-lg shadow-gray-200">
                                <i class="fas fa-check"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div id="stock-tab" class="content-tab hidden space-y-8">
            <h1 class="text-3xl font-black text-slate-800 uppercase tracking-tighter italic">Supplier Stock Management</h1>
            <div class="bg-white p-10 rounded-[40px] shadow-sm border max-w-2xl">
                <form action="admin_stock.php" method="POST" class="space-y-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="text-[10px] font-black text-gray-400 uppercase ml-1">Select Medicine</label>
                            <select name="medicine_id" class="w-full p-4 bg-gray-50 rounded-2xl outline-none border-none font-bold text-sm">
                                <?php foreach($inventory as $m): ?><option value="<?= $m['medicine_id'] ?>"><?= $m['medicine_name'] ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div><label class="text-[10px] font-black text-gray-400 uppercase ml-1">Supplier</label>
                            <select name="supplier_id" class="w-full p-4 bg-gray-50 rounded-2xl outline-none border-none font-bold text-sm">
                                <?php foreach($suppliers as $s): ?><option value="<?= $s['supplier_id'] ?>"><?= $s['supplier_name'] ?></option><?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <input type="number" name="quantity" placeholder="Quantity (Units)" required class="p-4 bg-gray-50 rounded-2xl font-bold text-sm border-none outline-none">
                        
                        <input type="number" step="0.01" name="purchase_price" placeholder="Purchase Price (Cost)" required class="p-4 bg-gray-50 rounded-2xl font-bold text-sm border-none outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <input type="number" step="0.01" name="selling_price" placeholder="Selling Price (Unit Price)" required class="p-4 bg-gray-50 rounded-2xl font-bold text-sm border-none outline-none">
                        
                        <input type="date" name="expiry_date" required class="p-4 bg-gray-50 rounded-2xl font-bold text-sm border-none outline-none">
                    </div>

                    <button type="submit" name="add_stock" class="w-full bg-green-600 text-white py-5 rounded-[25px] font-black uppercase tracking-widest hover:bg-green-700 shadow-xl shadow-green-100 transition-all">Update Stock</button>
                </form>
            </div>
        </div>

        <div id="coup-tab" class="content-tab hidden space-y-8">
            <h1 class="text-3xl font-black text-slate-800 uppercase tracking-tighter italic">Coupon Management</h1>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="bg-white p-8 rounded-[35px] border shadow-sm">
                    <h3 class="text-sm font-black mb-6 uppercase text-gray-400">New Coupon</h3>
                    <form action="admin_actions.php" method="POST" class="space-y-4">
                        <input type="text" name="coupon_code" placeholder="Code (e.g., SAVE20)" required class="w-full p-4 bg-gray-50 rounded-2xl font-black text-sm border-none outline-none">
                        <div class="grid grid-cols-2 gap-4">
                            <input type="number" name="discount" placeholder="Discount (%)" required class="p-4 bg-gray-50 rounded-2xl font-black text-sm border-none outline-none">
                            <input type="number" name="min_amount" placeholder="Min Order (Tk)" required class="p-4 bg-gray-50 rounded-2xl font-black text-sm border-none outline-none">
                        </div>
                        <input type="date" name="expiry" required class="w-full p-4 bg-gray-50 rounded-2xl border-none outline-none font-bold text-sm">
                        <button type="submit" name="add_coupon" class="w-full bg-slate-900 text-white py-4 rounded-2xl font-black uppercase tracking-widest text-xs shadow-xl">Save Coupon</button>
                    </form>
                </div>
                <div class="space-y-4">
                    <?php foreach($coupons as $c): ?>
                    <div class="bg-white p-6 rounded-3xl border flex justify-between items-center shadow-sm border-l-8 border-l-pink-400">
                        <div>
                            <p class="text-xl font-black text-slate-800"><?= $c['coupon_code'] ?></p>
                            <p class="text-[10px] text-gray-400 font-bold uppercase">Disc: <?= $c['discount_percent'] ?>% | Min: <?= $c['min_order_amount'] ?> Tk</p>
                        </div>
                        <form action="admin_actions.php" method="POST"><input type="hidden" name="coupon_id" value="<?= $c['coupon_id'] ?>"><button type="submit" name="delete_coupon" class="text-red-300 hover:text-red-500 transition-all"><i class="fas fa-trash-alt"></i></button></form>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div id="rev-tab" class="content-tab hidden space-y-8">
            <h1 class="text-3xl font-black text-slate-800 uppercase tracking-tighter italic">User Reviews</h1>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php foreach($reviews as $r): ?>
                <div class="bg-white p-8 rounded-[40px] border shadow-sm border-t-8 border-t-yellow-400">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h4 class="font-black text-slate-800"><?= $r['medicine_name'] ?></h4>
                            <p class="text-xs text-slate-400 font-bold tracking-tight">User: <?= $r['full_name'] ?></p>
                        </div>
                        <div class="text-yellow-400 flex space-x-1">
                            <?php for($i=1; $i<=5; $i++): ?><i class="fas fa-star text-[10px] <?= $i <= $r['rating'] ? 'text-yellow-400' : 'text-gray-100' ?>"></i><?php endfor; ?>
                        </div>
                    </div>
                    <p class="text-sm text-slate-600 font-medium italic leading-relaxed">"<?= $r['comment'] ?>"</p>
                    <p class="text-[9px] text-gray-300 font-bold uppercase mt-4"><?= date('d M, Y', strtotime($r['created_at'])) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div id="int-tab" class="content-tab hidden space-y-4">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-black text-slate-800 uppercase tracking-tighter italic">Drug Conflicts</h1>
                <button onclick="document.getElementById('addIntModal').classList.remove('hidden')" class="bg-red-600 text-white px-5 py-2 rounded-xl font-black text-[10px] uppercase shadow-md hover:bg-red-700 transition-all">+ New Conflict</button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach($interactions as $i): ?>
                <div class="bg-white p-5 rounded-3xl border shadow-sm relative group border-t-4 <?= $i['severity'] == 'High' ? 'border-t-red-500' : 'border-t-blue-400' ?>">
                    <div class="flex justify-between items-start mb-2">
                        <span class="px-3 py-1 rounded-full text-[8px] font-black uppercase tracking-widest <?= $i['severity'] == 'High' ? 'bg-red-100 text-red-600' : 'bg-blue-100 text-blue-600' ?>"><?= $i['severity'] ?> Severity</span>
                        <div class="flex space-x-2">
                            <button onclick='openEditIntModal(<?= json_encode($i) ?>)' class="text-gray-300 hover:text-blue-500"><i class="fas fa-edit text-xs"></i></button>
                            <form action="admin_actions.php" method="POST"><input type="hidden" name="interaction_id" value="<?= $i['interaction_id'] ?>"><button type="submit" name="delete_interaction" class="text-gray-300 hover:text-red-500"><i class="fas fa-trash text-xs"></i></button></form>
                        </div>
                    </div>
                    <h4 class="text-base font-black text-slate-800 leading-tight"><?= $i['med1'] ?> + <?= $i['med2'] ?></h4>
                    <p class="text-[11px] text-slate-500 font-medium mt-2 leading-snug italic line-clamp-2"><?= $i['warning_description'] ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div id="rep-tab" class="content-tab hidden space-y-8">
            <h1 class="text-4xl font-black text-slate-800 uppercase tracking-tighter italic">Sales Report</h1>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="bg-gradient-to-br from-green-500 to-green-600 p-12 rounded-[50px] text-white shadow-2xl relative overflow-hidden">
                    <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full"></div>
                    <p class="text-xs font-black uppercase tracking-widest opacity-70">Total Revenue (Delivered)</p>
                    <h2 class="text-6xl font-black mt-4"><?= number_format($report['total_revenue'], 2) ?> ৳</h2>
                </div>
                <div class="bg-slate-900 p-12 rounded-[50px] text-white shadow-2xl relative overflow-hidden">
                    <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-white/5 rounded-full"></div>
                    <p class="text-xs font-black uppercase tracking-widest opacity-70">Successful Deliveries</p>
                    <h2 class="text-6xl font-black mt-4"><?= $report['total_orders'] ?> Orders</h2>
                </div>
            </div>
        </div>
    </div>

    <div id="batchModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-[100] flex items-center justify-center p-4">
        <div class="bg-white p-10 rounded-[40px] shadow-2xl w-full max-w-3xl">
            <div class="flex justify-between items-center mb-8 border-b pb-4">
                <h2 class="text-2xl font-black text-slate-800 italic uppercase tracking-tighter">Batch & Stock Details</h2>
                <button onclick="document.getElementById('batchModal').classList.add('hidden')" class="text-gray-400 hover:text-red-500"><i class="fas fa-times-circle text-2xl"></i></button>
            </div>
            <div class="overflow-hidden rounded-3xl border border-gray-50">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                        <tr><th class="p-5">Batch No</th><th class="p-5 text-center">Stock (Units)</th><th class="p-5">Expiry Date</th><th class="p-5 text-right">Purchase Price</th></tr>
                    </thead>
                    <tbody id="batchListBody" class="divide-y divide-gray-50"></tbody>
                </table>
            </div>
            <div class="mt-8 flex justify-end">
                <button onclick="document.getElementById('batchModal').classList.add('hidden')" class="bg-slate-900 text-white px-10 py-4 rounded-2xl font-black uppercase text-xs">Close</button>
            </div>
        </div>
    </div>

    <div id="orderItemsModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-[100] flex items-center justify-center p-4">
        <div class="bg-white p-10 rounded-[40px] shadow-2xl w-full max-w-2xl">
            <h2 class="text-2xl font-black text-slate-800 italic uppercase mb-8 border-b pb-4">Order Item Details</h2>
            <div class="overflow-hidden rounded-3xl border border-gray-50">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 text-[10px] font-black text-slate-400 uppercase tracking-widest"><tr><th class="p-5">Medicine</th><th class="p-5 text-center">Quantity</th><th class="p-5 text-right">Price</th></tr></thead>
                    <tbody id="itemsListBody" class="divide-y divide-gray-50"></tbody>
                </table>
            </div>
            <div class="mt-8 flex justify-end">
                <button onclick="document.getElementById('orderItemsModal').classList.add('hidden')" class="bg-slate-900 text-white px-10 py-4 rounded-2xl font-black uppercase text-xs">Close</button>
            </div>
        </div>
    </div>

    <div id="editMedModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-[70] flex items-center justify-center p-4">
        <div class="bg-white p-10 rounded-[35px] shadow-2xl w-full max-w-lg border border-blue-100">
            <h2 class="text-2xl font-black text-slate-800 mb-6 uppercase tracking-tighter italic">Edit Medicine Details</h2>
            <form action="admin_actions.php" method="POST" class="space-y-4">
                <input type="hidden" name="medicine_id" id="editMedId">
                <input type="text" name="medicine_name" id="editMedName" placeholder="Name" required class="w-full p-4 bg-slate-50 rounded-2xl outline-none font-bold text-sm">
                <div class="grid grid-cols-2 gap-4">
                    <input type="number" step="0.01" name="unit_price" id="editMedPrice" placeholder="Price" required class="p-4 bg-slate-50 rounded-2xl outline-none font-bold text-sm">
                    <input type="number" name="min_stock" id="editMedMinStock" placeholder="Min Stock" required class="p-4 bg-slate-50 rounded-2xl outline-none font-bold text-sm">
                </div>
                <div class="flex gap-4 pt-4">
                    <button type="button" onclick="document.getElementById('editMedModal').classList.add('hidden')" class="flex-1 bg-gray-100 text-gray-500 py-4 rounded-2xl font-black uppercase text-xs">Cancel</button>
                    <button type="submit" name="update_medicine" class="flex-1 bg-blue-600 text-white py-4 rounded-2xl font-black uppercase text-xs shadow-xl">Update</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showBatchDetails(batches) {
            const body = document.getElementById('batchListBody');
            body.innerHTML = '';
            
            if(!batches || batches.length === 0) {
                body.innerHTML = '<tr><td colspan="4" class="p-8 text-center text-gray-400 font-bold uppercase italic">No batch data available!</td></tr>';
            } else {
                batches.forEach(b => {
                    const expiryDate = new Date(b.expiry_date);
                    const today = new Date();
                    const isExpired = expiryDate < today;

                    body.innerHTML += `
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-5 font-black text-slate-800">${b.batch_number}</td>
                            <td class="p-5 text-center font-bold text-green-600">${b.quantity_instock} Units</td>
                            <td class="p-5 font-bold ${isExpired ? 'text-red-500' : 'text-slate-500'}">${b.expiry_date}</td>
                            <td class="p-5 text-right font-black text-slate-900">${b.purchase_price} ৳</td>
                        </tr>
                    `;
                });
            }
            document.getElementById('batchModal').classList.remove('hidden');
        }

        function showOrderItems(orderId, items) {
            const body = document.getElementById('itemsListBody');
            body.innerHTML = '';
            if(!items || items.length === 0) {
                body.innerHTML = '<tr><td colspan="3" class="p-8 text-center text-gray-400 font-bold italic">No items found!</td></tr>';
            } else {
                items.forEach(item => {
                    body.innerHTML += `<tr class="hover:bg-slate-50"><td class="p-5 font-bold text-slate-800">${item.medicine_name}</td><td class="p-5 text-center font-black text-green-600">${item.quantity} Pcs</td><td class="p-5 text-right font-bold text-slate-500">${item.subtotal} ৳</td></tr>`;
                });
            }
            document.getElementById('orderItemsModal').classList.remove('hidden');
        }

        function showTab(tab) {
            document.querySelectorAll('.content-tab').forEach(t => t.classList.add('hidden'));
            document.getElementById(tab + '-tab').classList.remove('hidden');
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active-tab'));
            event.currentTarget.classList.add('active-tab');
        }

        function openEditMedModal(item) {
            document.getElementById('editMedId').value = item.medicine_id;
            document.getElementById('editMedName').value = item.medicine_name;
            document.getElementById('editMedPrice').value = item.unit_price;
            document.getElementById('editMedMinStock').value = item.min_stock_level;
            document.getElementById('editMedModal').classList.remove('hidden');
        }

        function openEditIntModal(item) {
            document.getElementById('editIntId').value = item.interaction_id;
            document.getElementById('editIntSeverity').value = item.severity;
            document.getElementById('editIntDesc').value = item.warning_description;
            document.getElementById('editIntModal').classList.remove('hidden');
        }
    </script>
</body>
</html>