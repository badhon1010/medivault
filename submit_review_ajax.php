<?php
include 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to submit a review.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['medicine_id']) || empty($data['rating']) || empty($data['comment'])) {
    echo json_encode(['success' => false, 'message' => 'Please provide a rating and comment!']);
    exit();
}

$med_id = intval($data['medicine_id']);
$rating = intval($data['rating']);
$comment = trim($data['comment']);

try {
    // 3. The "Upsert" Query (Insert or Update)
    // This works perfectly with the UNIQUE constraint we added in SQL
    $sql = "INSERT INTO medicine_reviews (user_id, medicine_id, rating, comment, created_at) 
            VALUES (?, ?, ?, ?, NOW()) 
            ON DUPLICATE KEY UPDATE 
            rating = VALUES(rating), 
            comment = VALUES(comment), 
            created_at = NOW()";
            
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([$user_id, $med_id, $rating, $comment]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Review submitted successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error occurred.']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>