<?php
include 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to place an order.']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['cart'])) {
    echo json_encode(['success' => false, 'message' => 'Cart is empty!']);
    exit();
}

try {
    $pdo->beginTransaction(); 

    $userId = $_SESSION['user_id'];
    $cartItems = $data['cart'];
    $finalTotal = floatval($data['total']);

    $stmtP = $pdo->prepare("SELECT prescription_id FROM prescriptions WHERE user_id = ? ORDER BY uploaded_at DESC LIMIT 1");
    $stmtP->execute([$userId]);
    $prescId = $stmtP->fetchColumn() ?: null;

    $stmtOrder = $pdo->prepare("INSERT INTO orders (user_id, total_amount, order_type, order_status, prescription_id) VALUES (?, ?, 'Online', 'Pending', ?)");
    $stmtOrder->execute([$userId, $finalTotal, $prescId]);
    $orderId = $pdo->lastInsertId();

    foreach ($cartItems as $item) {
        $medId = $item['id'];
        $batchId = $item['batch_id'];
        $qty = intval($item['qty']);
        $price = floatval($item['price']);

        $stCheck = $pdo->prepare("SELECT quantity_instock FROM inventory_batches WHERE batch_id = ?");
        $stCheck->execute([$batchId]);
        $available = $stCheck->fetchColumn();

        if ($available < $qty) {
            throw new Exception($item['name'] . " is out of stock!");
        }

        $pdo->prepare("UPDATE inventory_batches SET quantity_instock = quantity_instock - ? WHERE batch_id = ?")
            ->execute([$qty, $batchId]);
        
        $subtotal = $price * $qty;
        $pdo->prepare("INSERT INTO order_items (order_id, medicine_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)")
            ->execute([$orderId, $medId, $qty, $price, $subtotal]);
    }

    $pdo->commit(); 
    echo json_encode(['success' => true, 'message' => 'Order placed successfully!']);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>