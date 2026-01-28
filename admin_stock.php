<?php
include 'config.php';

if (isset($_POST['add_stock'])) {
    $med_id = $_POST['medicine_id'];
    $sup_id = $_POST['supplier_id'];
    $qty = $_POST['quantity'];
    $p_price = $_POST['purchase_price'];
    $s_price = $_POST['selling_price'];
    $expiry = $_POST['expiry_date'];
    $batch = "BAT-" . strtoupper(substr(md5(time()), 0, 5));

    try {
        $pdo->beginTransaction();
        
        $pdo->prepare("INSERT INTO stock_purchases (supplier_id, medicine_id, quantity, purchase_price) VALUES (?, ?, ?, ?)")
            ->execute([$sup_id, $med_id, $qty, $p_price]);

        $pdo->prepare("INSERT INTO inventory_batches (medicine_id, batch_number, expiry_date, quantity_instock, purchase_price) VALUES (?, ?, ?, ?, ?)")
            ->execute([$med_id, $batch, $expiry, $qty, $p_price]);

        $pdo->prepare("UPDATE medicines SET supplier_id = ?, unit_price = ? WHERE medicine_id = ?")
            ->execute([$sup_id, $s_price, $med_id]);

        $pdo->commit();
        echo "<script>alert('Stock and customer price updated successfully!'); window.location='adminpanel.php';</script>";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }
}
?>