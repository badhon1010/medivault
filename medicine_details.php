<?php
include 'config.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }
$med_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT m.*, 
    (SELECT AVG(rating) FROM medicine_reviews WHERE medicine_id = m.medicine_id) as avg_rating, 
    (SELECT COUNT(*) FROM medicine_reviews WHERE medicine_id = m.medicine_id) as total_reviews 
    FROM medicines m WHERE m.medicine_id = ?");
$stmt->execute([$med_id]);
$med = $stmt->fetch();
if (!$med) { die("Medicine not found!"); }

$description = !empty($med['description']) ? $med['description'] : "<strong>{$med['medicine_name']}</strong> contains <strong>{$med['generic_name']}</strong>. It is widely used to treat various conditions as prescribed by healthcare professionals. Please consult your doctor for dosage.";
$sideEffects = !empty($med['side_effects']) ? $med['side_effects'] : "Common side effects may include nausea, dizziness, or headache. If these persist, consult your doctor immediately.";

$stmtB = $pdo->prepare("SELECT * FROM inventory_batches WHERE medicine_id = ? AND quantity_instock > 0 AND expiry_date >= CURDATE() ORDER BY expiry_date ASC");
$stmtB->execute([$med_id]);
$batches = $stmtB->fetchAll();

$stmtR = $pdo->prepare("SELECT r.*, u.full_name FROM medicine_reviews r JOIN users u ON r.user_id = u.user_id WHERE r.medicine_id = ? ORDER BY r.created_at DESC");
$stmtR->execute([$med_id]);
$reviews = $stmtR->fetchAll();

