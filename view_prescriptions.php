<?php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
$user_id = $_SESSION['user_id'];

if (isset($_POST['delete_presc'])) {
    $pid = $_POST['p_id'];
    $path = $_POST['p_path'];
    
    try {
        $checkStmt = $pdo->prepare("SELECT * FROM prescriptions WHERE prescription_id = ? AND user_id = ?");
        $checkStmt->execute([$pid, $user_id]);

        if ($checkStmt->rowCount() > 0) {
            
            $unlinkStmt = $pdo->prepare("UPDATE orders SET prescription_id = NULL WHERE prescription_id = ?");
            $unlinkStmt->execute([$pid]);

            $stmt = $pdo->prepare("DELETE FROM prescriptions WHERE prescription_id = ?");
            if ($stmt->execute([$pid])) {
                if (file_exists($path)) {
                    unlink($path);
                }
                echo "<script>alert('Prescription deleted successfully!'); window.location='view_prescriptions.php';</script>";
            }
        } else {
            echo "<script>alert('Access Denied!');</script>";
        }
    } catch (Exception $e) {
        echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
    }
}

$sql = "SELECT p.*, o.order_id, o.order_status, o.total_amount, o.order_date 
        FROM prescriptions p 
        LEFT JOIN orders o ON p.order_id = o.order_id 
        WHERE p.user_id = ? 
        ORDER BY p.uploaded_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$prescriptions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescription Gallery | MediVault</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
            <a href="dashboard.php" class="flex items-center gap-2 group">
                <div class="bg-arogga text-white p-2 rounded-lg group-hover:rotate-12 transition-transform duration-300">
                    <i class="fas fa-heartbeat text-xl"></i>
                </div>
                <span class="text-2xl font-bold text-slate-800 tracking-tight">Medi<span class="text-arogga">Vault</span></span>
            </a>
            
            <a href="dashboard.php" class="flex items-center gap-2 text-slate-500 hover:text-arogga font-bold text-sm transition-colors">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-10 flex-grow">
        
        <div class="flex justify-between items-end mb-8 border-b border-gray-200 pb-4">
            <div>
                <h1 class="text-3xl font-black text-slate-800 uppercase tracking-tight">My Prescriptions</h1>
                <p class="text-slate-400 text-sm mt-1">Manage your uploaded prescriptions and orders</p>
            </div>
            <a href="upload_prescription.php" class="hidden md:inline-flex items-center gap-2 bg-slate-900 text-white px-6 py-3 rounded-xl font-bold text-xs uppercase tracking-wider hover:bg-arogga transition-all shadow-lg">
                <i class="fas fa-cloud-upload-alt"></i> Upload New
            </a>
        </div>

        <?php if (count($prescriptions) > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php foreach ($prescriptions as $p): ?>
                    <div class="bg-white rounded-3xl p-4 shadow-sm border border-transparent hover:shadow-xl hover:border-teal-100 transition-all duration-300 group flex flex-col h-full">
                        
                        <div class="h-48 bg-gray-50 rounded-2xl overflow-hidden relative mb-4 flex items-center justify-center border border-gray-100">
                            <?php 
                                $ext = pathinfo($p['image_path'], PATHINFO_EXTENSION);
                                if(in_array(strtolower($ext), ['jpg', 'jpeg', 'png'])): 
                            ?>
                                <img src="<?= htmlspecialchars($p['image_path']) ?>" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                            <?php else: ?>
                                <div class="text-center">
                                    <i class="fas fa-file-pdf text-5xl text-red-500 mb-2"></i>
                                    <p class="text-[10px] font-bold text-gray-400">PDF File</p>
                                </div>
                            <?php endif; ?>
                            
                            <a href="<?= htmlspecialchars($p['image_path']) ?>" target="_blank" class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity duration-300 backdrop-blur-[2px]">
                                <span class="bg-white text-slate-900 px-4 py-2 rounded-full text-xs font-black uppercase tracking-wider transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300">
                                    <i class="fas fa-eye mr-2"></i> View Full
                                </span>
                            </a>
                        </div>

                        <div class="flex-grow space-y-3">
                            
                            <div>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Doctor / Ref</p>
                                <h3 class="text-sm font-bold text-slate-800 line-clamp-1">
                                    <i class="fas fa-user-md text-arogga mr-1"></i>
                                    <?= !empty($p['doctor_name']) ? htmlspecialchars($p['doctor_name']) : 'Unknown Doctor' ?>
                                </h3>
                                <?php if(!empty($p['doctor_note'])): ?>
                                    <p class="text-[10px] text-gray-500 mt-1 line-clamp-1 italic">"<?= htmlspecialchars($p['doctor_note']) ?>"</p>
                                <?php endif; ?>
                            </div>

                            <hr class="border-dashed border-gray-200">

                            <div>
                                <?php if ($p['order_id']): ?>
                                    <div class="flex justify-between items-center bg-gray-50 p-2 rounded-lg border border-gray-100">
                                        <div>
                                            <p class="text-[9px] font-black text-gray-400 uppercase">Order #<?= $p['order_id'] ?></p>
                                            <p class="text-xs font-bold text-slate-700"><?= $p['total_amount'] ?> ৳</p>
                                        </div>
                                        <span class="px-2 py-1 rounded text-[9px] font-black uppercase tracking-wide 
                                            <?= $p['order_status'] == 'Pending' ? 'bg-orange-100 text-orange-600' : 
                                               ($p['order_status'] == 'Confirmed' ? 'bg-blue-100 text-blue-600' : 
                                               ($p['order_status'] == 'Delivered' ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600')) ?>">
                                            <?= $p['order_status'] ?>
                                        </span>
                                    </div>
                                <?php else: ?>
                                    <div class="bg-gray-50 p-2 rounded-lg border border-gray-100 text-center">
                                        <p class="text-[10px] font-bold text-gray-400 uppercase">Not linked to order</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 flex justify-between items-center border-t border-gray-100">
                            <span class="text-[10px] font-bold text-gray-400">
                                <i class="far fa-calendar-alt mr-1"></i> <?= date('d M, Y', strtotime($p['uploaded_at'])) ?>
                            </span>
                            
                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this prescription?');">
                                <input type="hidden" name="p_id" value="<?= $p['prescription_id'] ?>">
                                <input type="hidden" name="p_path" value="<?= $p['image_path'] ?>">
                                <button type="submit" name="delete_presc" class="text-gray-300 hover:text-red-500 transition-colors p-2" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>

                    </div>
                    <?php endforeach; ?>
            </div>

        <?php else: ?>
            <div class="flex flex-col items-center justify-center py-20 bg-white rounded-[40px] shadow-sm border border-dashed border-gray-300">
                <div class="bg-gray-50 p-6 rounded-full mb-4">
                    <i class="fas fa-file-medical text-4xl text-gray-300"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-700">No Prescriptions Yet</h3>
                <p class="text-slate-400 text-sm mt-2 mb-6">Upload a prescription to place orders easily.</p>
                <a href="dashboard.php" class="bg-arogga text-white px-8 py-3 rounded-full font-bold text-xs uppercase shadow-lg hover:bg-teal-700 transition-all">
                    Go to Shop
                </a>
            </div>
        <?php endif; ?>
    </div>

    <footer class="bg-white border-t py-6 mt-auto">
        <div class="container mx-auto px-4 text-center">
            <p class="text-xs font-bold text-slate-400">&copy; <?= date('Y') ?> MediVault. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>