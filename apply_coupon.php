<?php
include 'config.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$code = $data['coupon_code'] ?? '';
$total = floatval($data['total'] ?? 0);

if (empty($code)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a coupon code!']);
    exit;
}

try {
    // 1. Check coupon from database
    $stmt = $pdo->prepare("SELECT * FROM coupons WHERE coupon_code = ?");
    $stmt->execute([$code]);
    $coupon = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Validate Coupon
    if (!$coupon) {
        echo json_encode(['success' => false, 'message' => 'Invalid Coupon Code!']);
        exit;
    }

    if ($coupon['status'] !== 'active') {
        echo json_encode(['success' => false, 'message' => 'This coupon is inactive!']);
        exit;
    }

    if (strtotime($coupon['expiry_date']) < time()) {
        echo json_encode(['success' => false, 'message' => 'Coupon has expired!']);
        exit;
    }

    if ($total < $coupon['min_order_amount']) {
        echo json_encode(['success' => false, 'message' => 'Minimum order amount must be ' . $coupon['min_order_amount'] . ' Tk!']);
        exit;
    }

    $discountAmount = 0;
    if (isset($coupon['discount_percent']) && $coupon['discount_percent'] > 0) {
        $discountAmount = ($total * $coupon['discount_percent']) / 100;
    } else {
        $discountAmount = 0; 
    }

    if ($discountAmount > $total) {
        $discountAmount = $total;
    }

    echo json_encode([
        'success' => true,
        'discount' => round($discountAmount, 2),
        'message' => 'Coupon Applied! You saved ' . round($discountAmount, 2) . ' Tk'
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error checking coupon.']);
}
?>