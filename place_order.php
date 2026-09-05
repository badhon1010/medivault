<?php
include 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to place an order.']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['cart']) || !is_array($data['cart']) || empty($data['cart'])) {
    echo json_encode(['success' => false, 'message' => 'Cart is empty!']);
    exit();
}

try {
    $pdo->beginTransaction();

    $userId = (int) $_SESSION['user_id'];
    $cartItems = $data['cart'];
    $finalTotal = isset($data['total']) ? (float) $data['total'] : 0.0;

    $stmtP = $pdo->prepare("SELECT prescription_id FROM prescriptions WHERE user_id = ? ORDER BY uploaded_at DESC LIMIT 1");
    $stmtP->execute([$userId]);
    $prescId = $stmtP->fetchColumn() ?: null;

    $totalAmount = 0.0;

    foreach ($cartItems as $index => $item) {
        $medId = isset($item['id']) ? (int) $item['id'] : 0;
        $batchId = isset($item['batch_id']) ? (int) $item['batch_id'] : 0;
        $qty = isset($item['qty']) ? (int) $item['qty'] : 0;
        $price = isset($item['price']) ? (float) $item['price'] : 0.0;
        $name = isset($item['name']) ? trim((string) $item['name']) : 'Item';

        if ($medId <= 0 || $batchId <= 0 || $qty <= 0 || $price <= 0) {
            throw new Exception('Invalid cart item at position ' . ($index + 1) . '.');
        }

        $medicineStmt = $pdo->prepare("SELECT medicine_id, medicine_name, requires_prescription FROM medicines WHERE medicine_id = ? LIMIT 1");
        $medicineStmt->execute([$medId]);
        $medicine = $medicineStmt->fetch();

        if (!$medicine) {
            throw new Exception($name . ' could not be found.');
        }

        if ((int) $medicine['requires_prescription'] === 1 && !$prescId) {
            throw new Exception('A valid prescription is required for ' . $medicine['medicine_name'] . '.');
        }

        $batchStmt = $pdo->prepare("SELECT batch_id, medicine_id, quantity_instock, expiry_date FROM inventory_batches WHERE batch_id = ? LIMIT 1");
        $batchStmt->execute([$batchId]);
        $batch = $batchStmt->fetch();

        if (!$batch) {
            throw new Exception($name . ' batch is unavailable.');
        }

        if ((int) $batch['medicine_id'] !== $medId) {
            throw new Exception($name . ' does not belong to the selected batch.');
        }

        if ($batch['expiry_date'] && strtotime($batch['expiry_date']) < strtotime('today')) {
            throw new Exception($name . ' batch has expired and cannot be ordered.');
        }

        if ((int) $batch['quantity_instock'] < $qty) {
            throw new Exception($name . ' does not have enough stock in the selected batch.');
        }

        $totalAmount += $price * $qty;
    }

    if ($finalTotal > 0 && abs($finalTotal - $totalAmount) > 0.01) {
        throw new Exception('Order total does not match the cart contents.');
    }

    $stmtOrder = $pdo->prepare("INSERT INTO orders (user_id, total_amount, order_type, order_status, prescription_id) VALUES (?, ?, 'Online', 'Pending', ?)");
    $stmtOrder->execute([$userId, $totalAmount, $prescId]);
    $orderId = $pdo->lastInsertId();

    foreach ($cartItems as $item) {
        $medId = (int) $item['id'];
        $batchId = (int) $item['batch_id'];
        $qty = (int) $item['qty'];
        $price = (float) $item['price'];

        $batchUpdate = $pdo->prepare("UPDATE inventory_batches SET quantity_instock = quantity_instock - ? WHERE batch_id = ? AND quantity_instock >= ?");
        $batchUpdate->execute([$qty, $batchId, $qty]);

        if ($batchUpdate->rowCount() !== 1) {
            throw new Exception('Stock changed while processing your order. Please review your cart.');
        }

        $subtotal = $price * $qty;
        $pdo->prepare("INSERT INTO order_items (order_id, medicine_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)")
            ->execute([$orderId, $medId, $qty, $price, $subtotal]);
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Order placed successfully!']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>