<?php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
$user_id = $_SESSION['user_id'];

if (isset($_POST['cancel_order'])) {
    $oid = $_POST['order_id'];

    try {
        $checkStmt = $pdo->prepare("SELECT order_status FROM orders WHERE order_id = ? AND user_id = ?");
        $checkStmt->execute([$oid, $user_id]);
        $order = $checkStmt->fetch();

        if ($order && $order['order_status'] == 'Pending') {
            $pdo->beginTransaction();
            $pdo->prepare("UPDATE prescriptions SET order_id = NULL WHERE order_id = ?")->execute([$oid]);
            $pdo->prepare("DELETE FROM order_items WHERE order_id = ?")->execute([$oid]);
            $pdo->prepare("DELETE FROM orders WHERE order_id = ?")->execute([$oid]);
            $pdo->commit();
            echo "<script>alert('The order has been successfully canceled!'); window.location='order_history.php';</script>";
        } else {
            echo "<script>alert('This order cannot be canceled (it may be under processing).');</script>";
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
    }
}

$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History | MediVault</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; } </style>
</head>
<body class="flex flex-col min-h-screen">

    <nav class="bg-white shadow-md sticky top-0 z-50 w-full">
        <div class="container mx-auto px-4 h-20 flex justify-between items-center">
            <h2 class="text-2xl font-black text-slate-800 tracking-tight">Medi<span class="text-teal-600">Vault</span></h2>
            <a href="dashboard.php" class="flex items-center gap-2 text-slate-500 hover:text-teal-600 font-bold text-sm transition-colors">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-10 flex-grow">
        <h1 class="text-3xl font-black text-slate-800 uppercase tracking-tight mb-8">My Orders</h1>

        <?php if (count($orders) > 0): ?>
            <div class="space-y-6">
                <?php foreach ($orders as $order): ?>
                    <div class="bg-white p-8 rounded-3xl shadow-sm border border-transparent hover:shadow-xl transition-all border-l-8 
                        <?= $order['order_status'] == 'Pending' ? 'border-l-orange-400' : 
                           ($order['order_status'] == 'Confirmed' ? 'border-l-blue-500' : 
                           ($order['order_status'] == 'Delivered' ? 'border-l-green-500' : 'border-l-red-500')) ?>">
                        
                        <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                            
                            <div class="w-full md:w-1/3 text-center md:text-left">
                                <div class="flex items-center gap-3 justify-center md:justify-start mb-1">
                                    <h3 class="text-xl font-black text-slate-800">Order #<?= $order['order_id'] ?></h3>
                                    
                                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider 
                                        <?= $order['order_status'] == 'Pending' ? 'bg-orange-100 text-orange-600' : 
                                           ($order['order_status'] == 'Confirmed' ? 'bg-blue-100 text-blue-600' : 
                                           ($order['order_status'] == 'Delivered' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600')) ?>">
                                        <?= $order['order_status'] ?>
                                    </span>
                                </div>
                                <p class="text-xs font-bold text-gray-400">
                                    <i class="far fa-calendar-alt mr-1"></i> <?= date('d M Y, h:i A', strtotime($order['order_date'])) ?>
                                </p>
                            </div>

                            <div class="w-full md:w-1/3 text-center border-t md:border-t-0 md:border-x border-dashed border-gray-100 py-4 md:py-0">
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Amount</p>
                                <p class="text-3xl font-black text-teal-600 tracking-tight"><?= number_format($order['total_amount'], 2) ?> ৳</p>
                            </div>

                            <div class="w-full md:w-1/3 flex flex-col items-center md:items-end gap-3 justify-center">
                                
                                <div class="flex items-center gap-4">
                                    <a href="invoice.php?id=<?= $order['order_id'] ?>" class="text-blue-500 hover:text-blue-700 font-bold text-xs uppercase underline underline-offset-4 decoration-2 transition-all">
                                        <i class="fas fa-file-invoice mr-1"></i> Invoice
                                    </a>

                                    <?php if ($order['order_status'] == 'Pending'): ?>
                                        <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this order?');">
                                            <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                            <button type="submit" name="cancel_order" class="bg-red-50 text-red-500 px-6 py-3 rounded-xl font-black text-xs uppercase hover:bg-red-500 hover:text-white transition-all shadow-sm flex items-center gap-2">
                                                <i class="fas fa-times"></i> Cancel Order
                                            </button>
                                        </form>
                                    <?php elseif ($order['order_status'] == 'Cancelled'): ?>
                                        <span class="text-sm font-bold text-gray-300 italic">Cancelled</span>
                                    <?php else: ?>
                                        <span class="text-sm font-bold text-gray-300 italic">Processing / Cannot Cancel</span>
                                    <?php endif; ?>
                                </div>

                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-20 bg-white rounded-[40px] border border-dashed border-gray-300">
                <i class="fas fa-shopping-basket text-6xl text-gray-200 mb-4"></i>
                <h3 class="text-xl font-bold text-slate-700">No Orders Found</h3>
                <a href="dashboard.php" class="inline-block mt-4 text-teal-600 font-bold hover:underline">Start Shopping</a>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>