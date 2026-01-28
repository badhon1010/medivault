<?php 
include 'config.php'; 

if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }
$user_id = $_SESSION['user_id'];

$checkP = $pdo->prepare("SELECT COUNT(*) FROM prescriptions WHERE user_id = ?");
$checkP->execute([$user_id]);
$hasPrescription = ($checkP->fetchColumn() > 0) ? 1 : 0;

$query = "SELECT m.*, SUM(b.quantity_instock) as total_available, 
          (SELECT AVG(rating) FROM medicine_reviews WHERE medicine_id = m.medicine_id) as avg_rating,
          (SELECT COUNT(*) FROM medicine_reviews WHERE medicine_id = m.medicine_id) as total_reviews
          FROM medicines m 
          LEFT JOIN inventory_batches b ON m.medicine_id = b.medicine_id 
          GROUP BY m.medicine_id";
$medicines = $pdo->query($query)->fetchAll();

$interactions = $pdo->query("SELECT * FROM drug_interactions")->fetchAll(PDO::FETCH_ASSOC);
$batches = $pdo->query("SELECT * FROM inventory_batches WHERE quantity_instock > 0 AND expiry_date >= CURDATE() ORDER BY expiry_date ASC")->fetchAll();
$batch_map = [];
foreach($batches as $b) { $batch_map[$b['medicine_id']][] = $b; }

