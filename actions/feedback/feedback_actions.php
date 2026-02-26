<?php
/**
 * Feedback Actions
 * Handles guest reviews and moderation
 */

require_once __DIR__ . '/../../config/config.php';

/**
 * Get all feedback
 */
function get_all_feedback($pdo) {
    try {
        return $pdo->query("SELECT * FROM feedback ORDER BY created_at DESC")->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Add new feedback
 */
function add_feedback($pdo, $data) {
    try {
        $sql = "INSERT INTO feedback (customer_name, email, rating, comment, status) 
                VALUES (:customer_name, :email, :rating, :comment, :status)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($data);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Moderate feedback
 */
function moderate_feedback($pdo, $id, $status) {
    try {
        $stmt = $pdo->prepare("UPDATE feedback SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Get feedback statistics
 */
function get_feedback_stats($pdo) {
    try {
        $stats = [];
        
        // Total Feedback
        $stats['total'] = $pdo->query("SELECT COUNT(*) FROM feedback")->fetchColumn();
        
        // 5-Star Reviews
        $stats['five_star'] = $pdo->query("SELECT COUNT(*) FROM feedback WHERE rating = 5")->fetchColumn();
        
        // Pending Reviews
        $stats['pending'] = $pdo->query("SELECT COUNT(*) FROM feedback WHERE status = 'Pending'")->fetchColumn();
        
        // Average Rating
        $stats['average'] = $pdo->query("SELECT AVG(rating) FROM feedback")->fetchColumn() ?: 0;
        
        return $stats;
    } catch (PDOException $e) {
        return [
            'total' => 0,
            'five_star' => 0,
            'pending' => 0,
            'average' => 0
        ];
    }
}
?>
