<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $med_id = $_POST['medicine_id'];
    $rating = $_POST['rating'];
    $comment = trim($_POST['comment']);

    try {
        $stmt = $pdo->prepare("INSERT INTO medicine_reviews (user_id, medicine_id, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $med_id, $rating, $comment]);
        echo "<script>alert('রিভিউ জমা হয়েছে!'); window.location='dashboard.php';</script>";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>