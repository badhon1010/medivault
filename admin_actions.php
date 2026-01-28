<?php
include 'config.php';

$redirect = 'adminpanel.php'; 

if (isset($_POST['add_medicine'])) {
    try {
        $name = trim($_POST['medicine_name']);
        $generic = trim($_POST['generic_name']);
        $category = $_POST['category_id'];
        $price = $_POST['unit_price'];
        $min_stock = $_POST['min_stock_level'];
        
        // New Fields Handling
        $indications = trim($_POST['indications'] ?? '');
        $description = trim($_POST['description'] ?? '');

        // SQL Query Updated
        $sql = "INSERT INTO medicines (medicine_name, generic_name, category_id, unit_price, min_stock_level, indications, description) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$name, $generic, $category, $price, $min_stock, $indications, $description])) {
            echo "<script>alert('New medicine added successfully!'); window.location='$redirect';</script>";
        } else {
            echo "<script>alert('Failed to add medicine!'); window.location='$redirect';</script>";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        exit();
    }
}

// 2. EDIT MEDICINE
if (isset($_POST['update_medicine'])) {
    $id = $_POST['medicine_id'];
    $name = trim($_POST['medicine_name']);
    $price = $_POST['unit_price'];
    $min_stock = $_POST['min_stock'];

    $sql = "UPDATE medicines SET medicine_name = ?, unit_price = ?, min_stock_level = ? WHERE medicine_id = ?";
    if ($pdo->prepare($sql)->execute([$name, $price, $min_stock, $id])) {
        echo "<script>alert('Medicine updated successfully!'); window.location='$redirect';</script>";
    }
}

// 3. EDIT CONFLICT
if (isset($_POST['update_interaction'])) {
    $id = $_POST['interaction_id'];
    $severity = $_POST['severity'];
    $desc = trim($_POST['description']);

    $sql = "UPDATE drug_interactions SET severity = ?, warning_description = ? WHERE interaction_id = ?";
    if ($pdo->prepare($sql)->execute([$severity, $desc, $id])) {
        echo "<script>alert('Conflict updated successfully!'); window.location='$redirect';</script>";
    }
}

// 4. ADD COUPON
if (isset($_POST['add_coupon'])) {
    $code = trim($_POST['coupon_code']);
    $discount = $_POST['discount'];
    $min_amt = $_POST['min_amount'];
    $expiry = $_POST['expiry'];

    $sql = "INSERT INTO coupons (coupon_code, discount_percent, min_order_amount, expiry_date, status) VALUES (?, ?, ?, ?, 'active')";
    if ($pdo->prepare($sql)->execute([$code, $discount, $min_amt, $expiry])) {
        echo "<script>alert('Coupon created successfully!'); window.location='$redirect';</script>";
    }
}

// 5. DELETE COUPON
if (isset($_POST['delete_coupon'])) {
    $pdo->prepare("DELETE FROM coupons WHERE coupon_id = ?")->execute([$_POST['coupon_id']]);
    echo "<script>alert('Coupon deleted successfully!'); window.location='$redirect';</script>";
}

// 6. DELETE CONFLICT
if (isset($_POST['delete_interaction'])) {
    $pdo->prepare("DELETE FROM drug_interactions WHERE interaction_id = ?")->execute([$_POST['interaction_id']]);
    echo "<script>alert('Conflict deleted successfully!'); window.location='$redirect';</script>";
}

// 7. UPDATE ORDER STATUS
if (isset($_POST['update_order_status'])) {
    $pdo->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?")->execute([$_POST['status'], $_POST['order_id']]);
    echo "<script>alert('Order status updated!'); window.location='$redirect';</script>";
}
?>