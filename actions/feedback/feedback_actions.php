<?php
/**
 * Feedback Actions
 * Handles guest reviews and moderation
 */

require_once __DIR__ . '/../../config/config.php';

/**
 * Get all feedback
 */
function get_all_feedback($pdo, $status = null) {
    try {
        $sql = "SELECT * FROM feedback";
        if ($status) {
            $sql .= " WHERE status = " . $pdo->quote($status);
        }
        $sql .= " ORDER BY created_at DESC";
        return $pdo->query($sql)->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Add new feedback
 */
function add_feedback($pdo, $data) {
    try {
        $sql = "INSERT INTO feedback (name, email, rating, review_text, status) 
                VALUES (:name, :email, :rating, :review_text, :status)";
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
        $status = strtolower($status);
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
        $stats['pending'] = $pdo->query("SELECT COUNT(*) FROM feedback WHERE status = 'pending'")->fetchColumn();
        
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