$checkP = $pdo->prepare("SELECT COUNT(*) FROM prescriptions WHERE user_id = ?");
$checkP->execute([$user_id]);
$hasPrescription = ($checkP->fetchColumn() > 0) ? 'true' : 'false';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($med['medicine_name']) ?> | Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style> 
        body{font-family:'Inter',sans-serif; background-color: #f0fdfa;}
        .text-arogga{color:#0d9488} .bg-arogga{background-color:#0d9488} 
        .tab-active{border-bottom:3px solid #0d9488;color:#0d9488; background-color: rgba(13, 148, 136, 0.05);}
        .glass-card { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.2); }
    </style>
</head>
<body class="flex flex-col min-h-screen text-slate-800">
    
    <nav class="bg-white/80 backdrop-blur-md shadow-sm sticky top-0 z-50 border-b border-teal-50">
        <div class="container mx-auto px-4 h-20 flex items-center justify-between">
            <a href="dashboard.php" class="text-2xl font-black text-slate-800 tracking-tight">Medi<span class="text-arogga">Vault</span></a>
            <a href="dashboard.php" class="flex items-center gap-2 px-4 py-2 text-sm font-bold text-slate-500 hover:text-arogga hover:bg-teal-50 rounded-full transition-all">
                <i class="fas fa-arrow-left"></i> Back to Shop
            </a>
        </div>
    </nav>

    <div class="container mx-auto mt-8 px-4 pb-20 flex-grow max-w-6xl">
        
        <nav class="flex mb-8 text-sm font-medium text-gray-400">
            <a href="dashboard.php" class="hover:text-arogga">Home</a> 
            <span class="mx-2">/</span> 
            <span>Details</span> 
            <span class="mx-2">/</span> 
            <span class="text-arogga font-bold"><?= htmlspecialchars($med['medicine_name']) ?></span>
        </nav>

        <div class="glass-card rounded-[40px] shadow-xl overflow-hidden flex flex-col lg:flex-row mb-12">
            
            <div class="lg:w-2/5 bg-gradient-to-br from-gray-50 to-teal-50 p-12 flex items-center justify-center relative group">
                <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>
                <?php 
                    $img = !empty($med['medicine_image']) ? "assets/img/".trim($med['medicine_image']) : "assets/img/napa.jpg";
                    if(!file_exists($img)) $img = "assets/img/napa.jpg";
                ?>
                <img src="<?= $img ?>" class="max-h-96 w-auto object-contain drop-shadow-2xl transition-transform duration-700 group-hover:scale-110 group-hover:-rotate-3 z-10">
                
                <?php if($med['requires_prescription']): ?>
                    <div class="absolute top-6 left-6 z-20">
                        <span class="bg-white/90 backdrop-blur text-purple-600 px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider shadow-sm border border-purple-100 flex items-center gap-2">
                            <i class="fas fa-file-prescription"></i> Prescription Required
                        </span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="lg:w-3/5 p-8 lg:p-12 bg-white">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <h1 class="text-4xl font-black text-slate-900 mb-2 tracking-tight"><?= htmlspecialchars($med['medicine_name']) ?></h1>
                        <p class="text-sm font-bold text-slate-400 uppercase tracking-wider bg-slate-100 px-3 py-1 rounded-lg inline-block"><?= htmlspecialchars($med['generic_name']) ?></p>
                    </div>
                    <div class="flex flex-col items-end">
                        <div class="flex text-yellow-400 text-sm mb-1">
                            <?php for($i=1;$i<=5;$i++) echo $i<=round($med['avg_rating']) ? '<i class="fas fa-star"></i>' : '<i class="fas fa-star text-gray-200"></i>'; ?>
                        </div>
                        <span class="text-xs font-bold text-slate-400"><?= $med['total_reviews'] ?> Reviews</span>
                    </div>
                </div>

                <div class="mt-8 mb-8">
                    <div class="flex items-baseline gap-2">
                        <span class="text-5xl font-black text-arogga"><?= $med['unit_price'] ?> ৳</span>
                        <span class="text-sm font-bold text-gray-400">/ per unit</span>
                    </div>
                </div>

                <div class="bg-slate-50 p-6 rounded-3xl border border-slate-100 mb-8">
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Select Batch & Expiry</label>
                    <div class="relative">
                        <select id="detail-batch-select" class="w-full p-4 pl-5 pr-10 bg-white border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent text-sm font-bold appearance-none cursor-pointer shadow-sm">
                            <option value="" selected disabled>Choose available batch...</option>
                            <?php foreach($batches as $b): ?>
                                <option value="<?= $b['batch_id'] ?>" data-stock="<?= $b['quantity_instock'] ?>" data-batch-num="<?= $b['batch_number'] ?>">
                                    Batch: <?= $b['batch_number'] ?> — Exp: <?= date('M Y', strtotime($b['expiry_date'])) ?> (Stock: <?= $b['quantity_instock'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <i class="fas fa-chevron-down absolute right-5 top-5 text-gray-400 pointer-events-none"></i>
                    </div>
                </div>

                <div class="flex gap-4">
                    <button onclick="addToCartFromDetail(<?= $med['medicine_id'] ?>, '<?= addslashes($med['medicine_name']) ?>', <?= $med['unit_price'] ?>, <?= $med['requires_prescription'] ?>)" 
                        class="flex-1 bg-slate-900 text-white py-5 rounded-2xl font-black uppercase tracking-wider hover:bg-arogga transition-all shadow-xl shadow-teal-100 active:scale-95 flex justify-center items-center gap-3">
                        <i class="fas fa-shopping-bag"></i> Add to Cart
                    </button>
                    <button onclick="document.getElementById('review-section').scrollIntoView({behavior: 'smooth'})" 
                        class="px-6 py-5 bg-teal-50 text-arogga rounded-2xl font-bold hover:bg-teal-100 transition-colors">
                        <i class="fas fa-comment-alt"></i>
                    </button>
                </div>
                
                <div class="grid grid-cols-3 gap-4 mt-8 pt-8 border-t border-slate-100">
                    <div class="text-center">
                        <i class="fas fa-check-circle text-teal-500 text-xl mb-2"></i>
                        <p class="text-[10px] font-bold text-slate-500 uppercase">100% Authentic</p>
                    </div>
                    <div class="text-center">
                        <i class="fas fa-shipping-fast text-teal-500 text-xl mb-2"></i>
                        <p class="text-[10px] font-bold text-slate-500 uppercase">24h Delivery</p>
                    </div>
                    <div class="text-center">
                        <i class="fas fa-headset text-teal-500 text-xl mb-2"></i>
                        <p class="text-[10px] font-bold text-slate-500 uppercase">Expert Support</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-2 space-y-8">
                <div class="bg-white rounded-[30px] shadow-sm border border-gray-100 p-8">
                    <div class="flex border-b border-gray-100 mb-6">
                        <button onclick="switchTab('desc')" id="btn-desc" class="pb-4 px-6 text-sm font-bold text-gray-400 hover:text-arogga transition-all tab-active">Description</button>
                        <button onclick="switchTab('side')" id="btn-side" class="pb-4 px-6 text-sm font-bold text-gray-400 hover:text-arogga transition-all">Side Effects</button>
                    </div>

                    <div id="content-desc" class="text-slate-600 leading-relaxed space-y-4 animate-fade-in text-sm">
                        <?= $description ?>
                    </div>

                    <div id="content-side" class="hidden text-slate-600 leading-relaxed space-y-4 animate-fade-in text-sm">
                        <?= $sideEffects ?>
                        <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-r-xl mt-4">
                            <p class="text-xs text-red-700 font-bold flex items-center gap-2">
                                <i class="fas fa-exclamation-triangle"></i> Warning: Do not take this medicine without a valid prescription.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div id="review-section" class="bg-white rounded-[30px] shadow-sm border border-gray-100 p-8">
                    <h3 class="text-xl font-black text-slate-800 mb-6 flex items-center gap-2">
                        <i class="fas fa-pen-nib text-arogga"></i> Write a Review
                    </h3>
                    <form id="reviewForm" class="space-y-4">
                        <input type="hidden" id="medId" value="<?= $med_id ?>">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-2 ml-1">Rating</label>
                            <div class="flex gap-4">
                                <?php for($i=1; $i<=5; $i++): ?>
                                <label class="cursor-pointer">
                                    <input type="radio" name="rating" value="<?= $i ?>" class="hidden peer">
                                    <i class="fas fa-star text-2xl text-gray-200 peer-checked:text-yellow-400 hover:text-yellow-300 transition-colors"></i>
                                </label>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-2 ml-1">Your Experience</label>
                            <textarea id="comment" rows="3" class="w-full p-4 bg-slate-50 rounded-2xl border-none outline-none focus:ring-2 focus:ring-teal-500 text-sm font-medium" placeholder="How was the product?"></textarea>
                        </div>
                        <button type="button" onclick="submitReview()" class="bg-slate-900 text-white px-8 py-3 rounded-xl font-bold text-sm hover:bg-black transition-colors">
                            Submit Review
                        </button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="bg-white rounded-[30px] shadow-sm border border-gray-100 p-6 sticky top-24">
                    <h3 class="text-lg font-black text-slate-800 mb-6">Recent Reviews</h3>
                    
                    <div class="space-y-4 max-h-[600px] overflow-y-auto pr-2 custom-scrollbar">
                        <?php if(count($reviews) > 0): ?>
                            <?php foreach($reviews as $r): ?>
                            <div class="pb-4 border-b border-gray-50 last:border-0">
                                <div class="flex justify-between items-start mb-1">
                                    <h5 class="font-bold text-slate-800 text-sm"><?= htmlspecialchars($r['full_name']) ?></h5>
                                    <span class="text-[10px] text-gray-400 font-bold"><?= date('d M', strtotime($r['created_at'])) ?></span>
                                </div>
                                <div class="text-yellow-400 text-[10px] mb-2">
                                    <?php for($i=1; $i<=5; $i++) echo $i <= $r['rating'] ? '<i class="fas fa-star"></i>' : '<i class="fas fa-star text-gray-200"></i>'; ?>
                                </div>
                                <p class="text-xs text-slate-500 leading-relaxed">"<?= htmlspecialchars($r['comment']) ?>"</p>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-8">
                                <i class="far fa-comment-dots text-4xl text-gray-200 mb-2"></i>
                                <p class="text-xs text-gray-400">No reviews yet. Be the first!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        let userHasUploadedPrescription = <?= $hasPrescription ?>;

        // Tab Switcher
        function switchTab(tab) {
            document.getElementById('content-desc').classList.add('hidden');
            document.getElementById('content-side').classList.add('hidden');
            document.getElementById('btn-desc').classList.remove('tab-active');
            document.getElementById('btn-desc').classList.add('text-gray-400');
            document.getElementById('btn-side').classList.remove('tab-active');
            document.getElementById('btn-side').classList.add('text-gray-400');

            document.getElementById('content-' + tab).classList.remove('hidden');
            const activeBtn = document.getElementById('btn-' + tab);
            activeBtn.classList.add('tab-active');
            activeBtn.classList.remove('text-gray-400');
        }

        // Add to Cart Logic
        function addToCartFromDetail(medId, name, price, reqPresc) {
            const batchSelect = document.getElementById('detail-batch-select');
            if (!batchSelect || batchSelect.value === "") { alert("Please select a batch first!"); return; }
            const selectedOption = batchSelect.options[batchSelect.selectedIndex];
            
            if (reqPresc == 1 && !userHasUploadedPrescription) {
                 if(confirm("Prescription required. Upload now?")) { window.location.href = "upload_prescription.php"; }
                return;
            }

            const item = {
                id: medId,
                batch_id: selectedOption.value,
                batch_number: selectedOption.getAttribute('data-batch-num'),
                name: name,
                price: parseFloat(price),
                qty: 1,
                maxStock: parseInt(selectedOption.getAttribute('data-stock'))
            };

            let tempCart = JSON.parse(localStorage.getItem('tempCart')) || [];
            tempCart.push(item);
            localStorage.setItem('tempCart', JSON.stringify(tempCart));
            
            window.location.href = "dashboard.php";
        }

        // Submit Review AJAX
        function submitReview() {
            const medId = document.getElementById('medId').value;
            const comment = document.getElementById('comment').value;
            const ratingInput = document.querySelector('input[name="rating"]:checked');
            
            if(!ratingInput || !comment) {
                alert("Please select a rating and write a comment.");
                return;
            }

            fetch('submit_review_ajax.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    medicine_id: medId,
                    rating: ratingInput.value,
                    comment: comment
                })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    alert("Thanks for your review!");
                    location.reload();
                } else {
                    alert(data.message);
                }
            });
        }
    </script>
</body>
</html>