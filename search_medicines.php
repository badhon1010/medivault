<?php
include 'config.php';

$query = $_GET['query'] ?? '';

// Symptoms Search Query Included
$sql = "SELECT m.*, SUM(b.quantity_instock) as total_available, 
        (SELECT AVG(rating) FROM medicine_reviews WHERE medicine_id = m.medicine_id) as avg_rating 
        FROM medicines m 
        LEFT JOIN inventory_batches b ON m.medicine_id = b.medicine_id 
        WHERE m.medicine_name LIKE ? OR m.generic_name LIKE ? OR m.indications LIKE ? 
        GROUP BY m.medicine_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(["%$query%", "%$query%", "%$query%"]);
$medicines = $stmt->fetchAll();

$batches = $pdo->query("SELECT * FROM inventory_batches WHERE quantity_instock > 0 AND expiry_date >= CURDATE() ORDER BY expiry_date ASC")->fetchAll();
$batch_map = [];
foreach($batches as $b) { $batch_map[$b['medicine_id']][] = $b; }

if(empty($medicines)) { echo '<div class="col-span-full text-center py-10 text-gray-400">No medicines found.</div>'; exit; }

foreach($medicines as $med) {
    $stockBadge = ($med['total_available'] > 0) 
        ? '<span class="absolute top-3 left-3 bg-emerald-50 text-emerald-600 text-[10px] font-bold px-2 py-1 rounded-md z-10">IN STOCK</span>'
        : '<span class="absolute top-3 left-3 bg-red-50 text-red-500 text-[10px] font-bold px-2 py-1 rounded-md z-10">OUT OF STOCK</span>';

    $img_file = !empty($med['medicine_image']) ? trim($med['medicine_image']) : 'default.png';
    $img_path = "assets/img/" . $img_file;
    if (!file_exists($img_path)) $img_path = "assets/img/napa.jpg";

    $batchOptions = '<option value="" disabled selected>Select Batch</option>';
    if (isset($batch_map[$med['medicine_id']])) {
        foreach($batch_map[$med['medicine_id']] as $batch) {
            // Fixed: Date format added
            $expDate = date('d M Y', strtotime($batch['expiry_date']));
            $batchOptions .= "<option value='{$batch['batch_id']}' data-stock='{$batch['quantity_instock']}' data-batch-num='{$batch['batch_number']}'> Batch: {$batch['batch_number']} (Exp: $expDate)</option>";
        }
    }

    echo "
    <div class='med-card bg-white rounded-2xl p-4 border border-transparent shadow-sm relative group transition-all duration-300 hover:shadow-lg'>
        
        {$stockBadge}

        <a href='medicine_details.php?id={$med['medicine_id']}' class='block'>
            <div class='h-32 flex items-center justify-center mb-3 transition-transform duration-500 group-hover:scale-110'>
                <img src='{$img_path}' class='h-full object-contain' alt='Medicine'>
            </div>

            <h4 class='text-sm font-bold text-slate-800 line-clamp-1 group-hover:text-arogga transition-colors'>" . htmlspecialchars($med['medicine_name']) . "</h4>
            <p class='text-[10px] text-gray-400 mb-2 line-clamp-1'>" . htmlspecialchars($med['generic_name']) . "</p>
        </a>

        <select id='batch-select-{$med['medicine_id']}' class='w-full mb-3 text-[10px] bg-gray-50 border border-gray-100 rounded-lg p-1.5 outline-none focus:border-teal-400 text-gray-600'>
            {$batchOptions}
        </select>

        <div class='flex items-center justify-between mt-1 mb-2'>
            <div>
                <span class='text-xs text-gray-400 line-through mr-1'>" . ($med['unit_price'] + 5) . "৳</span>
                <span class='text-lg font-bold text-arogga'>{$med['unit_price']}৳</span>
            </div>
            
            <div class='flex items-center text-[10px] text-yellow-400 font-bold bg-yellow-50 px-2 py-1 rounded-lg'>
                <i class='fas fa-star mr-1'></i>
                <span class='text-slate-500'>(" . round($med['avg_rating'], 1) . ")</span>
            </div>
        </div>

        <button onclick=\"addToCart({$med['medicine_id']}, '".addslashes($med['medicine_name'])."', {$med['unit_price']}, ".($med['total_available']??0).", {$med['requires_prescription']})\" 
                class='w-full bg-arogga text-white py-2 rounded-xl text-xs font-bold hover:bg-teal-700 transition-all shadow-md flex items-center justify-center gap-2 active:scale-95'>
            <i class='fas fa-shopping-bag'></i> Add to Cart
        </button>
    </div>";
}
?>