$stmtHistory = $pdo->prepare("SELECT DISTINCT medicine_id FROM order_items oi JOIN orders o ON oi.order_id = o.order_id WHERE o.user_id = ? AND o.order_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$stmtHistory->execute([$user_id]);
$purchaseHistory = $stmtHistory->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediVault | Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; overflow-x: hidden; }
        .bg-arogga { background-color: #0d9488; } 
        .text-arogga { color: #0d9488; }
        .border-arogga { border-color: #0d9488; }
        .hover-arogga:hover { background-color: #0f766e; }
        
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .med-card { animation: fadeInUp 0.5s ease backwards; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .med-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px -5px rgba(0,0,0,0.15); }

        #cartDrawer { transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1); z-index: 100; }
        .drawer-open { transform: translateX(0) !important; }
        .drawer-overlay { transition: opacity 0.3s ease; }

        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: #0d9488; border-radius: 10px; }
    </style>
    <script>
        const allInteractions = <?= json_encode($interactions) ?>;
        const userPurchaseHistory = <?= json_encode($purchaseHistory) ?>;
    </script>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen w-full">

    <nav class="bg-white shadow-md sticky top-0 z-50 w-full">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-20">
                <a href="dashboard.php" class="flex items-center gap-2 group">
                    <div class="bg-arogga text-white p-2 rounded-lg group-hover:rotate-12 transition-transform duration-300">
                        <i class="fas fa-heartbeat text-xl"></i>
                    </div>
                    <span class="text-2xl font-bold text-slate-800 tracking-tight">Medi<span class="text-arogga">Vault</span></span>
                </a>

                <div class="hidden md:flex flex-1 max-w-xl mx-8 relative group">
                    <input type="text" id="searchInput" placeholder="Search medicines..." 
                           class="w-full pl-5 pr-12 py-3 bg-gray-100 border border-transparent rounded-full focus:ring-2 focus:ring-teal-500 focus:bg-white focus:border-teal-500 transition-all duration-300 text-sm font-medium outline-none shadow-sm group-hover:shadow-md">
                    <button class="absolute right-2 top-1.5 bg-arogga text-white w-9 h-9 rounded-full flex items-center justify-center hover:bg-teal-700 hover:scale-110 transition-all duration-300 shadow-sm">
                        <i class="fas fa-search text-xs"></i>
                    </button>
                </div>

                <div class="flex items-center gap-6">
                    <div class="hidden lg:flex items-center gap-6 font-semibold text-gray-500 text-sm">
                        <a href="upload_prescription.php" class="hover:text-arogga transition-colors flex items-center gap-1 hover:scale-105 transform duration-200">
                            <i class="fas fa-file-upload"></i> Upload
                        </a>
                        <a href="view_prescriptions.php" class="hover:text-arogga transition-colors flex items-center gap-1 hover:scale-105 transform duration-200">
                            <i class="fas fa-images"></i> Gallery
                        </a>
                        <a href="order_history.php" class="hover:text-arogga transition-colors flex items-center gap-1 hover:scale-105 transform duration-200">
                            <i class="fas fa-history"></i> Orders
                        </a>
                        <a href="profile.php" class="hover:text-arogga transition-colors flex items-center gap-1 hover:scale-105 transform duration-200">
                            <i class="fas fa-user"></i> Profile
                        </a>
                    </div>
                    
                    <button onclick="toggleCart(true)" class="relative bg-teal-50 text-arogga p-3 rounded-xl hover:bg-arogga hover:text-white transition-all active:scale-90">
                        <i class="fas fa-shopping-bag text-xl"></i>
                        <span id="cart-badge" class="absolute -top-1 -right-1 bg-red-500 text-white text-[9px] font-black w-5 h-5 flex items-center justify-center rounded-full shadow-lg border-2 border-white" style="display:none;">0</span>
                    </button>

                    <div class="flex items-center gap-3 border-l pl-6 border-gray-200">
                        <div class="text-right hidden sm:block">
                            <p class="text-[10px] text-gray-400 font-bold uppercase">Hello,</p>
                            <p class="text-xs font-bold text-slate-700"><?= htmlspecialchars($_SESSION['user_name']) ?></p>
                        </div>
                        <a href="logout.php" class="text-red-400 hover:text-white hover:bg-red-500 bg-red-50 p-2 rounded-full transition-all duration-300" title="Logout">
                            <i class="fas fa-power-off"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div id="drawerOverlay" onclick="toggleCart(false)" class="fixed inset-0 bg-black/40 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity z-[90]"></div>
    <div id="cartDrawer" class="fixed top-0 right-0 h-full w-full max-w-sm bg-white shadow-2xl translate-x-full z-[100] flex flex-col">
        <div class="p-6 border-b flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-black text-slate-800 uppercase tracking-tight">Medicine Cart</h3>
            <button onclick="toggleCart(false)" class="text-gray-400 hover:text-red-500 transition-colors"><i class="fas fa-times text-xl"></i></button>
        </div>
        
        <div id="cart-items" class="flex-grow overflow-y-auto p-6 space-y-4">
            <div class="text-center py-20 opacity-50">
                <i class="fas fa-shopping-cart text-6xl text-gray-200 mb-4"></i>
                <p class="text-gray-400 font-bold">Your bag is empty</p>
            </div>
        </div>

        <div class="p-6 bg-gray-50 border-t border-gray-100">
            <div class="flex justify-between items-center mb-4">
                <span class="text-gray-500 font-bold uppercase text-[10px]">Total Amount</span>
                <span id="total-price" class="text-2xl font-black text-slate-800">0 ৳</span>
            </div>
            
            <div id="interaction-alert" class="hidden bg-red-50 p-3 rounded-xl mb-3 border border-red-100">
                <p id="alert-msg" class="text-[10px] text-red-600 font-bold leading-tight"></p>
            </div>

            <div class="flex gap-2 mb-4">
                <input type="text" id="couponCode" placeholder="Code" class="flex-1 px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs outline-none focus:border-teal-400">
                <button onclick="applyCoupon()" class="bg-slate-800 text-white px-3 rounded-lg text-xs font-bold hover:bg-black transition-colors">Apply</button>
            </div>

            <button onclick="placeOrder()" class="w-full bg-arogga text-white py-4 rounded-2xl font-black uppercase tracking-widest shadow-xl shadow-teal-100 hover:bg-teal-700 active:scale-95 transition-all">
                Checkout Now <i class="fas fa-arrow-right ml-2"></i>
            </button>
        </div>
    </div>

    <div class="container mx-auto mt-8 px-4 pb-20 flex-grow">
        
        <div class="bg-gradient-to-r from-teal-500 to-emerald-600 rounded-3xl p-8 mb-8 text-white shadow-lg relative overflow-hidden">
            <div class="absolute right-0 top-0 opacity-10 transform translate-x-10 -translate-y-10">
                <i class="fas fa-capsules text-9xl"></i>
            </div>
            <h2 class="text-3xl font-bold mb-2">Your Health, Our Priority</h2>
            <p class="text-teal-100 text-sm mb-6 max-w-lg">Upload your prescription and get 100% authentic medicines delivered to your doorstep.</p>
            <a href="upload_prescription.php" class="bg-white text-arogga px-6 py-2.5 rounded-full font-bold text-sm shadow-md hover:bg-gray-50 transition-colors inline-flex items-center gap-2">
                <i class="fas fa-camera"></i> Upload Prescription
            </a>
        </div>

        <div class="flex justify-between items-end mb-6">
            <div>
                <h3 class="text-xl font-bold text-slate-800">Popular Medicines</h3>
                <p class="text-xs text-gray-400 mt-1">100% Genuine Products</p>
            </div>
        </div>

        <div id="medicineGrid" class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
            <?php foreach($medicines as $index => $med): ?>
            <div class="med-card bg-white rounded-2xl p-4 border border-transparent shadow-sm relative group" style="animation-delay: <?= $index * 0.05 ?>s">
                
                <?php if($med['total_available'] > 0): ?>
                    <span class="absolute top-3 left-3 bg-emerald-50 text-emerald-600 text-[10px] font-bold px-2 py-1 rounded-md z-10">IN STOCK</span>
                <?php else: ?>
                    <span class="absolute top-3 left-3 bg-red-50 text-red-500 text-[10px] font-bold px-2 py-1 rounded-md z-10">OUT OF STOCK</span>
                <?php endif; ?>

                <a href="medicine_details.php?id=<?= $med['medicine_id'] ?>" class="block">
                    <div class="h-32 flex items-center justify-center mb-3 transition-transform duration-500 group-hover:scale-110">
                        <?php 
                        $img_file = !empty($med['medicine_image']) ? trim($med['medicine_image']) : 'default.png';
                        $img_path = "assets/img/" . $img_file;
                        if (!file_exists($img_path)) $img_path = "assets/img/napa.jpg";
                        ?>
                        <img src="<?= $img_path ?>" class="h-full object-contain" alt="Medicine">
                    </div>

                    <h4 class="text-sm font-bold text-slate-800 line-clamp-1 group-hover:text-arogga transition-colors" title="<?= htmlspecialchars($med['medicine_name']) ?>">
                        <?= htmlspecialchars($med['medicine_name']) ?>
                    </h4>
                    <p class="text-[10px] text-gray-400 mb-2 line-clamp-1"><?= htmlspecialchars($med['generic_name']) ?></p>
                </a>

                <select id="batch-select-<?= $med['medicine_id'] ?>" class="w-full mb-3 text-[10px] bg-gray-50 border border-gray-100 rounded-lg p-1.5 outline-none focus:border-teal-400 text-gray-600">
                    <option value="" selected disabled>Select Batch</option>
                    <?php if (isset($batch_map[$med['medicine_id']])) {
                        foreach($batch_map[$med['medicine_id']] as $batch) {
                            $expDate = date('d M Y', strtotime($batch['expiry_date']));
                            echo "<option value='{$batch['batch_id']}' data-stock='{$batch['quantity_instock']}' data-batch-num='{$batch['batch_number']}'> Batch: {$batch['batch_number']} (Exp: $expDate)</option>";
                        }
                    } ?>
                </select>

                <div class="flex items-center justify-between mt-1 mb-2">
                    <div>
                        <span class="text-xs text-gray-400 line-through mr-1"><?= $med['unit_price'] + 5 ?>৳</span>
                        <span class="text-lg font-bold text-arogga"><?= $med['unit_price'] ?>৳</span>
                    </div>
                    
                    <div class="flex items-center text-[10px] text-yellow-400 font-bold bg-yellow-50 px-2 py-1 rounded-lg">
                        <i class="fas fa-star mr-1"></i>
                        <span class="text-slate-500">(<?= round($med['avg_rating'], 1) ?>)</span>
                    </div>
                </div>

                <button onclick="addToCart(<?= $med['medicine_id'] ?>, '<?= addslashes($med['medicine_name']) ?>', <?= $med['unit_price'] ?>, <?= $med['total_available'] ?? 0 ?>, <?= $med['requires_prescription'] ?>)" 
                        class="w-full bg-arogga text-white py-2 rounded-xl text-xs font-bold hover:bg-teal-700 transition-all shadow-md flex items-center justify-center gap-2 active:scale-95">
                    <i class="fas fa-shopping-bag"></i> Add to Cart
                </button>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <footer class="bg-slate-900 text-slate-300 pt-16 pb-8 mt-auto w-full">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
                <div>
                    <h4 class="text-white text-lg font-bold mb-4 flex items-center gap-2">
                        <i class="fas fa-heartbeat text-arogga"></i> MediVault
                    </h4>
                    <p class="text-xs leading-relaxed text-slate-400">
                        MediVault is your trusted online pharmacy. We provide 100% authentic medicines, fast delivery, and expert support. Your health is our priority.
                    </p>
                </div>
                <div>
                    <h4 class="text-white text-sm font-bold mb-4 uppercase tracking-wider">Quick Links</h4>
                    <ul class="space-y-2 text-xs">
                        <li><a href="dashboard.php" class="hover:text-arogga transition-colors">Home</a></li>
                        <li><a href="order_history.php" class="hover:text-arogga transition-colors">Orders</a></li>
                        <li><a href="aboutus.php" class="hover:text-arogga transition-colors">About Us</a></li>
                        <li><a href="terms.php" class="hover:text-arogga transition-colors">Terms & Conditions</a></li>

                    </ul>
                </div>
                <div>
                    <h4 class="text-white text-sm font-bold mb-4 uppercase tracking-wider">Contact</h4>
                    <ul class="space-y-3 text-xs">
                        <li><i class="fas fa-phone-alt text-arogga mr-2"></i> +880 1234 567890</li>
                        <li><i class="fas fa-envelope text-arogga mr-2"></i> help@medivault.com</li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white text-sm font-bold mb-4 uppercase tracking-wider">Payment</h4>
                    <div class="flex gap-4 text-2xl text-slate-500">
                        <i class="fab fa-cc-visa hover:text-white transition-colors"></i>
                        <i class="fab fa-cc-mastercard hover:text-white transition-colors"></i>
                    </div>
                </div>
            </div>
            <div class="border-t border-slate-800 pt-8 text-center text-xs text-slate-500">
                &copy; <?= date('Y') ?> MediVault. All rights reserved.
            </div>
        </div>
    </footer>

    <div id="reviewModal" class="hidden fixed inset-0 bg-black/60 z-[100] flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white p-6 rounded-3xl shadow-2xl w-full max-w-sm border border-gray-100 transform transition-all scale-100">
            <h3 id="reviewMedName" class="text-lg font-bold text-slate-800 mb-4 text-center">Write a Review</h3>
            <form action="submit_review.php" method="POST" class="space-y-4">
                <input type="hidden" name="medicine_id" id="modalMedId">
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1 ml-1">Rating</label>
                    <div class="relative">
                        <select name="rating" class="w-full p-3 bg-gray-50 border-none rounded-xl text-sm font-bold outline-none focus:ring-2 focus:ring-teal-400 appearance-none">
                            <option value="5">⭐⭐⭐⭐⭐ (Excellent)</option>
                            <option value="4">⭐⭐⭐⭐ (Good)</option>
                            <option value="3">⭐⭐⭐ (Average)</option>
                            <option value="2">⭐⭐ (Poor)</option>
                            <option value="1">⭐ (Very Poor)</option>
                        </select>
                        <i class="fas fa-chevron-down absolute right-3 top-3.5 text-gray-400 text-xs"></i>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1 ml-1">Comment</label>
                    <textarea name="comment" required rows="3" class="w-full p-3 bg-gray-50 border-none rounded-xl text-sm outline-none focus:ring-2 focus:ring-teal-400 resize-none" placeholder="Share your experience..."></textarea>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('reviewModal').classList.add('hidden')" class="flex-1 bg-gray-100 text-gray-500 py-3 rounded-xl font-bold text-xs hover:bg-gray-200 transition-colors">Cancel</button>
                    <button type="submit" class="flex-1 bg-arogga text-white py-3 rounded-xl font-bold text-xs shadow-lg hover:bg-teal-700 transition-colors">Submit</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Load persistence
        window.onload = function() {
            if (localStorage.getItem('tempCart')) {
                let tempItems = JSON.parse(localStorage.getItem('tempCart'));
                tempItems.forEach(item => { proceedToAddToCart(item.id, item.batch_id, item.batch_number, item.name, item.price, item.maxStock); });
                localStorage.removeItem('tempCart');
                window.history.replaceState({}, document.title, "dashboard.php");
            }
            let storedCart = JSON.parse(localStorage.getItem('medCart'));
            if(storedCart) { cart = storedCart; updateCartUI(); }
        };

        let userHasUploadedPrescription = <?= $hasPrescription ?>; 
        let cart = [];
        window.discountAmount = 0; // Global Discount Variable

        function toggleCart(show) {
            const drawer = document.getElementById('cartDrawer');
            const overlay = document.getElementById('drawerOverlay');
            if (show) {
                drawer.classList.add('drawer-open');
                overlay.classList.remove('pointer-events-none', 'opacity-0');
            } else {
                drawer.classList.remove('drawer-open');
                overlay.classList.add('pointer-events-none', 'opacity-0');
            }
        }

        function addToCart(medId, name, price, stock, reqPresc) {
            const batchSelect = document.getElementById('batch-select-' + medId);
            if (!batchSelect || batchSelect.value === "") { alert("Please select a batch first!"); return; }
            
            const selectedOption = batchSelect.options[batchSelect.selectedIndex];
            const batchId = selectedOption.value;
            const batchNum = selectedOption.getAttribute('data-batch-num');
            const batchStock = parseInt(selectedOption.getAttribute('data-stock'));

            if (parseInt(reqPresc) === 1 && !userHasUploadedPrescription) {
                if(confirm("Prescription required for this item. Do you want to upload now?")) {
                    window.location.href = "upload_prescription.php";
                }
                return;
            }
            proceedToAddToCart(medId, batchId, batchNum, name, price, batchStock);
            toggleCart(true); 
        }

        function proceedToAddToCart(id, batchId, batchNum, name, price, stock) {
            let existingItem = cart.find(item => item.batch_id === batchId);
            if (existingItem) {
                if (existingItem.qty + 1 > stock) { alert("Stock limit reached!"); return; }
                existingItem.qty += 1;
            } else {
                cart.push({ id, batch_id: batchId, batch_number: batchNum, name, price: parseFloat(price), qty: 1, maxStock: parseInt(stock) });
            }
            // Reset discount on cart change to prevent calculation errors
            window.discountAmount = 0; 
            updateCartUI();
            saveCart();
            checkInteractions();
        }

        function updateCartUI() {
            const list = document.getElementById('cart-items');
            const badge = document.getElementById('cart-badge');
            const totalDisplay = document.getElementById('total-price');
            let subTotal = 0;
            
            let totalQty = cart.reduce((acc, i) => acc + i.qty, 0);
            badge.innerText = totalQty;
            badge.style.display = cart.length > 0 ? 'flex' : 'none';

            if(cart.length === 0) {
                list.innerHTML = `<div class="text-center py-8">
                    <i class="fas fa-shopping-basket text-4xl text-gray-200 mb-2"></i>
                    <p class="text-xs text-gray-400 font-medium">Your bag is empty</p>
                </div>`;
                totalDisplay.innerText = '0 ৳';
                window.discountAmount = 0;
                return;
            }

            list.innerHTML = cart.map((item, index) => {
                subTotal += (item.price * item.qty);
                return `
                    <div class="bg-white p-3 rounded-xl border border-gray-100 shadow-sm flex justify-between items-center group hover:border-teal-100 transition-colors">
                        <div>
                            <p class="font-bold text-xs text-slate-800 line-clamp-1">${item.name}</p>
                            <p class="text-[9px] text-gray-400">Batch: ${item.batch_number}</p>
                            <p class="text-xs font-bold text-arogga mt-0.5">${item.price} ৳</p>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <button onclick="removeItem(${index})" class="text-gray-300 hover:text-red-500 transition-colors">
                                <i class="fas fa-trash-alt text-xs"></i>
                            </button>
                            <div class="flex items-center bg-gray-50 rounded-lg px-2 py-0.5 border border-gray-100">
                                <button onclick="changeQty(${index}, -1)" class="text-gray-500 hover:text-arogga font-bold text-xs px-1">-</button>
                                <span class="text-xs font-bold text-slate-700 w-4 text-center mx-1">${item.qty}</span>
                                <button onclick="changeQty(${index}, 1)" class="text-gray-500 hover:text-arogga font-bold text-xs px-1">+</button>
                            </div>
                        </div>
                    </div>`;
            }).join('');

            // CALCULATION LOGIC
            let finalAmount = subTotal - window.discountAmount;
            if(finalAmount < 0) finalAmount = 0;

            if (window.discountAmount > 0) {
                // Show discounted price view
                totalDisplay.innerHTML = `<span class="text-xs text-gray-400 line-through mr-2">${subTotal}</span> <span class="text-emerald-600">${finalAmount.toFixed(2)} ৳</span>`;
            } else {
                totalDisplay.innerText = finalAmount.toFixed(2) + ' ৳';
            }
        }

        function saveCart() { localStorage.setItem('medCart', JSON.stringify(cart)); }

        function changeQty(index, delta) {
            const nextQty = cart[index].qty + delta;
            if (nextQty > 0 && nextQty <= cart[index].maxStock) {
                cart[index].qty = nextQty;
                window.discountAmount = 0; // Reset discount on quantity change
                saveCart(); 
                updateCartUI();
                checkInteractions();
            } else if (nextQty > cart[index].maxStock) {
                alert("Cannot add more than stock!");
            }
        }

        function removeItem(index) {
            cart.splice(index, 1);
            window.discountAmount = 0; // Reset discount on remove
            saveCart();
            updateCartUI();
            checkInteractions();
        }

        function applyCoupon() {
            const code = document.getElementById('couponCode').value;
            // Calculate subtotal
            const currentTotal = cart.reduce((acc, item) => acc + (item.price * item.qty), 0);

            if (currentTotal === 0) return alert("Please add items to cart first!");

            fetch('apply_coupon.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ coupon_code: code, total: currentTotal })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    window.discountAmount = parseFloat(data.discount); // Set Discount
                    alert(data.message);
                    updateCartUI(); // Re-render totals
                } else {
                    alert(data.message);
                    window.discountAmount = 0;
                    updateCartUI();
                }
            })
            .catch(err => console.error(err));
        }

        function placeOrder() {
            if (cart.length === 0) return alert("Cart is empty!");
            if (!confirm("Are you sure you want to place this order?")) return;

            const currentTotal = cart.reduce((acc, item) => acc + (item.price * item.qty), 0);
            const finalTotal = currentTotal - window.discountAmount;

            const orderBtn = document.querySelector('button[onclick="placeOrder()"]');
            if(orderBtn) {
                orderBtn.disabled = true; 
                orderBtn.innerText = "Processing...";
                orderBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }

            fetch('place_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    cart: cart, 
                    total: finalTotal,
                    discount: window.discountAmount 
                })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    alert("Order placed successfully!");
                    cart = [];
                    window.discountAmount = 0;
                    localStorage.removeItem('medCart');
                    location.reload(); 
                } else {
                    alert("Order Failed: " + data.message);
                    if(orderBtn) {
                        orderBtn.disabled = false;
                        orderBtn.innerText = "Checkout";
                        orderBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    }
                }
            })
            .catch(err => console.error(err));
        }

        let hasActiveConflict = false;
        function checkInteractions() {
            const cartIds = cart.map(item => parseInt(item.id));
            const alertBox = document.getElementById('interaction-alert');
            const alertMsg = document.getElementById('alert-msg');
            const checkoutBtn = document.querySelector('button[onclick="placeOrder()"]');
            
            let warningMsg = "";
            hasActiveConflict = false;

            allInteractions.forEach(rule => {
                const m1 = parseInt(rule.medicine_id_1);
                const m2 = parseInt(rule.medicine_id_2);
                if (cartIds.includes(m1) && cartIds.includes(m2)) {
                    hasActiveConflict = true;
                    warningMsg += `⚠️ ${rule.warning_description}\n`;
                }
            });

            cartIds.forEach(medId => {
                allInteractions.forEach(rule => {
                    const m1 = parseInt(rule.medicine_id_1);
                    const m2 = parseInt(rule.medicine_id_2);
                    if ((medId === m1 && userPurchaseHistory.includes(m2)) || 
                        (medId === m2 && userPurchaseHistory.includes(m1))) {
                        hasActiveConflict = true;
                        warningMsg += `🚫 History Warning: ${rule.warning_description}\n`;
                    }
                });
            });

            if (hasActiveConflict) {
                alertBox.classList.remove('hidden');
                alertMsg.innerText = warningMsg;
                if(checkoutBtn) {
                    checkoutBtn.disabled = true;
                    checkoutBtn.classList.add('opacity-50', 'cursor-not-allowed');
                }
            } else {
                alertBox.classList.add('hidden');
                if(checkoutBtn) {
                    checkoutBtn.disabled = false;
                    checkoutBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            }
        }

        document.getElementById('searchInput').addEventListener('input', function() {
            fetch(`search_medicines.php?query=${this.value}`)
                .then(res => res.text())
                .then(data => { document.getElementById('medicineGrid').innerHTML = data; });
        });
        
        function openReviewModal(id, name) {
            document.getElementById('modalMedId').value = id;
            document.getElementById('reviewMedName').innerText = name;
            document.getElementById('reviewModal').classList.remove('hidden');
        }
    </script>
</body>
</html>