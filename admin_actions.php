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

// 4. ADD SUPPLIER
if (isset($_POST['add_supplier'])) {
    $supplierName = trim($_POST['supplier_name'] ?? '');
    $contactPerson = trim($_POST['contact_person'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($supplierName === '') {
        echo "<script>alert('Supplier name is required!'); window.location='$redirect';</script>";
        exit();
    }

    $sql = "INSERT INTO suppliers (supplier_name, contact_person, phone, email, address) VALUES (?, ?, ?, ?, ?)";
    if ($pdo->prepare($sql)->execute([$supplierName, $contactPerson, $phone, $email, $address])) {
        echo "<script>alert('Supplier created successfully!'); window.location='$redirect';</script>";
    } else {
        echo "<script>alert('Failed to add supplier!'); window.location='$redirect';</script>";
    }
}

// 5. DELETE SUPPLIER
if (isset($_POST['delete_supplier'])) {
    $supplierId = (int) ($_POST['supplier_id'] ?? 0);
    if ($supplierId > 0) {
        $pdo->prepare("DELETE FROM suppliers WHERE supplier_id = ?")->execute([$supplierId]);
        echo "<script>alert('Supplier deleted successfully!'); window.location='$redirect';</script>";
    }
}

// 5A. ADD CATEGORY
if (isset($_POST['add_category'])) {
    $categoryName = trim($_POST['category_name'] ?? '');
    if ($categoryName === '') {
        echo "<script>alert('Category name is required!'); window.location='$redirect';</script>";
        exit();
    }

    $exists = $pdo->prepare("SELECT category_id FROM categories WHERE LOWER(category_name) = LOWER(?) LIMIT 1");
    $exists->execute([$categoryName]);
    if ($exists->fetch()) {
        echo "<script>alert('This category already exists!'); window.location='$redirect';</script>";
        exit();
    }

    $sql = "INSERT INTO categories (category_name) VALUES (?)";
    if ($pdo->prepare($sql)->execute([$categoryName])) {
        echo "<script>alert('Category created successfully!'); window.location='$redirect';</script>";
    } else {
        echo "<script>alert('Failed to add category!'); window.location='$redirect';</script>";
    }
}

// 5B. DELETE CATEGORY
if (isset($_POST['delete_category'])) {
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    if ($categoryId <= 0) {
        echo "<script>alert('Invalid category selected!'); window.location='$redirect';</script>";
        exit();
    }

    $usage = $pdo->prepare("SELECT COUNT(*) as total FROM medicines WHERE category_id = ?");
    $usage->execute([$categoryId]);
    $count = (int) $usage->fetchColumn();

    if ($count > 0) {
        echo "<script>alert('This category is in use by medicines. Please reassign or delete those items first.'); window.location='$redirect';</script>";
        exit();
    }

    $pdo->prepare("DELETE FROM categories WHERE category_id = ?")->execute([$categoryId]);
    echo "<script>alert('Category deleted successfully!'); window.location='$redirect';</script>";
}

// 6. ADD COUPON
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

// 7. DELETE COUPON
if (isset($_POST['delete_coupon'])) {
    $pdo->prepare("DELETE FROM coupons WHERE coupon_id = ?")->execute([$_POST['coupon_id']]);
    echo "<script>alert('Coupon deleted successfully!'); window.location='$redirect';</script>";
}

// 8. DELETE CONFLICT
if (isset($_POST['delete_interaction'])) {
    $pdo->prepare("DELETE FROM drug_interactions WHERE interaction_id = ?")->execute([$_POST['interaction_id']]);
    echo "<script>alert('Conflict deleted successfully!'); window.location='$redirect';</script>";
}

// 9. UPDATE APPOINTMENT STATUS
if (isset($_POST['update_appointment_status'])) {
    $allowedStatuses = ['Pending', 'Approved', 'Completed', 'Cancelled'];
    $status = $_POST['status'] ?? '';
    $appointmentId = (int) ($_POST['appointment_id'] ?? 0);

    if ($appointmentId > 0 && in_array($status, $allowedStatuses, true)) {
        $pdo->prepare("UPDATE appointments SET status = ? WHERE appointment_id = ?")->execute([$status, $appointmentId]);
        echo "<script>alert('Appointment status updated!'); window.location='$redirect';</script>";
    } else {
        echo "<script>alert('Invalid appointment status provided!'); window.location='$redirect';</script>";
    }
}

// 10. CREATE SUPPORT TICKET
if (isset($_POST['create_support_ticket'])) {
    $userId = (int) ($_SESSION['user_id'] ?? 0);
    $subject = trim($_POST['subject'] ?? '');
    $category = trim($_POST['category'] ?? 'General');
    $priority = trim($_POST['priority'] ?? 'Medium');
    $message = trim($_POST['message'] ?? '');

    if ($userId <= 0 || $subject === '' || $message === '') {
        echo "<script>alert('Please provide a subject and message.'); window.location='support_ticket.php';</script>";
        exit();
    }

    $sql = "INSERT INTO support_tickets (user_id, subject, category, priority, message, status) VALUES (?, ?, ?, ?, ?, 'Open')";
    if ($pdo->prepare($sql)->execute([$userId, $subject, $category, $priority, $message])) {
        echo "<script>alert('Support ticket submitted successfully!'); window.location='my_support_tickets.php';</script>";
    } else {
        echo "<script>alert('Failed to submit support ticket.'); window.location='support_ticket.php';</script>";
    }
}

// 11. UPDATE SUPPORT TICKET STATUS
if (isset($_POST['update_support_ticket'])) {
    $allowedStatuses = ['Open', 'In Progress', 'Resolved', 'Closed'];
    $status = $_POST['status'] ?? '';
    $ticketId = (int) ($_POST['ticket_id'] ?? 0);

    if ($ticketId > 0 && in_array($status, $allowedStatuses, true)) {
        $pdo->prepare("UPDATE support_tickets SET status = ? WHERE ticket_id = ?")->execute([$status, $ticketId]);
        echo "<script>alert('Support ticket updated!'); window.location='$redirect';</script>";
    } else {
        echo "<script>alert('Invalid ticket status provided!'); window.location='$redirect';</script>";
    }
}

// 12. UPDATE ORDER STATUS
if (isset($_POST['update_order_status'])) {
    $allowedStatuses = ['Pending', 'Confirmed', 'Packed', 'Shipped', 'Delivered', 'Cancelled'];
    $status = $_POST['status'] ?? '';
    $orderId = (int) ($_POST['order_id'] ?? 0);

    if ($orderId > 0 && in_array($status, $allowedStatuses, true)) {
        $pdo->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?")->execute([$status, $orderId]);
        echo "<script>alert('Order status updated!'); window.location='$redirect';</script>";
    } else {
        echo "<script>alert('Invalid order status provided!'); window.location='$redirect';</script>";
    }
}
?>